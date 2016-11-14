<?php

namespace Vizzle\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task schedule table.
 *
 * @ORM\Entity()
 * @ORM\Table(name="VSchedule")
 * @ORM\HasLifecycleCallbacks()
 */
class Schedule
{
    /**
     * Schedule id.
     *
     * @var integer
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Schedule name.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", unique=true)
     */
    private $name;

    /**
     * Type.
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string")
     */
    private $type = 'task';

    /**
     * Cmd.
     *
     * @var string
     *
     * @ORM\Column(name="cmd", type="string", nullable=true)
     */
    private $cmd;

    /**
     * Task name.
     *
     * @var string
     *
     * @ORM\Column(name="task", type="string", nullable=true)
     */
    private $task;

    /**
     * Cron.
     *
     * @var string
     *
     * @ORM\Column(name="cron", type="string")
     */
    private $cron;

    /**
     * Task params array.
     *
     * @var array
     *
     * @ORM\Column(name="params", type="array", nullable=true)
     */
    private $params;

    /**
     * Task priority.
     *
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer", options={"default":"0"})
     */
    private $priority = 0;

    /**
     * Is schedule enabled.
     *
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * On PrePersist
     *
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    //

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Schedule
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Schedule
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set cmd
     *
     * @param string $cmd
     *
     * @return Schedule
     */
    public function setCmd($cmd)
    {
        $this->cmd = $cmd;

        return $this;
    }

    /**
     * Get cmd
     *
     * @return string
     */
    public function getCmd()
    {
        return $this->cmd;
    }

    /**
     * Set task
     *
     * @param string $task
     *
     * @return Schedule
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return string
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set cron
     *
     * @param string $cron
     *
     * @return Schedule
     */
    public function setCron($cron)
    {
        $this->cron = $cron;

        return $this;
    }

    /**
     * Get cron
     *
     * @return string
     */
    public function getCron()
    {
        return $this->cron;
    }

    /**
     * Set params
     *
     * @param array $params
     *
     * @return Schedule
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return Schedule
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Schedule
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Schedule
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Schedule
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
