<?php

namespace AppBundle\Controller;

use AppBundle\DependencyInjection\SearchService\SearchService;
use AppBundle\Entity\Design2Visitor;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class SearchController extends Controller
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function searchAction(Request $request)
    {
        $userKeep = $this->get('user_keep');
        if (!$userKeep->isLogged()) {
            return $this->redirectToRoute('login');
        }

        /** @var SearchService $searchService */
        $searchService = $this->get('search_service');
        $result = $searchService->search($request);
        
        if ($userKeep->getCurrentUser()->isRoot()) {
            if($result){
                $exportService = $this->get('export_service');
                $exportService->createCsv($result);
            }
        }

        $templateParams = [
            'message' => '',
            'searchResult' => $result,
            'user' => $userKeep->getCurrentUser(),
        ];

        return $this->render('default/search_page.html.twig', $templateParams);
    }
    
}
