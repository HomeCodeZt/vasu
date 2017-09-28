<?php

namespace AppBundle\Controller;

use AppBundle\Entity\File;
use AppBundle\Entity\Visitor;
use AppBundle\Form\VisitorType;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;



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
        }else{
            return $this->redirectToRoute('search');
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
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
    
}
