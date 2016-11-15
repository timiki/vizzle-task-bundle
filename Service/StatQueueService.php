<?php

namespace Vizzle\TaskBundle\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Vizzle\ServiceBundle\Mapping;
use Vizzle\TaskBundle\Entity\Queue;
use Vizzle\TaskBundle\Entity\Stat\TaskQueue;

/**
 * @Mapping\Process(
 *     name="task:queue:stat",
 *     description="Task queue stat collection service.",
 *     mode="AUTO"
 * )
 */
class StatQueueService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @Mapping\OnStart()
     */
    public function onStart()
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @Mapping\Execute()
     */
    public function execute()
    {
        $em        = $this->em;
        $container = $this->container;

        $em->transactional(function () use (&$em) {

            $date = new \DateTime();

            $qb = $em->getRepository('VizzleTaskBundle:Stat\TaskQueue')->createQueryBuilder('stat');
            $qb->where('stat.date = :date');
            $qb->setParameter('date', $date);

            if (!$qb->getQuery()->setLockMode(LockMode::PESSIMISTIC_READ)->getOneOrNullResult()) {

                $qb = $em->getRepository('VizzleTaskBundle:Queue')->createQueryBuilder('queue');

                $qb->andWhere('queue.status <> :complete');
                $qb->andWhere('queue.status <> :error');
                $qb->setParameter('complete', 'COMPLETE');
                $qb->setParameter('error', 'ERROR');

                $tasks  = $qb->getQuery()->setLockMode(LockMode::PESSIMISTIC_READ)->execute();
                $result = [
                    'total' => 0,
                    'wait'  => 0,
                    'run'   => 0,
                    'error' => 0,
                ];

                /* @var Queue $task */
                foreach ($tasks as $task) {

                    $result['total'] += 1;

                    switch ($task->getStatus()) {
                        case 'RUN':
                        case 'PREPARE':
                            $result['run'] += 1;
                            break;
                        case 'WAIT':
                            $result['wait'] += 1;
                            break;
                        case 'ERROR':
                            $result['error'] += 1;
                            break;
                    }

                }

                // Average

                $qb = $em->getRepository('VizzleTaskBundle:Queue\Stat')->createQueryBuilder('queue_stat');

                $qb->select(
                    $qb->expr()->avg('queue_stat.timeWait'),
                    $qb->expr()->avg('queue_stat.timeExecuting'),
                    $qb->expr()->avg('queue_stat.memory')
                );

                $qb->where('queue_stat.createdAt >= :date');
                $qb->setParameter('date', (new \DateTime())->sub(new \DateInterval('PT1H')));

                $average = $qb->getQuery()->getSingleResult();

                // Stat

                $stat = new TaskQueue();
                $stat->setDate($date);
                $stat->setTotal($result['total']);
                $stat->setWait($result['wait']);
                $stat->setRun($result['run']);
                $stat->setError($result['error']);
                $stat->setAWaitTime((float)$average[1]);
                $stat->setAExecutingTime((float)$average[2]);
                $stat->setAMemory((integer)$average[3]);

                $em->persist($stat);
            }

        });

        // Clear stat

        $day = $container->hasParameter('vizzle.stat_lifetime') ? $container->getParameter('vizzle.stat_lifetime') : 180;

        $qb = $em->createQueryBuilder();
        $qb->delete('VizzleTaskBundle:Stat\TaskQueue', 'stat');
        $qb->where('stat.date <= :date');
        $qb->setParameter('date', (new \DateTime())->sub(new \DateInterval('P' . $day . 'D')));

        $qb->getQuery()->execute();

        $em->clear();
    }
}
