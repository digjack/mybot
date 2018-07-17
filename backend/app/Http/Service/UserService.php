<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/7/14
 * Time: 23:23
 */
namespace App\Http\Service;

use Session;


class UserService {

    public $users = [
        'banli' => ['id' => 1, 'password' => 123, 'name' => 'banli', 'avatar' => 'https://raw.githubusercontent.com/taylorchen709/markdown-images/master/vueadmin/user.png'],
        'selena' => ['id' => 2, 'password' => 123, 'name' => 'selena', 'avatar' => 'https://raw.githubusercontent.com/taylorchen709/markdown-images/master/vueadmin/user.png']
    ];

    public function initUser($userId){
        foreach ($this->users as $user){
            if($user['id'] == $userId){
                Session::put('user', collect($user));
            }
        }
        return true;
    }

    public static function getUser(){
        $user = Session::get('user');
        if(empty($user)){
            abort(400, 'auth failed');
        }
        return $user->toArray();
    }

    public static function getUserId(){
        $user = Session::get('user')->toArray();
        return $user['id'];
    }

    public function setUser(){

    }
}
