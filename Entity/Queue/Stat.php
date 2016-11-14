<?php

namespace Vizzle\TaskBundle\Entity\Queue;

use Doctrine\ORM\Mapping as ORM;
use Vizzle\TaskBundle\Entity\Queue;

/**
 * Task queue stat table.
 *
 * @ORM\Entity()
 * @ORM\Table(
 *     name="VTaskQueueStat",
 *     indexes={
 *          @ORM\Index(name="queueId", columns={"queueId"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Stat
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
     * Task server, where task executed.
     *
     * @ORM\Column(name="server", type="string", nullable=true)
     */
    private $server;

    /**
     * Time wait for run task in seconds.
     *
     * @var integer
     *
     * @ORM\Column(name="timeWait", type="decimal", scale=3)
     */
    private $timeWait = 0;

    /**
     * Task executing time in seconds.
     *
     * @var integer
     *
     * @ORM\Column(name="timeExecuting", type="decimal", scale=3)
     */
    private $timeExecuting = 0;

    /**
     * Task memory pick usage in bytes.
     *
     * @var integer
     *
     * @ORM\Column(name="memory", type="bigint")
     */
    private $memory = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * Task in queue.
     *
     * @var Queue
     *
     * @ORM\OneToOne(targetEntity="Vizzle\TaskBundle\Entity\Queue", mappedBy="stat")
     * @ORM\JoinColumn(name="queueId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $queue;

    /**
     * @var integer
     *
     * @ORM\Column(name="queueId", type="bigint")
     */
    private $queueId;

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
     * Set server
     *
     * @param string $server
     *
     * @return Stat
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
     * Set timeWait
     *
     * @param integer $timeWait
     *
     * @return Stat
     */
    public function setTimeWait($timeWait)
    {
        $this->timeWait = $timeWait;

        return $this;
    }

    /**
     * Get timeWait
     *
     * @return integer
     */
    public function getTimeWait()
    {
        return $this->timeWait;
    }

    /**
     * Set timeExecuting
     *
     * @param integer $timeExecuting
     *
     * @return Stat
     */
    public function setTimeExecuting($timeExecuting)
    {
        $this->timeExecuting = $timeExecuting;

        return $this;
    }

    /**
     * Get timeExecuting
     *
     * @return integer
     */
    public function getTimeExecuting()
    {
        return $this->timeExecuting;
    }

    /**
     * Set memory
     *
     * @param integer $memory
     *
     * @return Stat
     */
    public function setMemory($memory)
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Get memory
     *
     * @return integer
     */
    public function getMemory()
    {
        return $this->memory;
    }


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Stat
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
     * Set queueId
     *
     * @param integer $queueId
     *
     * @return Stat
     */
    public function setQueueId($queueId)
    {
        $this->queueId = $queueId;

        return $this;
    }

    /**
     * Get queueId
     *
     * @return integer
     */
    public function getQueueId()
    {
        return $this->queueId;
    }

    /**
     * Set queue
     *
     * @param \Vizzle\TaskBundle\Entity\Queue $queue
     *
     * @return Stat
     */
    public function setQueue(\Vizzle\TaskBundle\Entity\Queue $queue = null)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Get queue
     *
     * @return \Vizzle\TaskBundle\Entity\Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
