<?php

namespace Vizzle\TaskBundle\Command\Queue;

use Doctrine\DBAL\LockMode;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vizzle\TaskBundle\Entity\Queue;

class WorkerCommand extends AbstractCommand
{
    /**
     * Is worker run.
     *
     * @var bool
     */
    private $run = true;

    /**
     * Sleep (sec).
     *
     * @var bool
     */
    private $sleep = 1; // 1 sec

    /**
     * Max sleep time (sec).
     *
     * @var bool
     */
    private $maxSleepTime = 10; // 10 sec

    /**
     * Min sleep time (sec).
     *
     * @var bool
     */
    private $minSleepTime = 1; // 1 sec

    /**
     * Signals.
     *
     * @var array
     */
    private $signals = [
        SIGINT,
        SIGTERM,
    ];

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('task:queue:worker')
            ->setDescription('Queue worker')
            ->setHelp(<<<EOT
The <info>task:queue:worker</info> command execute task from queue in loop:

<info>php bin/console task:queue:worker</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // When xdebug loaded processing will be abort after xdebug.max_nesting_level
        if (extension_loaded('xdebug')) {
            xdebug_disable();
        }

        // Handler for signal
        if (!in_array('pcntl_signal', explode(',', ini_get('disable_functions')))) {
            foreach ($this->signals as $signal) {
                pcntl_signal($signal, [$this, 'signal']);
            }
        }

        $container = $this->getContainer();
        $em        = $container->get('doctrine.orm.entity_manager');
        $logger    = $this->getContainer()->get('logger');

        while ($this->run) {

            try {

                /* @var Queue|null $task */
                $task = null;

                // Try select task for execute.
                $em->transactional(function () use (&$container, &$em, &$task) {

                    $qb = $em->createQueryBuilder();

                    $qb->select('q');
                    $qb->from('VizzleTaskBundle:Queue', 'q');
                    $qb->andWhere('q.status = :status');
                    $qb->andWhere('q.server = :server');
                    $qb->setParameter('server', $container->getParameter('vizzle.server'));
                    $qb->setParameter('status', 'PREPARE');
                    $qb->setMaxResults(1);

                    if ($task = $qb->getQuery()->setLockMode(LockMode::PESSIMISTIC_WRITE)->getOneOrNullResult()) {
                        $task->setPid(getmypid());
                        $task->setStatus('RUN');
                    }

                });

                if ($task) {

                    $task = $em
                        ->getRepository('VizzleTaskBundle:Queue')
                        ->findOneBy(
                            [
                                'server' => $container->getParameter('vizzle.server'),
                                'status' => 'RUN',
                                'pid'    => getmypid(),
                            ]
                        );

                    if ($task) {

                        $task->setUpdatedAt(new \DateTime());
                        $em->flush($task);

                        $output->writeln(
                            sprintf(
                                'Run task #%s "%s".',
                                $task->getId(),
                                $task->getTask()
                            )
                        );

                        $logger->info(
                            sprintf(
                                'Queue worker (%s) run task #%s "%s" at server "%s".',
                                getmypid(),
                                $task->getId(),
                                $task->getTask(),
                                $container->getParameter('vizzle.server')
                            )
                        );

                        $this->executeTaskInQueue($task);
                    }

                    // Set fast.

                    $this->sleep = $this->sleep > $this->minSleepTime ? $this->sleep - 1 : $this->minSleepTime;

                } else {

                    // Set slow.
                    $this->sleep = $this->sleep < $this->maxSleepTime ? $this->sleep + 1 : $this->maxSleepTime;
                }

            } catch (\Exception $e) {

                $logger->error(
                    sprintf(
                        'Queue worker (%s) has error "%s" at server "%s".',
                        getmypid(),
                        $e->getMessage(),
                        $container->getParameter('vizzle.server')
                    )
                );

            }

            pcntl_signal_dispatch();
            $em->clear();

            if ($this->run) {
                sleep($this->sleep);
            }
        }

        return 0;
    }

    /**
     * On signal.
     *
     * @param integer $signal
     */
    public function signal($signal)
    {
        switch ($signal) {
            case SIGINT:
            case SIGKILL:
            case SIGTERM:
                $this->run = false;
        }
    }
}

