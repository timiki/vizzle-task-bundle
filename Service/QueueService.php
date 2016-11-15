<?php

namespace Vizzle\TaskBundle\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Process\Process;
use Vizzle\VizzleBundle\Process\ProcessUtils;
use Vizzle\ServiceBundle\Mapping;
use Vizzle\TaskBundle\Entity\Queue;

/**
 * @Mapping\Process(
 *     name="task:queue",
 *     description="Task queue service.",
 *     mode="AUTO"
 * )
 */
class QueueService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Process list of queue workers.
     *
     * @var Process[]
     */
    protected $workers = [];

    /**
     * Max count of run task at some time.
     *
     * @var integer
     */
    protected $maxCount = 0;

    /**
     * Process utils.
     *
     * @var ProcessUtils
     */
    protected $processUtils;

    /**
     * @var integer
     */
    protected $lastExecute;

    /**
     * @Mapping\OnStart()
     */
    public function onStart()
    {
        $this->em           = $this->container->get('doctrine.orm.entity_manager');
        $this->logger       = $this->container->get('logger');
        $this->processUtils = new ProcessUtils();

        // If need run workers?
        if ($this->container->getParameter('vizzle.queue.handler') === 'worker') {

            $this->maxCount = $this->container->getParameter('vizzle.queue.worker_count');
            $cmd            = 'php ' . $this->container->get('kernel')->getConsoleCmd() . ' task:queue:worker';

            // Is debug
            if ($this->container->get('kernel')->isDebug()) {
                $cmd .= ' --debug';
            }

            $processDir = $this->container->getParameter('kernel.root_dir');

            for ($i = 1; $i <= $this->container->getParameter('vizzle.queue.worker_count'); $i++) {

                $process = new Process($cmd, $processDir);
                $process->setTimeout(null);
                $process->setIdleTimeout(null);
                $process->start();
                $this->workers[] = $process;

                $this->logger->info(
                    sprintf(
                        'Run queue task worker #%s on server "%s".',
                        $i,
                        $this->container->getParameter('vizzle.server')
                    )
                );

            }

        } else {
            $this->maxCount = $this->container->getParameter('vizzle.queue.process_count');
        }

    }

    /**
     * @Mapping\OnStop()
     */
    public function onStop()
    {
        // Wait for finish all run task.
        while ($this->getCurrentRunTaskCount() > 0) {
            sleep(1);
        }

        // If run workers stop it.
        foreach ($this->workers as $i => $process) {
            $process->stop();
            $this->logger->info(
                sprintf(
                    'Stop queue task worker #%s on server "%s".',
                    $i,
                    $this->container->getParameter('vizzle.server')
                )
            );
        }
    }

    /**
     * @Mapping\Execute()
     */
    public function execute()
    {
        // Run clear

        $this->clearNotRunTask();
        $this->checkRunTask();

        // Find tasks to execute

        $em                 = $this->em;
        $container          = $this->container;
        $availableTaskCount = $this->maxCount - $this->getCurrentRunTaskCount();
        $count              = 0;

        if ($this->maxCount - $this->getCurrentRunTaskCount() > 0) {

            // Select task from queue for run.
            $em->transactional(function () use (&$em, &$container, &$count, &$availableTaskCount) {

                $qbSelect = $em->createQueryBuilder();

                $qbSelect->select('q.id');
                $qbSelect->from('VizzleTaskBundle:Queue', 'q');
                $qbSelect->andWhere('q.status = :status');
                $qbSelect->setMaxResults($availableTaskCount);
                $qbSelect->orderBy('q.priority', 'DESC');
                $qbSelect->setParameter('status', 'WAIT');

                $ids = $qbSelect->getQuery()->setLockMode(LockMode::PESSIMISTIC_READ)->getArrayResult();

                $qbUpdate = $em->createQueryBuilder();

                $qbUpdate->update('VizzleTaskBundle:Queue', 'q');
                $qbUpdate->andWhere('q.id IN (:ids)');
                $qbUpdate->set('q.status', ':status');
                $qbUpdate->set('q.server', ':server');
                $qbUpdate->set('q.updatedAt', ':updatedAt');
                $qbUpdate->setParameter('ids', $ids);
                $qbUpdate->setParameter('server', $container->getParameter('vizzle.server'));
                $qbUpdate->setParameter('status', 'PREPARE');
                $qbUpdate->setParameter('updatedAt', new \DateTime());

                $count = $qbUpdate->getQuery()->setLockMode(LockMode::PESSIMISTIC_WRITE)->getSingleScalarResult();
            });

            // If need run task as process
            if ($count > 0 && $container->getParameter('vizzle.queue.handler') === 'process') {

                $qb = $em->createQueryBuilder();

                $qb->select('q');
                $qb->from('VizzleTaskBundle:Queue', 'q');
                $qb->andWhere('q.server = :server');
                $qb->andWhere('q.status = :status');
                $qb->setParameter('status', 'PREPARE');
                $qb->setParameter('server', $container->getParameter('vizzle.server'));

                foreach ($qb->getQuery()->execute() as $task) {
                    $this->runQueueInProcess($task);
                }

            }

        } else {

            if ($this->container->getParameter('vizzle.queue.handler') === 'process') {
                $this->logger->warn(
                    sprintf(
                        'Server "%s" use all available limit (%s) for run task from queue in "%s" mode.',
                        $this->container->getParameter('vizzle.server'),
                        $this->maxCount,
                        $this->container->getParameter('vizzle.queue.handler')
                    )
                );
            }

        }

        $em->clear();
    }

    /**
     * Run task in single process.
     *
     * @param Queue $task
     */
    protected function runQueueInProcess(Queue $task)
    {
        $cmd = 'php ' . $this->container->get('kernel')->getConsoleCmd() . ' task:queue:process ' . $task->getId();

        // Is debug
        if ($this->container->get('kernel')->isDebug()) {
            $cmd .= ' --debug';
        }

        $this->processUtils->runBackground($cmd);

        $this->logger->info(
            sprintf(
                'Run task #%s "%s" at server "%s" in single process.',
                $task->getId(),
                $task->getTask(),
                $this->container->getParameter('vizzle.server')
            )
        );
    }


    /**
     * Get current run task count.
     *
     * @return integer
     */
    protected function getCurrentRunTaskCount()
    {
        $em        = $this->em;
        $container = $this->container;
        $count     = 0;

        $em->transactional(function () use (&$em, &$count, &$container) {
            $qb = $em->createQueryBuilder();

            $qb->select($qb->expr()->count('q.id'));
            $qb->from('VizzleTaskBundle:Queue', 'q');
            $qb->andWhere('q.server = :server');
            $qb->andWhere('q.status = :status');

            $qb->setParameter('status', 'RUN');
            $qb->setParameter('server', $container->getParameter('vizzle.server'));

            $count = $qb->getQuery()->setLockMode(LockMode::PESSIMISTIC_WRITE)->getSingleScalarResult();
        });

        return $count;
    }

    /**
     * Check run task on process exist.
     */
    protected function checkRunTask()
    {
        $em           = $this->em;
        $logger       = $this->logger;
        $container    = $this->container;
        $processUtils = $this->processUtils;

        $em->transactional(function () use (&$em, &$logger, &$container, &$processUtils) {

            $qb = $em->getRepository('VizzleTaskBundle:Queue')->createQueryBuilder('q');

            $qb->andWhere('q.status = :status');
            $qb->andWhere('q.server = :server');

            $qb->setParameter('status', 'RUN');
            $qb->setParameter('server', $container->getParameter('vizzle.server'));

            /* @var Queue $task */

            foreach ($qb->getQuery()->setLockMode(LockMode::PESSIMISTIC_WRITE)->execute() as $task) {

                if (!$processUtils->isExistPid($task->getPid())) {

                    $logger->error(
                        sprintf(
                            'Not found process (%d) for task #%d "%s" on server %s. Task will be reset to status "WAIT".',
                            $task->getPid(),
                            $task->getId(),
                            $task->getTask(),
                            $task->getServer()
                        )
                    );

                    $task = $this->resetQueueTask($task);
                    $task->setPriority($task->getPriority() + 1);
                }

            }

        });

        $em->clear();
    }

    /**
     * Clear task thant has status PREPARE more 60 sec.
     */
    protected function clearNotRunTask()
    {
        $em     = $this->em;
        $logger = $this->logger;

        $em->transactional(function () use (&$em, &$logger) {

            $waitTo = new \DateTime();
            $waitTo = $waitTo->sub(new \DateInterval('PT5M'));
            $qb     = $em->getRepository('VizzleTaskBundle:Queue')->createQueryBuilder('q');

            $qb->andWhere('q.status = :status');
            $qb->andWhere('q.updatedAt < :waitTo');
            $qb->setParameter('status', 'PREPARE');
            $qb->setParameter('waitTo', $waitTo);

            /* @var Queue $task */

            foreach ($qb->getQuery()->setLockMode(LockMode::PESSIMISTIC_WRITE)->execute() as $task) {

                $logger->error(
                    sprintf(
                        'Task #%s "%s" on server %s has status "PREPARE" more 60 sec. Task status will be reset to "WAIT".',
                        $task->getId(),
                        $task->getTask(),
                        $task->getServer()
                    )
                );

                $task = $this->resetQueueTask($task);
                $task->setPriority($task->getPriority() + 1);
            }

        });

        $em->clear();
    }

    /**
     * @param Queue $task
     *
     * @return Queue
     */
    protected function resetQueueTask(Queue $task)
    {
        $task->setStatus('WAIT');
        $task->setServer(null);
        $task->setExecutedAt(null);
        $task->setCompletedAt(null);
        $task->setResult(null);
        $task->setPid(null);

        return $task;
    }
}
