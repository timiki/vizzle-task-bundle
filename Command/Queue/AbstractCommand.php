<?php

namespace Vizzle\TaskBundle\Command\Queue;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Vizzle\TaskBundle\Entity\Queue;

abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * Only execute task from queue.
     *
     * @param Queue $task Task in queue.
     *
     * @return Queue
     */
    protected function executeTaskInQueue(Queue $task)
    {
        $runAt = microtime(true);

        $em         = $this->getContainer()->get('doctrine.orm.entity_manager');
        $manager    = $this->getContainer()->get('vizzle.task.manager');
        $taskMeta   = $manager->getTaskMetadata($task->getTask());
        $taskObject = $manager->getTaskObject($task->getTask(), $task->getParams());

        // Update task status.
        $task->setStatus('RUN');
        $task->setPid(getmypid());
        $task->setUpdatedAt(new \DateTime());
        $task->setExecutedAt(new \DateTime());
        $task->setServer($this->getContainer()->getParameter('vizzle.server'));
        $em->flush($task);

        $exceptions = [];
        $result     = null;

        try {
            $result = $taskObject->{$taskMeta['execute']}();
        } catch (\Exception $e) {
            $exceptions[] = $e;
        }

        // Task has error
        if (count($exceptions) > 0) {
            $task->setStatus('ERROR');
        } else {

            $task->setStatus('COMPLETE');

            if (is_string($result)) {
                $task->setResult($result);
            }

        }

        $em->flush($task);

        // Stat
        $stat = $em->getRepository('VizzleTaskBundle:Queue\Stat')->findOneBy(['queueId' => $task->getId()]);
        if (!$stat) {
            $stat = new Queue\Stat();
        }

        $stat->setTimeExecuting(microtime(true) - $runAt);
        $stat->setMemory(memory_get_peak_usage());
        $stat->setTimeWait($runAt - $task->getCreatedAt()->getTimestamp());
        $stat->setServer($this->getContainer()->getParameter('vizzle.server'));
        $stat->setQueue($task);

        $task->setCompletedAt(new \DateTime());
        $task->setUpdatedAt(new \DateTime());
        $task->setStat($stat);

        if (!$stat->getId()) {
            $em->persist($stat);
        }

        $em->flush($stat);
        $em->flush($task);

        return $task;
    }
}

