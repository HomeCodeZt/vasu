<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Visitor
 *
 * @ORM\Table(name="visitor")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VisitorRepository")
 */
class Visitor
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
     * @var string
     *
     * @ORM\Column(name="f_name", type="string", length=50)
     */
    private $fName;

    /**
     * @var string
     *
     * @ORM\Column(name="s_name", type="string", length=100)
     */
    private $sName;

    /**
     * @var string
     *
     * @ORM\Column(name="t_name", type="string", length=100)
     */
    private $tName;

    /**
     * @var int
     *
     * @ORM\Column(name="type_visitor_id", type="integer")
     */
    private $typeVisitorId;

    /**
     * @var int
     *
     * @ORM\Column(name="type_doc_id", type="integer")
     */
    private $typeDocId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_visit", type="datetime")
     */
    private $dateVisit;

    /**
     * @var string
     *
     * @ORM\Column(name="doc_num", type="string", length=200)
     */
    private $docNum;

    /**
     * @var int
     *
     * @ORM\Column(name="type_file_id", type="integer")
     */
    private $typeFileId;


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
     * Set fName
     *
     * @param string $fName
     * @return Visitor
     */
    public function setFName($fName)
    {
        $this->fName = $fName;

        return $this;
    }

    /**
     * Get fName
     *
     * @return string 
     */
    public function getFName()
    {
        return $this->fName;
    }

    /**
     * Set sName
     *
     * @param string $sName
     * @return Visitor
     */
    public function setSName($sName)
    {
        $this->sName = $sName;

        return $this;
    }

    /**
     * Get sName
     *
     * @return string 
     */
    public function getSName()
    {
        return $this->sName;
    }

    /**
     * Set tName
     *
     * @param string $tName
     * @return Visitor
     */
    public function setTName($tName)
    {
        if($tName){
            $this->tName = $tName;
        }else{
            $this->tName = '';
        }


        return $this;
    }

    /**
     * Get tName
     *
     * @return string 
     */
    public function getTName()
    {
        return $this->tName;
    }

    /**
     * Set typeVisitorId
     *
     * @param integer $typeVisitorId
     * @return Visitor
     */
    public function setTypeVisitorId($typeVisitorId)
    {
        $this->typeVisitorId = $typeVisitorId;

        return $this;
    }

    /**
     * Get typeVisitorId
     *
     * @return integer 
     */
    public function getTypeVisitorId()
    {
        return $this->typeVisitorId;
    }

    /**
     * Set typeDocId
     *
     * @param integer $typeDocId
     * @return Visitor
     */
    public function setTypeDocId($typeDocId)
    {
        $this->typeDocId = $typeDocId;

        return $this;
    }

    /**
     * Get typeDocId
     *
     * @return integer 
     */
    public function getTypeDocId()
    {
        return $this->typeDocId;
    }

    /**
     * Set dateVisit
     *
     * @param \DateTime $dateVisit
     * @return Visitor
     */
    public function setDateVisit($dateVisit)
    {
        $this->dateVisit = $dateVisit;

        return $this;
    }

    /**
     * Get dateVisit
     *
     * @return \DateTime 
     */
    public function getDateVisit()
    {
        return $this->dateVisit;
    }

    /**
     * @return string
     */
    public function getDocNum()
    {
        return $this->docNum;
    }

    /**
     * @param string $docNum
     */
    public function setDocNum($docNum)
    {
        $this->docNum = $docNum;
    }

    /**
     * @param int $typeFileId
     */
    public function setTypeFileId($typeFileId)
    {
        $this->typeFileId = $typeFileId;
    }

    /**
     * @return int
     */
    public function getTypeFileId()
    {
        return $this->typeFileId;
    }   
    
    
}
