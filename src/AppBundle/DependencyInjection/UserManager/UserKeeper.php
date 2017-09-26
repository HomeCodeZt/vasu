<?php

namespace AppBundle\DependencyInjection\UserManager;

use AppBundle\Entity\Users;
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
    /** @var  Users */
    private $user;

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
                $this->user = $user;
                $this->session->set(self::LOGIN,$user->getId());
                return true;
            }else{
                return false;
            }

        }else{
            return false;
        }

    }

    public function init(){
        $userID =  $this->session->get(self::LOGIN);
        if($userID){
            $this->user = $this->entityManager->getRepository('AppBundle:Users')->find($userID);
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

    /**
     * @return Users
     */
    public function getCurrentUser(){
        return $this->user;
    }
    
    public function logout(){
        $this->isLogin = false;
        $this->session->set(self::LOGIN,false);
    }

}