<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ExportController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function exportAction(Request $request)
    {
        $userKeep = $this->get('user_keep');
        if ($userKeep->isLogged() && $userKeep->getCurrentUser()->isRoot()) {
            $file = 'export/report.csv';
            $response = new Response();
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', "filename=report.csv");
            $response->setContent(file_get_contents($file));
            return $response;
        }else{
            return $this->render('default/404.html.twig',['message'=>'Доступ заборонений']);
        }
    }
    
}
