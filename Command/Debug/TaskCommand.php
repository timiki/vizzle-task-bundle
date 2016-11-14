<?php

namespace Vizzle\TaskBundle\Command\Debug;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('debug:task')
            ->setDescription('Displays current task list')
            ->setHelp(<<<EOT
The <info>debug:task</info> displays the tasks:

<info>php bin/console debug:task</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io     = new SymfonyStyle($input, $output);
        $mapper = $this->getContainer()->get('vizzle.task.mapper');

        $rows = [];

        foreach ($mapper->getMetadata() as $meta) {
            $rows[] = [
                $meta['name'],
                $meta['class'],
                $meta['description'],
            ];
        }

        $io->table([
            'Name',
            'Class',
            'Description',
        ], $rows);
    }

}
