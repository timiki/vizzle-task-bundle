<?php

namespace Vizzle\TaskBundle\Mapper;

use Vizzle\VizzleBundle\Mapper\Exceptions\InvalidMappingException;
use Vizzle\VizzleBundle\Mapper\AbstractMapper;
use Vizzle\TaskBundle\Mapping;

/**
 * Task mapper.
 */
class TaskMapper extends AbstractMapper
{
    /**
     * Process class.
     *
     * @param \ReflectionClass $reflectionClass Class
     *
     * @return array|null
     * @throws InvalidMappingException
     */
    public function processReflectionClass($reflectionClass)
    {
        if ($task = $this->reader->getClassAnnotation($reflectionClass, Mapping\Task::class)) {

            $metadata = [];

            if (empty($task->name)) {
                throw new InvalidMappingException(
                    sprintf(
                        '@Task annotation must have name in class "%s".',
                        $reflectionClass->getName()
                    )
                );
            }

            $metadata['class']       = $reflectionClass->getName();
            $metadata['file']        = $reflectionClass->getFileName();
            $metadata['name']        = strtolower($task->name);
            $metadata['description'] = $task->description;

            // Find execute methods

            $metadata['execute'] = null;

            foreach ($reflectionClass->getMethods() as $reflectionMethod) {

                if ($annotation = $this->reader->getMethodAnnotation($reflectionMethod, Mapping\Execute::class)) {

                    if ($metadata['execute'] !== null){

                        throw new InvalidMappingException(
                            sprintf(
                                '@Execute annotation must be one in class "%s".',
                                $reflectionClass->getName()
                            )
                        );

                    }

                    $metadata['execute'] = $reflectionMethod->name;
                }

            }

            // Find params

            $metadata['params'] = [];

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {

                if ($annotation = $this->reader->getPropertyAnnotation($reflectionProperty, Mapping\Param::class)) {
                    $metadata['params'][] = $reflectionProperty->name;
                }

            }

            return $metadata;
        }

        return null;
    }

}
