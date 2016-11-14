<?php

namespace Vizzle\TaskBundle\Mapping;

/**
 * Mark class as vizzle task.
 *
 * @Annotation
 * @Target("CLASS")
 */
final class Task
{
    /**
     * Task name.
     *
     * @var string
     */
    public $name;

    /**
     * Task description.
     *
     * @var string
     */
    public $description;
}
