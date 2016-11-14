<?php

namespace Vizzle\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vizzle\TaskBundle\Entity\Queue\Stat;

/**
 * Task queue table.
 *
 * @ORM\Entity()
 * @ORM\Table(
 *     name="VTaskQueue",
 *     indexes={
 *          @ORM\Index(name="task", columns={"task"}),
 *          @ORM\Index(name="status", columns={"status"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Queue
{
    /**
     * Queue id.
     *
     * @var integer
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Task name.
     *
     * @var string
     *
     * @ORM\Column(name="task", type="string")
     */
    private $task;

    /**
     * Task params.
     *
     * @var array
     *
     * @ORM\Column(name="params", type="array")
     */
    private $params = [];

    /**
     * Task priority.
     *
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer", options={"default":"0"})
     */
    private $priority = 0;

    /**
     * Task status.
     *
     * @var string
     *
     * @ORM\Column(name="status", type="string", options={"default": "WAIT"})
     */
    private $status = 'WAIT';

    /**
     * Task result.
     *
     * @var string
     *
     * @ORM\Column(name="result", type="text", nullable=true)
     */
    private $result;

    /**
     * Task server, where task executed.
     *
     * @ORM\Column(name="server", type="string", nullable=true)
     */
    private $server;

    /**
     * Task pid on server.
     *
     * @var integer
     *
     * @ORM\Column(name="pid", type="integer", nullable=true)
     */
    private $pid;

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
     * @var \DateTime
     *
     * @ORM\Column(name="executedAt", type="datetime", nullable=true)
     */
    private $executedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completedAt", type="datetime", nullable=true)
     */
    private $completedAt;

    /**
     * Task stat.
     *
     * @var Stat
     *
     * @ORM\OneToOne(targetEntity="Vizzle\TaskBundle\Entity\Queue\Stat", mappedBy="queue")
     * @ORM\JoinColumn(name="statId", referencedColumnName="id", onDelete="SET NULL")
     */
    private $stat = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="statId", type="bigint", nullable=true)
     */
    private $statId;

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

        if ($this->getUpdatedAt() === null) {
            $this->setUpdatedAt(new \DateTime());
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
     * Set task
     *
     * @param string $task
     *
     * @return Queue
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
     * Set params
     *
     * @param array $params
     *
     * @return Queue
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
     * @return Queue
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
     * Set status
     *
     * @param string $status
     *
     * @return Queue
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set server
     *
     * @param string $server
     *
     * @return Queue
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get server
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set pid
     *
     * @param integer $pid
     *
     * @return Queue
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Get pid
     *
     * @return integer
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Queue
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
     * @return Queue
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

    /**
     * Set executedAt
     *
     * @param \DateTime $executedAt
     *
     * @return Queue
     */
    public function setExecutedAt($executedAt)
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    /**
     * Get executedAt
     *
     * @return \DateTime
     */
    public function getExecutedAt()
    {
        return $this->executedAt;
    }

    /**
     * Set completedAt
     *
     * @param \DateTime $completedAt
     *
     * @return Queue
     */
    public function setCompletedAt($completedAt)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * Get completedAt
     *
     * @return \DateTime
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * Set statId
     *
     * @param integer $statId
     *
     * @return Queue
     */
    public function setStatId($statId)
    {
        $this->statId = $statId;

        return $this;
    }

    /**
     * Get statId
     *
     * @return integer
     */
    public function getStatId()
    {
        return $this->statId;
    }

    /**
     * Set stat
     *
     * @param \Vizzle\TaskBundle\Entity\Queue\Stat $stat
     *
     * @return Queue
     */
    public function setStat(\Vizzle\TaskBundle\Entity\Queue\Stat $stat = null)
    {
        $this->stat = $stat;

        return $this;
    }

    /**
     * Get stat
     *
     * @return \Vizzle\TaskBundle\Entity\Queue\Stat
     */
    public function getStat()
    {
        return $this->stat;
    }

    /**
     * Set result
     *
     * @param string $result
     *
     * @return Queue
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }
}
