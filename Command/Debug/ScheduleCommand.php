<?php

namespace Vizzle\TaskBundle\Command\Debug;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScheduleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('debug:schedule')
            ->setDescription('Displays schedule list')
            ->setHelp(<<<EOT
The <info>debug:schedule</info> displays schedule list:

<info>php bin/console debug:schedule</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $rows = [];

        foreach ($em->getRepository('VizzleTaskBundle:Schedule')->findAll() as $schedule) {
            $rows[] = [
                $schedule->getName(),
                $schedule->getCron(),
                $schedule->getTask(),
                json_encode($schedule->getParams()),
                $schedule->getEnabled() ? 'TRUE' : 'FALSE',
            ];
        }

        $io->table([
            'Name',
            'Cron',
            'Task',
            'Params (json)',
            'Enabled',
        ], $rows);
    }

}
