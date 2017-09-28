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


class AjaxController extends Controller
{
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
    
}
