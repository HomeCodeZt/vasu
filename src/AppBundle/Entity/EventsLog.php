<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventsLog
 *
 * @ORM\Table(name="events_log")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventsLogRepository")
 */
class EventsLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="d2visitor_id", type="integer")
     */
    private $d2visitorId;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="event_type", type="string", length=1)
     */
    private $eventType;


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
     * Set d2visitorId
     *
     * @param integer $d2visitorId
     * @return EventsLog
     */
    public function setD2visitorId($d2visitorId)
    {
        $this->d2visitorId = $d2visitorId;

        return $this;
    }

    /**
     * Get d2visitorId
     *
     * @return integer 
     */
    public function getD2visitorId()
    {
        return $this->d2visitorId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return EventsLog
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return EventsLog
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
     * Set eventType
     *
     * @param string $eventType
     * @return EventsLog
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * Get eventType
     *
     * @return string 
     */
    public function getEventType()
    {
        return $this->eventType;
    }
}
