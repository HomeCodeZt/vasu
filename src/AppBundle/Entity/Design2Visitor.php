<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Design2Visitor
 *
 * @ORM\Table(name="design2visitor")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Design2VisitorRepository")
 */
class Design2Visitor
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
     * @ORM\Column(name="visitor_id", type="integer")
     */
    private $visitorId;

    /**
     * @var int
     *
     * @ORM\Column(name="file_id", type="integer")
     */
    private $fileId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime")
     */
    private $dateCreated;


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
     * Set visitorId
     *
     * @param integer $visitorId
     * @return Design2Visitor
     */
    public function setVisitorId($visitorId)
    {
        $this->visitorId = $visitorId;

        return $this;
    }

    /**
     * Get visitorId
     *
     * @return integer 
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * Set fileId
     *
     * @param integer $fileId
     * @return Design2Visitor
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;

        return $this;
    }

    /**
     * Get fileId
     *
     * @return integer 
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return Design2Visitor
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime 
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}
