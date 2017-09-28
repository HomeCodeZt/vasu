<?php

namespace AppBundle\Controller;

use AppBundle\DependencyInjection\UserManager\UserKeeper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class AuthenticationController extends Controller
{
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
