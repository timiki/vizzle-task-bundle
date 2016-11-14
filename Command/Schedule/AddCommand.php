<?php

namespace Vizzle\TaskBundle\Command\Schedule;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Cron\CronExpression;
use Vizzle\TaskBundle\Entity\Schedule;

class AddCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('schedule:add')
            ->setDescription('Add new schedule')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The schedule name')
            ->addOption('cron', null, InputOption::VALUE_OPTIONAL, 'The cron expression')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The cron expression')
            ->addOption('task', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE, 'The task name')
            ->addOption('cmd', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE, 'The cmd for execute')
            ->addOption('params', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE, 'The task params')
            ->addOption('priority', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE, 'The task priority')
            ->setHelp(<<<EOT
The <info>schedule:add</info> command helps you add new schedule for
add task by cron expression:

<info>php bin/console schedule:add</info>
EOT
            );
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Add new schedule');

        // Name
        if (empty($input->getOption('name'))) {

            $input->setOption(
                'name',
                $io->ask('Schedule name', null, [$this, 'isValidName'])
            );

        }

        // Cron
        if (empty($input->getOption('cron'))) {

            $input->setOption(
                'cron',
                $io->ask('Cron expression', '* * * * *', [$this, 'isValidCron'])
            );

        }

        // Type
        if (empty($input->getOption('type'))) {

            $input->setOption(
                'type',
                $io->ask('Type', 'task', [$this, 'isValidType'])
            );

        }

        // Cmd
        if (empty($input->getOption('cmd')) && $input->getOption('type') === 'cmd') {

            $input->setOption(
                'cmd',
                $io->ask('Cmd', null, [$this, 'isValidCmd'])
            );

        }

        // Task
        if (empty($input->getOption('task')) && $input->getOption('type') === 'task') {

            $input->setOption(
                'task',
                $io->ask('Task name', null, [$this, 'isValidTask'])
            );

        }

        // Params
        if (empty($input->getOption('params'))) {

            $io->text('You must enter task params in json format or set empty. For example: {param1:value1, param2:value2}');

            $input->setOption(
                'params',
                $io->ask('Task params', '{}', [$this, 'isValidParams'])
            );

        }

        // Priority
        if (empty($input->getOption('priority'))) {

            $input->setOption(
                'priority',
                $io->ask('Task priority', 0, [$this, 'isValidPriority'])
            );

        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io        = new SymfonyStyle($input, $output);
        $container = $this->getContainer();
        $em        = $container->get('doctrine.orm.entity_manager');

        $schedule = new Schedule();

        $name = $this->isValidName($input->getOption('name'));
        $cron = $this->isValidCron($input->getOption('cron'));
        $type = $this->isValidType($input->getOption('type'));

        if ($type === 'cmd') {
            $schedule->setCmd($this->isValidCmd($input->getOption('cmd')));
        }

        if ($type === 'task') {
            $schedule->setTask($this->isValidTask($input->getOption('task')));
        }

        $params   = $this->isValidParams($input->getOption('params'));
        $priority = $this->isValidPriority($input->getOption('priority'));

        $params = json_decode($params, true);

        $schedule->setName($name);
        $schedule->setCron($cron);
        $schedule->setParams($params);
        $schedule->setPriority($priority);

        $io->ask('Is enabled', 'yes', function ($answer) use ($schedule) {
            $schedule->setEnabled($answer === 'yes');

            return $answer;
        });

        $em->persist($schedule);
        $em->flush($schedule);

        $io->success(sprintf(
            'Schedule "%s" was success added.',
            $name
        ));

        return 0;
    }

    public function isValidName($name)
    {
        $name = ltrim($name, '=:');

        $em                 = $this->getContainer()->get('doctrine.orm.entity_manager');
        $scheduleRepository = $em->getRepository('VizzleTaskBundle:Schedule');

        if (empty($name)) {
            throw new \RuntimeException('Schedule name can`t be empty .');
        }

        if ($scheduleRepository->findOneBy(['name' => $name])) {
            throw new \RuntimeException(sprintf(
                'Schedule "%s" already exist.',
                $name
            ));
        }

        return $name;
    }

    public function isValidType($type)
    {
        $type = ltrim($type, '=:');
        $type = strtolower($type);

        if (empty($type)) {
            throw new \RuntimeException('Type can`t be empty .');
        }

        if (!in_array($type, ['cmd', 'task'])) {
            throw new \RuntimeException('Type must be cmd or task');
        }

        return $type;
    }

    public function isValidCmd($cmd)
    {
        $cmd = ltrim($cmd, '=:');

        if (empty($cmd)) {
            throw new \RuntimeException('Cmd can`t be empty .');
        }

        return $cmd;
    }

    public function isValidTask($task)
    {
        $task = ltrim($task, '=:');

        if (empty($task)) {
            throw new \RuntimeException('Task name can`t be empty .');
        }

        return $task;
    }

    public function isValidCron($cron)
    {
        $cron = ltrim($cron, '=:');

        if (empty($cron)) {
            return '* * * * *';
        }

        if (!CronExpression::isValidExpression($cron)) {
            throw new \RuntimeException(sprintf(
                'Invalid cron expression "%s".',
                $cron
            ));
        }

        return $cron;
    }

    public function isValidParams($params)
    {
        $params = ltrim($params, '=:');

        if (empty($params)) {
            return '{}';
        }

        json_decode($params);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid json format.');
        }

        return $params;
    }

    public function isValidPriority($priority)
    {
        $priority = ltrim($priority, '=:');

        if (empty($priority)) {
            return 0;
        }

        if (!is_numeric($priority) || $priority < 0) {
            throw new \RuntimeException('Priority must be number, and > 0.');
        }

        return $priority;
    }

}
