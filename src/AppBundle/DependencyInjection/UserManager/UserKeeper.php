<?php

namespace AppBundle\DependencyInjection\UserManager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class UserManager
 * @package AppBundle\DependencyInjection\UserManager
 */
class UserKeeper
{
    const LOGIN = 'auth';

    /** @var  EntityManager */
    private $entityManager;
    /** @var  Session */
    private $session;

    private $isLogin = false;

    public function  __construct(EntityManager $entityManager,Session $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->init();
    }

    public function initLogin($login ,$pass){

        if($login && $pass){
            $user = $this->entityManager->getRepository('AppBundle:Users')->findOneBy(['login'=>$login,'pass'=>$pass]);
            if($user){
                $this->session->set(self::LOGIN,true);
                return true;
            }else{
                return false;
            }

        }else{
            return false;
        }

    }

    public function init(){
       $result =  $this->session->get(self::LOGIN);
        if($result){
            $this->isLogin = true;
        }
    }

    /**
     * @return boolean
     */
    public function isLogged()
    {
        return $this->isLogin;
    }

}