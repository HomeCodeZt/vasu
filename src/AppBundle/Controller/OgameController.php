<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class OgameController extends Controller
{
    const PASS = "7127056AAA";
    const LOGIN = "alexandr_r@i.ua";
    const UNI = "https://lobby-api.ogame.gameforge.com/users/me/loginLink?id=100608&server[language]=ru&server[number]=148";
    const URL_LOGIN = "https://lobby-api.ogame.gameforge.com/users";
    const FLEET_ONE = "https://s145-ru.ogame.gameforge.com/game/index.php?page=fleet1&cp=33625663";
    const FLEET_TWO = "https://s145-ru.ogame.gameforge.com/game/index.php?page=fleet2";
    const FLEET_THREE = "https://s145-ru.ogame.gameforge.com/game/index.php?page=fleet3";
    const SUBMIT = 'https://s145-ru.ogame.gameforge.com/game/index.php?page=movement';
    const LOBBY_URL = 'https://lobby.ogame.gameforge.com/?language=ru';

    const OVERVIEW = 'https://s148-ru.ogame.gameforge.com/game/index.php?page=overview';

    const REFERRER_LOGIN = 'Referer: https://ru.ogame.gameforge.com/';
    const REFERRER_LOGIN_UNI = 'Referer: https://lobby.ogame.gameforge.com/?language=ru';

    const ME = 'https://lobby-api.ogame.gameforge.com/users/me';
    const RU = 'https://lobby-api.ogame.gameforge.com/l10n/ru';
    const SERVERS = 'https://lobby-api.ogame.gameforge.com/servers';
    const ACCOUNTS = 'https://lobby-api.ogame.gameforge.com/users/me/accounts';

    const FLEET = 'https://s148-ru.ogame.gameforge.com/game/index.php?page=fleet1';

    private function exucateCurl($url,$postFields, $post = true){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url); // set url to post to
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($post){
            curl_setopt($ch, CURLOPT_POST, 1); // set POST method
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields); // add POST fields
        }
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/66.0.3359.181 Chrome/66.0.3359.181 Safari/537.36');
        curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__)."/my_cookies.txt"); //Создание файла куков
        curl_setopt($ch, CURLOPT_COOKIEJAR,  dirname(__FILE__)."/my_cookies.txt");
        $result = curl_exec($ch); // run the whole process
        curl_close($ch);
        return $result;
    }

    private function curlXHRRequest($url,$referrer,$postFields,$post = true){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url); // set url to post to
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Host: lobby-api.ogame.gameforge.com",
            "Origin: https://lobby.ogame.gameforge.com",
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/66.0.3359.181 Chrome/66.0.3359.181 Safari/537.36",
            "Accept: application/json",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Connection: keep-alive",
            "X-Requested-With: XMLHttpRequest",
            $referrer
        ));
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($post){
            curl_setopt($ch, CURLOPT_POST, 1); // set POST method
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields); // add POST fields
        }

        curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__)."/my_cookies.txt"); //Создание файла куков
        curl_setopt($ch, CURLOPT_COOKIEJAR,  dirname(__FILE__)."/my_cookies.txt");
        $result = curl_exec($ch); // run the whole process
        curl_close($ch);
        return $result;
    }

    public function StartAppAction(Request $request){
        $result = $this->checkStatusFleet();
        return  new Response("$result");
    }

    private function login(){
        $this->users();
        $this->getLobby();
        $this->ru();
        $this->me();
        $this->servers();
        $this->getAccounts();
        $result = $this->getUni();
        $result = $this->getOwerWishToken($result);
        return $result;
    }

    private function isLogin($result){
        $result = preg_match('~playerName~',$result);
        return $result ? true : false;
    }

    private function users(){
        $params = [
            'kid'=>'',
            'credentials[email]'=>self::LOGIN,
            'credentials[password]'=>self::PASS,
            'language'=>'ru'
        ];
       return $this->curlXHRRequest(self::URL_LOGIN,self::REFERRER_LOGIN,$params);
    }

    private function fleetOne(){
       return $this->exucateCurl(self::FLEET_ONE,"");
    }

    private function fleetTwo(){
       $params =[
           'galaxy'=>1,
           'system'=>103,
           'possition' => 6,
           'mission' => 4,
           'speed' => 100,
           'am204' => 100,
           'am202' => 500
       ];
        return $this->exucateCurl(self::FLEET_TWO,$params);
    }

    private function fleetThree(){
        $params =[
            'type'=> 1,
            'mission' => 3,
            'union' =>0,
            'galaxy'=>1,
            'system'=>103,
            'possition' => 6,
            'speed' => 100,
            'am202' => 100,
            'am203' => 100,
            'am204' => 500,
            'am205' => 100,
            'am206' => 100,
        ];
        return $this->exucateCurl(self::FLEET_THREE,$params);
    }

    private function send($token){
        $params =[
            'token'=>$token,
            'galaxy'=>1,
            'system'=>103,
            'position' => 6,
            'type' =>1,
            'mission' => 4,
            'holdingOrExpTime' => 0,
            'speed' => 100,
            'acsValues' =>'-',
            'prioMetal'=>'1',
            'prioCrystal'=>'2',
            'prioDeuterium'=>'3',
            'am202' => 1,
            'am203' => 1628,
            'am204' => 18581,
            'am205' => 50,
            'am206' => 1039,
            'am207' => 700,
            'am208' => 1,
            'am209' => 1304,
            'am210' => 195,
            'am211' => 410,
            'am213' => 168,
            'am214' => 3,
            'am215' => 100,
            'metal' => 1023469,
            'crystal' => 5186837,
            'deuterium' => 13085658


        ];
        return $this->exucateCurl(self::SUBMIT,$params);
    }

    private function getAccounts(){
        return $this->curlXHRRequest(self::ACCOUNTS ,self::REFERRER_LOGIN_UNI,"",false);
    }

    private function getOwerWishToken($result){
        $urlData = json_decode($result, true);
        return $this->exucateCurl($urlData['url'],[]);
    }

    private function getLobby(){
        return $this->exucateCurl(self::LOBBY_URL,[],false);
    }

    private function ru(){
        return $this->curlXHRRequest(self::RU,self::REFERRER_LOGIN_UNI,[],false);
    }

    private function me(){
        return $this->curlXHRRequest(self::ME,self::REFERRER_LOGIN_UNI,[],false);
    }

    private  function servers(){
        return $this->curlXHRRequest(self::SERVERS,self::REFERRER_LOGIN_UNI,[],false);
    }

    private function getUni(){
        return $this->curlXHRRequest(self::UNI,self::REFERRER_LOGIN_UNI,[],false);
    }

    private function getFleet(){
        return $this->exucateCurl(self::FLEET,false);
    }

    private function checkStatusFleet(){
        $result = $this->getFleet();
        if($this->isLogin($result)){
            if(!preg_match('~Нет передвижения флотов~',$result)){
                $this->notification;
            }
        }else{
            $result = $this->login();
            if(!preg_match('~Нет передвижения флотов~',$result)){
                $this->notification;
            }
        }

    }

}


