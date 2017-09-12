<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Design2Visitor;
use AppBundle\Entity\File;
use AppBundle\Entity\Visitor;
use AppBundle\Form\VisitorType;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\DateTime;

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
        $dateVisit = $request->get('dateVisit');

        if($form->isSubmitted()){

            $file = $em->getRepository('AppBundle:File')->findOneBy(['number'=>$fileNumber]);
            if($file == null){
                $file = new File();
                $file->setNumber($fileNumber);
                $file->setDescription('');
            }

            /** @var Visitor $visitor */
            $visitor = $form->getData();
            $dateVisit = new \DateTime(date($dateVisit));
            $visitor->setDateVisit($dateVisit);
            $visitor->setTypeDocId($documentId);
            $visitor->setTypeVisitorId($typeVisitorId);
            $em->persist($file);
            $em->persist($visitor);
            $em->flush();


            //Таблица связей
            $design2visitor = new Design2Visitor();
            $design2visitor->setVisitorId($visitor->getId());
            $design2visitor->setFileId($file->getId());
            $design2visitor->setDateCreated($visitor->getDateVisit());

            $em->persist($design2visitor);
            $em->flush();

        }
        
        $documents = $em->getRepository('AppBundle:Document')->findAll();
        $typeVisitors = $em->getRepository('AppBundle:TypeVisitor')->findAll();
        
        $templateParams = [
            'form' => $form->createView(),
            'documents'=>$documents,
            'typeVisitors' =>$typeVisitors,
            'searchResult' => $searchResult,
        ];
        
        return $this->render('default/index.html.twig', $templateParams);
    }

    public function searchAction(Request $request)
    {
        $searchDocNum =   $request->get('searchDocNum');  
        $searchSName =  $request->get('searchSName');
        $searchDateStart = str_replace('.','-',$request->get('searchDateStart'));
        if($searchDateStart){
            $searchDateStart = new \DateTime(date($searchDateStart));
            $searchDateEnd = $request->get('searchDateEnd') != null ? new \DateTime(date(str_replace('.','-',$request->get('searchDateEnd')))) : new \DateTime('now') ;
        }else{
            $searchDateStart = null;
            $searchDateEnd = null;
        }


        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $result = $em->getRepository('AppBundle:Design2Visitor')->search($searchDocNum,$searchSName,$searchDateStart,$searchDateEnd);
        return $result;
    }
    
    public function editAction(Request $request,$id){
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $visitor = $em->getRepository('AppBundle:Visitor')->find($id);
        if($visitor){
            $form = $this->createForm(VisitorType::class, $visitor);
            $form->handleRequest($request);

            if($form->isSubmitted()){

                $documentId = $request->get('documentId');
                $typeVisitorId = $request->get('typeVisitorId');
                $fileNumber = $request->get('fileNum');
                $dateVisit = $request->get('dateVisit');


                $file = $em->getRepository('AppBundle:File')->findOneBy(['number'=>$fileNumber]);
                if($file == null){
                    $file = new File();
                    $file->setNumber($fileNumber);
                    $file->setDescription('');
                }

                /** @var Visitor $visitor */
                $visitor = $form->getData();
                $dateVisit = new \DateTime(date($dateVisit));
                $visitor->setDateVisit($dateVisit);
                $visitor->setTypeDocId($documentId);
                $visitor->setTypeVisitorId($typeVisitorId);
                $em->persist($file);
                $em->persist($visitor);
                $em->flush();
                return $this->redirectToRoute('main_page');
            }


            $documents = $em->getRepository('AppBundle:Document')->findAll();
            $typeVisitors = $em->getRepository('AppBundle:TypeVisitor')->findAll();
            
            $params = [
                'form'=>$form->createView(),
                'visitor' => $visitor,
                'documents' => $documents,
                'typeVisitors' => $typeVisitors
            ];
            
            return $this->render(':default:edit_page.html.twig',$params);
        }else{
            return $this->notFoundAction($request);
        }
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function  notFoundAction(Request $request){
        return $this->render('default/404.html.twig',[]);
    }
    
    public function  ajaxSearchAction(Request $request){
        if ($request->isXmlHttpRequest()) {
           $phrase =  $request->get('string');
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');
            
            $content = $em->getRepository('AppBundle:Design2Visitor')->searchBySName($phrase);
            if(empty($content)){
                $result = 0;
            }else{
                $result = 1;
            }
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(
                json_encode(
                    [
                        'content' => $content,
                        'result'  => $result
                    ]
                )
            );
            return $response;
        }else{
            throw new BadRequestHttpException('XHR request expected');
        }
    }
}
