<?php

namespace Vizzle\TaskBundle\Entity\Stat;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stat queue task table.
 *
 * @ORM\Entity()
 * @ORM\Table(
 *     name="VStatTaskQueue",
 *     indexes={
 *          @ORM\Index(name="date", columns={"date"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class TaskQueue
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
     * Stat date.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * Total task count on date.
     *
     * @var string
     *
     * @ORM\Column(name="total", type="integer")
     */
    private $total = 0;

    /**
     * Wait task count on date.
     *
     * @var string
     *
     * @ORM\Column(name="wait", type="integer")
     */
    private $wait = 0;

    /**
     * Run task count on date.
     *
     * @var string
     *
     * @ORM\Column(name="run", type="integer")
     */
    private $run = 0;

    /**
     * Error task count on date.
     *
     * @var string
     *
     * @ORM\Column(name="error", type="integer")
     */
    private $error = 0;

    /**
     * Average wait time (1h);
     *
     * @var string
     *
     * @ORM\Column(name="aWaitTime", type="decimal", scale=3)
     */
    private $aWaitTime = 0;

    /**
     * Average executing time (1h);
     *
     * @var string
     *
     * @ORM\Column(name="aExecutingTime", type="decimal", scale=3)
     */
    private $aExecutingTime = 0;

    /**
     * Average memory (1h);
     *
     * @var string
     *
     * @ORM\Column(name="aMemory", type="bigint")
     */
    private $aMemory = 0;

    /**
     * Average memory total (1h);
     *
     * @var string
     *
     * @ORM\Column(name="aMemoryTotal", type="bigint")
     */
    private $aMemoryTotal = 0;

    /**
     * On PrePersist
     *
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->aMemoryTotal = $this->getAMemory() * $this->getRun();
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return TaskQueue
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set total
     *
     * @param integer $total
     *
     * @return TaskQueue
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set wait
     *
     * @param integer $wait
     *
     * @return TaskQueue
     */
    public function setWait($wait)
    {
        $this->wait = $wait;

        return $this;
    }

    /**
     * Get wait
     *
     * @return integer
     */
    public function getWait()
    {
        return $this->wait;
    }

    /**
     * Set run
     *
     * @param integer $run
     *
     * @return TaskQueue
     */
    public function setRun($run)
    {
        $this->run = $run;

        return $this;
    }

    /**
     * Get run
     *
     * @return integer
     */
    public function getRun()
    {
        return $this->run;
    }

    /**
     * Set error
     *
     * @param integer $error
     *
     * @return TaskQueue
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return integer
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set aWaitTime
     *
     * @param string $aWaitTime
     *
     * @return TaskQueue
     */
    public function setAWaitTime($aWaitTime)
    {
        $this->aWaitTime = $aWaitTime;

        return $this;
    }

    /**
     * Get aWaitTime
     *
     * @return string
     */
    public function getAWaitTime()
    {
        return $this->aWaitTime;
    }

    /**
     * Set aExecutingTime
     *
     * @param string $aExecutingTime
     *
     * @return TaskQueue
     */
    public function setAExecutingTime($aExecutingTime)
    {
        $this->aExecutingTime = $aExecutingTime;

        return $this;
    }

    /**
     * Get aExecutingTime
     *
     * @return string
     */
    public function getAExecutingTime()
    {
        return $this->aExecutingTime;
    }

    /**
     * Set aMemory
     *
     * @param integer $aMemory
     *
     * @return TaskQueue
     */
    public function setAMemory($aMemory)
    {
        $this->aMemory = $aMemory;

        return $this;
    }

    /**
     * Get aMemory
     *
     * @return integer
     */
    public function getAMemory()
    {
        return $this->aMemory;
    }

    /**
     * Set aMemoryTotal
     *
     * @param integer $aMemoryTotal
     *
     * @return TaskQueue
     */
    public function setAMemoryTotal($aMemoryTotal)
    {
        $this->aMemoryTotal = $aMemoryTotal;

        return $this;
    }

    /**
     * Get aMemoryTotal
     *
     * @return integer
     */
    public function getAMemoryTotal()
    {
        return $this->aMemoryTotal;
    }
}
