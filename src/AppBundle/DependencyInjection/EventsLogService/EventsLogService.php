<?php

namespace AppBundle\DependencyInjection\EventsLogService;

use AppBundle\Entity\EventsLog;
use AppBundle\Entity\File;
use AppBundle\Entity\Visitor;
use Doctrine\ORM\EntityManager;

/**
 * Class EventsLogService
 * @package AppBundle\DependencyInjection\ExportService
 */
class EventsLogService
{
    
    const EDIT_EVENT = 'edit';
    const CREATE_EVENT = 'create';
    
    /** @var  EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $d2visitorId
     * @param $userId
     * @param Visitor $visitorOld
     * @param $eventType
     */
    public function saveEvent2DB($d2visitorId,$userId,$visitorOld=null,$eventType){
        $eventsLog = new EventsLog();
        $eventsLog->setD2visitorId($d2visitorId);
        $eventsLog->setUserId($userId);
        $eventsLog->setDate(new \DateTime(date('Y-m-d H:i:s')));
        $eventsLog->setEventType($eventType);
        if($visitorOld){
            /** @var File $file */
            $file = $this->entityManager->getRepository('AppBundle:File')->find($visitorOld->getTypeFileId());
            $visitorOld->setTypeFileId($file->getNumber());
            $eventsLog->setObject($visitorOld);
        }else{
            $eventsLog->setObject($this->getVisitorObject($d2visitorId));
        }

        $this->entityManager->persist($eventsLog);
        $this->entityManager->flush($eventsLog);
    }
    
    private function getVisitorObject($d2visitorId){
       $visitorData =  $this->entityManager->getRepository('AppBundle:Design2Visitor')->searchById($d2visitorId);
        /** @var Visitor $visitor */
       $visitor = array_shift($visitorData[0]);
       $fNumber = array_shift($visitorData[0]);
       $visitor->setTypeFileId($fNumber);
       return $visitor;
    }

}