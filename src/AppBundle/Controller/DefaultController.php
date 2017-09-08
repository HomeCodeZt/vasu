<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Design2Visitor;
use AppBundle\Entity\File;
use AppBundle\Entity\Visitor;
use AppBundle\Form\VisitorType;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->createForm(VisitorType::class, new Visitor());
        
        $form->handleRequest($request);

        $searchResult = null;
        
        if($request->get('searchDocNum') || $request->get('searchSName') || $request->get('searchDateStart') ){
            $searchResult =  $this->searchAction($request);
        }
        
        $documentId = $request->get('documentId');
        $typeVisitorId = $request->get('typeVisitorId');
        $fileNumber = $request->get('fileNum');

        if($form->isSubmitted()){

            $file = $em->getRepository('AppBundle:File')->findOneBy(['number'=>$fileNumber]);
            if($file == null){
                $file = new File();
                $file->setNumber($fileNumber);
                $file->setDescription('');
            }

            /** @var Visitor $visitor */
            $visitor = $form->getData();
            $visitor->setTypeDocId($documentId);
            $visitor->setTypeVisitorId($typeVisitorId);
            $em->persist($file);
            $em->persist($visitor);
            $em->flush();


            //Таблица связей
            $design2visitor = new Design2Visitor();
            $design2visitor->setVisitorId($visitor->getId());
            $design2visitor->setFileId($file->getId());
            $design2visitor->setDateCreated(new \DateTime('now'));

            $em->persist($design2visitor);
            $em->flush();

        }
        
        $documents = $em->getRepository('AppBundle:Document')->findAll();
        $typeVisitors = $em->getRepository('AppBundle:TypeVisitor')->findAll();
        
        $templateParams = [
            'form' => $form->createView(),
            'documents'=>$documents,
            'typeVisitors' =>$typeVisitors,
            'searchResult' => $searchResult
        ];
        
        return $this->render('default/index.html.twig', $templateParams);
    }

    public function searchAction(Request $request)
    {
        $searchDocNum =   $request->get('searchDocNum');  
        $searchSName =  $request->get('searchSName');
        $searchDateStart = $request->get('searchDateStart');
        $searchDateEnd = $request->get('searchDateEnd') ? $request->get('searchDateStart') : new \DateTime('now') ;

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $result = $em->getRepository('AppBundle:Design2Visitor')->search($searchDocNum,$searchSName,$searchDateStart,$searchDateEnd);
        return $result;
    }
}
