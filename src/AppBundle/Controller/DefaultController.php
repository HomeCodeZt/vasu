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
            $this->createCsv($searchResult);
        }
        
        $documentId = $request->get('documentId');
        $typeVisitorId = $request->get('typeVisitorId');
        $fileNumber = $request->get('fileNum');
        $dateVisit = $request->get('dateVisit');

        $message = false;

        if($form->isSubmitted()){
            
            $file = $em->getRepository('AppBundle:File')->findOneBy(['number'=>$fileNumber]);
            if($file == null){
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
            if($visitor->getTName() == null){
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
        }
        
        $documents = $em->getRepository('AppBundle:Document')->findAll();
        $typeVisitors = $em->getRepository('AppBundle:TypeVisitor')->findAll();
        
        $templateParams = [
            'message'=>$message,
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
                if($file == null ){
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
                $visitor->setTypeDocId($documentId);

                $em->persist($visitor);
                $em->flush();
                return $this->redirectToRoute('main_page');
            }


            $documents = $em->getRepository('AppBundle:Document')->findAll();
            $typeVisitors = $em->getRepository('AppBundle:TypeVisitor')->findAll();
            /** @var File $currentFile */
            $currentFile = $em->getRepository('AppBundle:File')->find($visitor->getTypeFileId());
            
            $params = [
                'form'=>$form->createView(),
                'visitor' => $visitor,
                'documents' => $documents,
                'typeVisitors' => $typeVisitors,
                'currentFile' => $currentFile 
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

    /**
     * @param Request $request
     * @return Response
     */
    public function  ajaxSearchAction(Request $request){
        if ($request->isXmlHttpRequest()) {
           $phrase =  $request->get('string');
           $field =  $request->get('field');
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');
            
            $content = $em->getRepository('AppBundle:Design2Visitor')->searchBySName($phrase,$field );
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


    public  function createCsv(array $result)
    {
        $file = 'export/report.csv';

        $file = new \SplFileObject($file, 'w+');
        $paramsTitle= ['Учасники судового процесу Вищого адміністративного суду України'];
        $paramsEmpty= ['---','----------','----------','----------','----------','----------','----------','----------'];
        $paramsHeader = [
            'Номер справи',
            'Імя',
            'Прізвище',
            'По батькові',
            'Учасник',
            'Документ',
            'Номер документа',
            'Дата',
        ];

        $file->fputcsv($this->convertorUtf8toWin1251($paramsTitle),";");
        $file->fputcsv($this->convertorUtf8toWin1251($paramsEmpty),";");
        $file->fputcsv($this->convertorUtf8toWin1251($paramsHeader),";");

        foreach ($result as $row) {
            /** @var Visitor $visitor */
            $visitor = $row['visitor'];
            $fileNumber = $row['fileNumber'];
            $typeName = $row['typeName'];
            $docType = $row['docType'];

            $params = [
                $fileNumber,
                $visitor->getFName(),
                $visitor->getSName(),
                $visitor->getTName(),
                $typeName,
                $docType,
                $visitor->getDocNum(),
                $visitor->getDateVisit()->format('Y-m-d H:i:s'),
            ];
            $file->fputcsv($this->convertorUtf8toWin1251($params),";");
        }
    }
    
    public function exportAction(Request $request){
        $file = 'export/report.csv';
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "filename=report.csv");
        $response->setContent(file_get_contents($file));
        return $response;

    }

    /**
     * @param array $params
     * @return array
     */
    private function convertorUtf8toWin1251 (array $params){

        foreach ($params as $key => $string){
           $params[$key] =  mb_convert_encoding($string,"windows-1251","utf-8");
        }
        return $params;
    }
}
