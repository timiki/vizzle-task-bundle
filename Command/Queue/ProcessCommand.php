<?php

namespace Vizzle\TaskBundle\Command\Queue;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Vizzle\TaskBundle\Entity\Queue;

class ProcessCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('task:queue:process')
            ->setDescription('Task process')
            ->addArgument('task', InputArgument::REQUIRED, 'The task id in queue')
            ->setHelp(<<<EOT
The <info>task:queue:process</info> command execute task from queue by id:

<info>php bin/console task:queue:process</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io        = new SymfonyStyle($input, $output);
        $container = $this->getContainer();
        $em        = $container->get('doctrine.orm.entity_manager');
        $manager   = $container->get('vizzle.task.manager');
        $logger    = $container->get('logger');

        try {

            /* @var Queue $task */
            $task = $em->getRepository('VizzleTaskBundle:Queue')->find($input->getArgument('task'));

            // Is task exist in queue.
            if (!$task) {

                $io->error(
                    sprintf(
                        'Task with id "%s" not exist in queue.',
                        $input->getArgument('task')
                    )
                );

                return 1;
            }

            // Is task exist.
            if (!$manager->isTaskExist($task->getTask())) {
                $io->error(
                    sprintf(
                        'Task "%s" not exist',
                        $task->getTask()
                    )
                );

                return 1;
            }

            // Check status.
            if ($task->getStatus() !== 'PREPARE') {

                $io->error(
                    sprintf(
                        'Task "%s" in queue must has status "PREPARE", "%s" given.',
                        $task->getId(),
                        $task->getStatus()
                    )
                );

                return 1;
            }

            $this->executeTaskInQueue($task);

        } catch (\Exception $e) {

            $logger->error(
                sprintf(
                    'Queue process has error "%s" at server "%s".',
                    $e->getMessage(),
                    $container->getParameter('vizzle.server')
                )
            );

        }

        return 0;
    }
}

