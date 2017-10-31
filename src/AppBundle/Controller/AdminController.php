<?php

namespace AppBundle\Controller;

use AppBundle\DependencyInjection\EventsLogService\EventsLogService;
use AppBundle\DependencyInjection\ExportService\ExportService;
use AppBundle\DependencyInjection\SearchService\SearchService;
use AppBundle\Entity\Design2Visitor;
use AppBundle\Entity\File;
use AppBundle\Entity\Visitor;
use AppBundle\Form\VisitorType;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminAction(Request $request)
    {
        $userKeep = $this->get('user_keep');
        if (!$userKeep->isLogged()) {
            return $this->redirectToRoute('login');
        }
        if(!$userKeep->getCurrentUser()->isRoot()){
            return $this->redirectToRoute('main_page');
        }

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $form = $this->createForm(VisitorType::class, new Visitor());
        $form->handleRequest($request);

        $searchResult = null;

        if ($request->get('searchDocNum') || $request->get('searchSName') || $request->get('searchDateStart')) {
            /** @var SearchService $searchService */
            $searchService = $this->get('search_service');
            $searchResult = $searchService->search($request);
            if($searchResult){
                /** @var ExportService $exportService */
                $exportService = $this->get('export_service');
                $exportService->createCsv($searchResult,ExportService::FLAG_SEARCH);
            }
        }

        $documentId = $request->get('documentId');
        $typeVisitorId = $request->get('typeVisitorId');
        $fileNumber = $request->get('fileNum');
        $dateVisit = $request->get('dateVisit');
        $message = false;

        if ($form->isSubmitted()) {

            $file = $em->getRepository('AppBundle:File')->findOneBy(['number' => $fileNumber]);
            if ($file == null) {
                $file = new File();
                $file->setNumber($fileNumber);
                $file->setDescription('');
                $em->persist($file);
                $em->flush();
            }

            /** @var Visitor $visitor */
            $visitor = $form->getData();
            $dateVisit = new \DateTime(date($dateVisit));
            $visitor->setDateVisit($dateVisit);
            $visitor->setTypeFileId($file->getId());
            $visitor->setTypeVisitorId($typeVisitorId);
            if ($visitor->getTName() == null) {
                $visitor->setTName(' ');
            }

            $visitor->setTypeDocId($documentId);
            $em->persist($visitor);
            $em->flush();


            //Таблица связей
            $design2visitor = new Design2Visitor();
            $design2visitor->setVisitorId($visitor->getId());
            $design2visitor->setFileId($file->getId());
            $design2visitor->setDateCreated($visitor->getDateVisit());

            $em->persist($design2visitor);
            $em->flush();
            $message = 'Збережено !!!';
            
            /** @var EventsLogService $eventsLogService */
            $eventsLogService =  $this->get('events_log_service');
            $eventsLogService->saveEvent2DB($design2visitor->getId(),$userKeep->getCurrentUser()->getId(),EventsLogService::CREATE_EVENT);
        }

        $documents = $em->getRepository('AppBundle:Document')->findAll();
        $typeVisitors = $em->getRepository('AppBundle:TypeVisitor')->findAll();

        $templateParams = [
            'message' => $message,
            'form' => $form->createView(),
            'documents' => $documents,
            'typeVisitors' => $typeVisitors,
            'searchResult' => $searchResult,
            'user' => $userKeep->getCurrentUser(),
        ];

        return $this->render('default/index.html.twig', $templateParams);
    }
    
}
