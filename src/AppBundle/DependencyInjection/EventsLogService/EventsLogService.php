<?php

namespace AppBundle\DependencyInjection\EventsLogService;

use AppBundle\Entity\EventsLog;
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

    public function saveEvent2DB($d2visitorId,$userId,$eventType){
        $eventsLog = new EventsLog();
        $eventsLog->setD2visitorId($d2visitorId);
        $eventsLog->setUserId($userId);
        $eventsLog->setDate(new \DateTime(date('Y-m-d H:i:s')));
        $eventsLog->setEventType($eventType);
        
        $this->entityManager->persist($eventsLog);
        $this->entityManager->flush($eventsLog);
    }

}