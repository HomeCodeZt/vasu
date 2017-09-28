<?php

namespace AppBundle\Controller;

use AppBundle\DependencyInjection\UserManager\UserKeeper;
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
        $userKeep = $this->get('user_keep');
        if (!$userKeep->isLogged()) {
            return $this->redirectToRoute('login');
        }elseif ($userKeep->getCurrentUser()->isRoot()){
            return $this->redirectToRoute('admin_form');
        }

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->createForm(VisitorType::class, new Visitor());

        $form->handleRequest($request);

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

            $message = 'Збережено!';
        }

        $documents = $em->getRepository('AppBundle:Document')->findAll();
        $typeVisitors = $em->getRepository('AppBundle:TypeVisitor')->findAll();

        $templateParams = [
            'message' => $message,
            'form' => $form->createView(),
            'documents' => $documents,
            'typeVisitors' => $typeVisitors,
            'user' => $userKeep->getCurrentUser(),
        ];

        return $this->render('default/index.html.twig', $templateParams);
    }

    public function editAction(Request $request, $id)
    {
        $userKeep = $this->get('user_keep');
        if (!$userKeep->isLogged()) {
            return $this->redirectToRoute('login');
        }

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $visitor = $em->getRepository('AppBundle:Visitor')->find($id);
        if ($visitor) {
            $form = $this->createForm(VisitorType::class, $visitor);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {

                $documentId = $request->get('documentId');
                $typeVisitorId = $request->get('typeVisitorId');
                $fileNumber = $request->get('fileNum');
                $dateVisit = $request->get('dateVisit');

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
                $visitor->setTypeDocId($documentId);

                if($userKeep->getCurrentUser()->isRoot()){
                    $em->persist($visitor);
                    $em->flush();
                }

                return $this->redirectToRoute('main_page');
            }


            $documents = $em->getRepository('AppBundle:Document')->findAll();
            $typeVisitors = $em->getRepository('AppBundle:TypeVisitor')->findAll();
            /** @var File $currentFile */
            $currentFile = $em->getRepository('AppBundle:File')->find($visitor->getTypeFileId());

            $params = [
                'message' => false,
                'form' => $form->createView(),
                'visitor' => $visitor,
                'documents' => $documents,
                'typeVisitors' => $typeVisitors,
                'currentFile' => $currentFile,
                'user' => $userKeep->getCurrentUser(),
            ];

            return $this->render(':default:edit_page.html.twig', $params);
        } else {
            return $this->notFoundAction($request);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notFoundAction(Request $request)
    {
        return $this->render('default/404.html.twig', []);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function ajaxSearchAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $phrase = $request->get('string');
            $field = $request->get('field');
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');

            $content = $em->getRepository('AppBundle:Design2Visitor')->searchBySName($phrase, $field);
            if (empty($content)) {
                $result = 0;
            } else {
                $result = 1;
            }
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(
                json_encode(
                    [
                        'content' => $content,
                        'result' => $result,
                    ]
                )
            );

            return $response;
        } else {
            throw new BadRequestHttpException('XHR request expected');
        }
    }

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

    public function loginAction(Request $request)
    {
        $login = $request->get('login');
        $pass = $request->get('pass');
        /** @var UserKeeper $userKeepService */
        $userKeepService = $this->get('user_keep');
        $result = $userKeepService->initLogin($login, $pass);
        if ($result) {
            if ($userKeepService->getCurrentUser()->isRoot()) {
                return $this->redirectToRoute('admin_form');
            } else {
                return $this->redirectToRoute('search');
            }

        } else {
            return $this->render('default/login.html.twig', []);
        }
    }

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
            $searchResult = $this->searchAction($request, true);
            if($searchResult){
                $this->createCsv($searchResult);
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

    public function searchAction(Request $request, $admin = null)
    {
        $userKeep = $this->get('user_keep');
        if (!$userKeep->isLogged()) {
            return $this->redirectToRoute('login');
        }

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

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        if ($searchDocNum || $searchSName || $searchDateStart || $searchDateEnd) {
            $result = $em->getRepository('AppBundle:Design2Visitor')->search(
                $searchDocNum,
                $searchSName,
                $searchDateStart,
                $searchDateEnd
            );
        } else {
            $result = null;
        }

        if ($admin) {
            return $result;
        }

        if ($userKeep->getCurrentUser()->isRoot()) {
            if($result){
                $this->createCsv($result);
            }
        }

        $templateParams = [
            'message' => '',
            'searchResult' => $result,
            'user' => $userKeep->getCurrentUser(),
        ];

        return $this->render('default/search_page.html.twig', $templateParams);
    }


    /**
     * @param array $result
     */
    public function createCsv(array $result)
    {
        $file = 'export/report.csv';
        $file = new \SplFileObject($file, 'w+');
        $paramsTitle = ['Учасники судового процесу Вищого адміністративного суду України'];
        $paramsEmpty = [
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
            '-----------------------',
        ];
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

        $file->fputcsv($this->convertorUtf8toWin1251($paramsTitle), ";");
        $file->fputcsv($this->convertorUtf8toWin1251($paramsEmpty), ";");
        $file->fputcsv($this->convertorUtf8toWin1251($paramsHeader), ";");

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
            $file->fputcsv($this->convertorUtf8toWin1251($params), ";");
        }
    }

    /**
     * @param array $params
     * @return array
     */
    private function convertorUtf8toWin1251(array $params)
    {
        foreach ($params as $key => $string) {
            $params[$key] = mb_convert_encoding($string, "windows-1251", "utf-8");
        }
        return $params;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction(Request $request)
    {
        $userKeep = $this->get('user_keep');
        if ($userKeep->isLogged()) {
            $userKeep->logout();
        }
        return $this->redirectToRoute('login');
    }
}
