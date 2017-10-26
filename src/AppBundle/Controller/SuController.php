<?php

namespace AppBundle\Controller;

use AppBundle\DependencyInjection\SearchService\SearchService;
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

        return $this->render('default/log.html.twig',[]);
    }

    public function logExportAction(Request $request){
        $id = $request->get('id');
        $startDate = $request->get('searchDateStart');
        $endDate = $request->get('searchDateEnd');

        if($id != null && $id > 0){
            return $this->exportById($id);
        }

        if($startDate && $endDate){
            return $this->exportByDate($startDate,$endDate);
        }

        return $this->redirectToRoute('log');
    }

    private function exportById($id){
        /** @var SearchService $searchService */
        $searchService = $this->get('search_service');
        $result = $searchService->gelEventsByUserId($id);
        return $this->render('default/log.html.twig',['result'=>$result]);
    }

    private function exportByDate($startDate, $endDate){

    }
}
