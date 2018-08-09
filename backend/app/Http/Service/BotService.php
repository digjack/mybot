<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/7/3
 * Time: 0:34
 */

namespace App\Http\Service;

use Session;
use Illuminate\Support\Facades\Redis;

class BotService
{

    private $bot;
    private $contacts;

    const SEX_MALE = 1;
    const SEX_FEMALE = 2;

    public static function getBot()
    {
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        unset($vbot['contacts']);
        return $vbot;
    }

    public static function myWeInfo()
    {
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        return $vbot['myself'];
    }

    public static function getFriends($keyword = '')
    {
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        $friends =  $vbot['contacts']['friends']??[];
        if(! empty($keyword)){
            $res = [];
            foreach ($friends as $username =>  $friend){
                if(! empty($friend['NickName']) && mb_strpos($friend['NickName'], $keyword) !== false){
                    $res[$username] = $friend;
                }
                if(! empty($friend['RemarkName']) && mb_strpos($friend['RemarkName'], $keyword) !== false){
                    $res[$username] = $friend;
                }
            }
            $friends = $res;
        }
        foreach ($friends as $index => $friend){
            $friends[$index]['RemarkName'] = trim( $friends[$index]['RemarkName']);
            $friends[$index]['NickName'] = trim( $friends[$index]['NickName']);
        }
        return $friends;
    }

    public static function getGroups($keyword = '')
    {
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        if(! empty($vbot['contacts']['groups'])){
            foreach ($vbot['contacts']['groups'] as $key => $group){
                unset($vbot['contacts']['groups'][$key]['MemberList']);
            }
        }
        return $vbot['contacts']['groups']??[];
    }

    public static function getGroupMemebers($weUserName, $keyword = ''){
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        $members = $vbot['contacts']['groups'][$weUserName]['MemberList']??[];
        return $members;
    }

    public function getUserNameByNick($nickname, $type = 'friends'){
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        $members = $vbot['contacts'][$type]??[];
        foreach ($members as  $userId => $member){
            if(trim($member['NickName']) == $nickname){
                return $userId;
            }
        }
        return false;
    }

    public function getUserNameByRemark($remarkName){
        if(empty($remarkName)){
            return false;
        }
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        $friends = $vbot['contacts']['friends']??[];
        foreach ($friends as  $userId => $friend){
            if($friend['RemarkName'] == $remarkName){
                return $userId;
            }
        }
        return false;
    }

    public function exitBot(){
        $bot = self::getBot();
        if(empty($bot['swoole_port'])){
            return true;
        }
        $params = [
            'action' => 'send',
            'params' => 'exit'
        ];
        $port = $bot['swoole_port'];
        try{
            $res = MsgService::curl('http://127.0.0.1:'.$port, json_encode($params), true);
        }catch (\Exception $e){
            abort(400, $e->getMessage());
        }
        return $res;
    }

    public static function getNickByUserName($userId){
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        if(! empty($vbot['contacts']['friends'][$userId]['NickName'])){
            return $vbot['contacts']['friends'][$userId]['NickName'];
        }
        if(! empty($vbot['contacts']['groups'][$userId]['NickName'])){
            return $vbot['contacts']['groups'][$userId]['NickName'];
        }
        return 'no_nick';
    }

    public static function getRemarkNameByUserName($userId){
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_' . $username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        if(! empty($vbot['contacts']['friends'][$userId]['RemarkName'])){
            return $vbot['contacts']['friends'][$userId]['RemarkName'];
        }
        if(! empty($vbot['contacts']['groups'][$userId]['RemarkName'])){
            return $vbot['contacts']['groups'][$userId]['RemarkName'];
        }
        return '';
    }
}