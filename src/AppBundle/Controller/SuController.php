<?php

namespace AppBundle\Controller;

use AppBundle\DependencyInjection\ExportService\ExportService;
use AppBundle\DependencyInjection\SearchService\SearchService;
use AppBundle\Entity\EventsLog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SuController extends Controller
{
    public function LogAction(Request $request){
        
        $userKeep = $this->get('user_keep');
        if (!$userKeep->isLogged()) {
            return $this->redirectToRoute('login');
        }
        if(!$userKeep->getCurrentUser()->isRoot() && !$userKeep->getCurrentUser()->isSu()){
            return $this->redirectToRoute('main_page');
        }

        return $this->render('default/log.html.twig',['user'=>$userKeep->getCurrentUser()]);
    }

    public function logExportAction(Request $request){

        $userKeep = $this->get('user_keep');
        if (!$userKeep->isLogged()) {
            return $this->redirectToRoute('login');
        }
        if(!$userKeep->getCurrentUser()->isRoot() && !$userKeep->getCurrentUser()->isSu()){
            return $this->redirectToRoute('main_page');
        }

        $id = $request->get('id');
        $startDate = $request->get('searchDateStart');
        $endDate = $request->get('searchDateEnd');

        if($id != null && $id > 0){
            return $this->exportById($id,$userKeep->getCurrentUser());
        }

        if($startDate){
            return $this->exportByDate($startDate,$endDate,$userKeep->getCurrentUser());
        }

        return $this->redirectToRoute('log');
    }

    /**
     * @param $id
     * @param $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function exportById($id,$user){
        /** @var SearchService $searchService */
        $searchService = $this->get('search_service');
        $result = $searchService->gelEventsByUserId($id);
        $eventsLogObject = $searchService->gelEventsLogObjectByUserId($id);
        /**
         * @var  $key
         * @var EventsLog $eventLog
         */
        foreach ($eventsLogObject as $key =>$eventLog){
            $eventsLogObject[$key] = $eventLog->getObject();
        }
        return $this->render('default/log.html.twig',['result'=>$result,'user'=>$user,'eventsLogObject'=>$eventsLogObject]);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function exportByDate($startDate, $endDate,$user){
        /** @var SearchService $searchService */
        $searchService = $this->get('search_service');
        /** @var ExportService $exportService */
        $exportService = $this->get('export_service');

        $result = $searchService->gelEventsByDate($startDate,$endDate);
        $exportService->createCsv($result,ExportService::FLAG_LOG);
        return $this->render('default/log.html.twig',['result'=>$result,'user'=>$user]);
    }
}
