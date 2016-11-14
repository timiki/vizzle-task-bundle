<?php

namespace Vizzle\TaskBundle\Command\Schedule;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('schedule:remove')
            ->setDescription('Remove schedule')
            ->addOption('name', 's', InputOption::VALUE_OPTIONAL, 'The schedule name')
            ->setHelp(<<<EOT
The <info>schedule:remove</info> command helps you remove schedule:

<info>php bin/console schedule:remove</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io        = new SymfonyStyle($input, $output);
        $container = $this->getContainer();
        $em        = $container->get('doctrine.orm.entity_manager');

        $name = $input->getOption('name');
        $name = ltrim($name, '=:');

        if (empty($name)) {
            throw new \RuntimeException('Schedule name can`t be empty .');
        }

        $scheduleRepository = $em->getRepository('VizzleTaskBundle:Schedule');
        $schedule           = $scheduleRepository->findOneBy(['name' => $name]);

        if (!$schedule) {
            throw new \RuntimeException(sprintf(
                'Schedule "%s" not exist.',
                $name
            ));
        }

        $em->remove($schedule);
        $em->flush($schedule);

        $io->success(sprintf(
            'Schedule "%s" was success remove.',
            $name
        ));

        return 0;
    }
}
