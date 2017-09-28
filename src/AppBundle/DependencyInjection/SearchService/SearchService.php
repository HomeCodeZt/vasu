<?php

namespace AppBundle\DependencyInjection\SearchService;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class ExportService
 * @package AppBundle\DependencyInjection\ExportService
 */
class SearchService
{
    /** @var EntityManager  */
    private $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        /** @var EntityManager entityManager */
        $this->entityManager = $entityManager;
    }

    public function search(Request $request){
        $searchDocNum = $request->get('searchDocNum');
        $searchSName = $request->get('searchSName');
        $searchDateStart = str_replace('.', '-', $request->get('searchDateStart'));
        if ($searchDateStart) {
            $searchDateStart = new \DateTime(date($searchDateStart));
            $searchDateEnd = $request->get('searchDateEnd') != null ? new \DateTime(
                date(str_replace('.', '-', $request->get('searchDateEnd')))
            ) : new \DateTime('now');
        } else {
            $searchDateStart = null;
            $searchDateEnd = null;
        }
        
        if ($searchDocNum || $searchSName || $searchDateStart || $searchDateEnd) {
            $result = $this->entityManager->getRepository('AppBundle:Design2Visitor')->search(
                $searchDocNum,
                $searchSName,
                $searchDateStart,
                $searchDateEnd
            );
        } else {
            $result = null;
        }
        
        return $result;
    }
    
}