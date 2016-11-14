<?php

namespace Vizzle\TaskBundle\Command\Generate;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Style\SymfonyStyle;
use Vizzle\VizzleBundle\Command\Generate\AbstractCommand;

class TaskCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:task')
            ->setDescription('Generates a new task')
            ->addOption('bundle', 'b', InputOption::VALUE_OPTIONAL, 'The bundle where the task is generated')
            ->addOption('name', 't', InputOption::VALUE_OPTIONAL, 'The task name')
            ->setHelp(<<<EOT
The <info>generate:task</info> command helps you generate new task
inside bundles:

<info>php bin/console generate:task</info>
EOT
            );
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate new task');

        // Bundle name
        if (empty($input->getOption('bundle'))) {

            $input->setOption(
                'bundle',
                $io->ask('Bundle name', null, [$this, 'isValidBundle'])
            );

        }

        // Task name
        if (empty($input->getOption('name'))) {

            $input->setOption(
                'name',
                $io->ask('Task name', null, [$this, 'isValidName'])
            );

        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $bundle     = $this
            ->getContainer()
            ->get('kernel')
            ->getBundle(
                $this->isValidBundle($input->getOption('bundle'))
            );
        $name       = $this->isValidName($input->getOption('name'));
        $filesystem = new Filesystem();

        $bundleDir = $bundle->getPath();
        $taskDir   = $bundleDir . '/Task';

        $filesystem->mkdir($taskDir);

        $taskClassName = $this->classify($name) . 'Task';
        $taskFile      = $taskDir . '/' . $taskClassName . '.php';

        if ($filesystem->exists($taskFile)) {
            throw new \RuntimeException(sprintf(
                'Task "%s" already exists',
                $name
            ));
        }

        $parameters = [
            'namespace' => $bundle->getNamespace(),
            'class'     => $taskClassName,
            'name'      => $name,
        ];

        $this->renderFile('Task.php.twig', $taskFile, $parameters);

        $io->success(sprintf(
            'Task "%s" was generate in file "%s".',
            $name,
            $taskFile
        ));

        return 0;
    }

    /**
     * Get the twig environment path to skeletons.
     *
     * @return string
     */
    public function getTwigPath()
    {
        return dirname(__DIR__) . '/../Resources/skeleton';
    }

    public function isValidName($name)
    {
        $name = ltrim($name, '=:');

        if (empty($name)) {
            throw new \RuntimeException('Task name can`t be empty.');
        }

        $name = str_replace(' ', ':', $name);

        if ($this->getContainer()->get('vizzle.task.manager')->isTaskExist($name)) {
            throw new \RuntimeException(sprintf(
                'Task "%s" already exist.',
                $name
            ));
        }

        return $name;
    }

    public function isValidBundle($bundle)
    {
        $bundle = ltrim($bundle, '=:');

        try {
            $this->getContainer()->get('kernel')->getBundle($bundle);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'Bundle "%s" does not exist.',
                $bundle
            ));
        }

        return $bundle;
    }
}
