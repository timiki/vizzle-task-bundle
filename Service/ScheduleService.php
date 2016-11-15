<?php

namespace Vizzle\TaskBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Vizzle\VizzleBundle\Process\ProcessUtils;
use Vizzle\ServiceBundle\Mapping;
use Vizzle\TaskBundle\Entity\Queue;
use Vizzle\TaskBundle\Entity\Schedule;
use Cron\CronExpression;

/**
 * @Mapping\Process(
 *     name="task:schedule",
 *     description="Task schedule service.",
 *     mode="AUTO"
 * )
 */
class ScheduleService implements ContainerAwareInterface
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
     * @var ProcessUtils
     */
    protected $processUtils;

    /**
     * @Mapping\OnStart()
     */
    public function onStart()
    {
        $this->em           = $this->container->get('doctrine.orm.entity_manager');
        $this->logger       = $this->container->get('logger');
        $this->processUtils = new ProcessUtils();
    }

    /**
     * @Mapping\Execute()
     */
    public function execute()
    {
        $em     = $this->em;
        $logger = $this->logger;

        $em->transactional(function () use ($em, $logger) {

            // Get all active schedule.
            $qb = $em
                ->getRepository('VizzleTaskBundle:Schedule')
                ->createQueryBuilder('schedule');

            $qb->where('schedule.enabled = true');

            /* @var Schedule[] $schedules */
            $schedules = $qb->getQuery()->execute();
            $now       = new \DateTime();

            foreach ($schedules as $schedule) {

                // Valid cron
                if (!CronExpression::isValidExpression($schedule->getCron())) {

                    $this->logger->error(
                        sprintf(
                            'Schedule "%s" has wrong cron expression "%s".',
                            $schedule->getName(),
                            $schedule->getCron()
                        )
                    );

                    break;
                }

                $cron = CronExpression::factory($schedule->getCron());

                // If schedule run first.
                if ($schedule->getUpdatedAt() === null) {
                    $schedule->setUpdatedAt($cron->getNextRunDate(new \DateTime()));
                }

                if ($schedule->getUpdatedAt()->getTimestamp() <= $now->getTimestamp()) {

                    if ($schedule->getType() === 'task') {

                        // Add task to queue.
                        $queue = new Queue();
                        $queue->setParams($schedule->getParams());
                        $queue->setPriority($schedule->getPriority());
                        $queue->setTask($schedule->getTask());

                        $em->persist($queue);
                        $schedule->setUpdatedAt($cron->getNextRunDate(new \DateTime()));

                        $this->logger->info(
                            sprintf(
                                'Schedule service add task "%s" to queue by schedule "%s".',
                                $schedule->getTask(),
                                $schedule->getName()
                            )
                        );
                    }

                    if ($schedule->getType() === 'cmd') {

                        $this->logger->info(
                            sprintf(
                                'Schedule service run cmd in background "%s".',
                                $schedule->getName()
                            )
                        );


                        $this->processUtils->runBackground($schedule->getCmd());
                    }

                }

            }

        });

        $em->clear();
    }
}
