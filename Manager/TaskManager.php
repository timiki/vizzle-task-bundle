<?php

namespace Vizzle\TaskBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Vizzle\VizzleBundle\Mapper\MapperAwareInterface;
use Vizzle\VizzleBundle\Mapper\MapperAwareTrait;

/**
 * Task manager.
 */
class TaskManager implements ContainerAwareInterface, MapperAwareInterface
{
    use ContainerAwareTrait;
    use MapperAwareTrait;

    /**
     * Get task object instance.
     *
     * @param string $task   Task name.
     * @param array  $params Task params array.
     *
     * @return mixed|null
     */
    public function getTaskObject($task, $params = [])
    {
        if ($metadata = $this->getTaskMetadata($task)) {

            $task = new $metadata['class'];

            // Access to container
            if ($task instanceof ContainerAwareInterface) {
                $task->setContainer($this->container);
            }

            // Inject params

            $reflection = new \ReflectionObject($task);

            foreach ((array)$params as $name => $value) {

                if ($reflection->hasProperty($name)) {
                    $reflectionProperty = $reflection->getProperty($name);
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($task, $value);
                }

            }

            return $task;
        }

        return null;
    }

    /**
     * Check is task exist.
     *
     * @param string $task Task name.
     *
     * @return bool
     * @throws \Vizzle\Common\Mapper\Exceptions\InvalidMappingException
     */
    public function isTaskExist($task)
    {
        foreach ($this->mapper->getMetadata() as $metadata) {
            if ($metadata['name'] === strtolower($task)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get task metadata array.
     *
     * @param string $task Task name.
     *
     * @return array|null
     * @throws Exceptions\TaskNotFoundException
     */
    public function getTaskMetadata($task)
    {
        foreach ($this->mapper->getMetadata() as $metadata) {
            if ($metadata['name'] === strtolower($task)) {
                return $metadata;
            }
        }

        throw new Exceptions\TaskNotFoundException('Task ' . $task . ' not found');
    }

    /**
     * Get list tasks.
     *
     * @return array
     */
    public function getTasks()
    {
        $task = [];

        foreach ($this->mapper->getMetadata() as $meta) {
            $task[] = $meta['name'];
        }

        return $task;
    }
}