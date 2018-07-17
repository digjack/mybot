<?php

namespace App\Http\Controllers;

use App\Http\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Session;
use App\Http\Service\MsgService;
use App\Http\Service\BotService;
class InfoController extends Controller
{
    private $vbot;
    private $contacts;

    public function init(){
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_'.$username;
        $vbot = json_decode(Redis::get($vbotIndex), true);
        $this->contacts = $vbot['contacts']??[];
        $vbot['contact_num'] = count($this->contacts);
        unset($vbot['contacts']);
        $this->vbot = $vbot;
        return true;
    }

    public function info(Request $request){
        if(! $this->init($request)){
            return false;
        }
        $times = 2;
        while($times){
            $times --;
            if(empty($this->vbot['status']) || $this->vbot['status'] != 'success'){
                break;
            }
            if( $this->vbot['status'] == 'success' && empty($this->vbot['myself'])){
                sleep(2);
            }
        }

        return response()->json($this->vbot);
    }

    public function qr(Request $request){
        $type = $request->input('type', 'normal');

        $botService = new BotService();
        $botService->exitBot();

        $user = UserService::getUser();
        $username = $user['name'];
        $queueIndex = 'vbot_wait_init';
        $vbotIndex = 'vbot_'.$username;
        if($type == 'force'){
            $cookieFile = "/service/vbot/task/tmp/cookies/{$vbotIndex}";
            @unlink($cookieFile);
        }
        Redis::del($vbotIndex);
        Redis::lpush($queueIndex, $vbotIndex);
        $times = 10;
        while ($times){
            $times--;
            $vbot = Redis::get($vbotIndex);
            $vbot = json_decode($vbot, true);
            if(! empty($vbot['status']) && $vbot['status'] == 'success'){
                return response()->json(['status' => 'success', 'qr' => null]);
            }
            if(empty($vbot['qr'])){
                sleep(1.5);
                continue;
            }
            return response()->json(['status' => 'success', 'qr' => $vbot['qr']]);
        }
        return response()->json(['status' => 'failed' , 'qr' => '']);
    }

    public function status(Request $request){
        $this->init();
        $status = (! empty($this->vbot['status']) && $this->vbot['status'] == 'success')?true:false;
        return response()->json(['status' => $status]);
    }

    public function send(Request $request){
        $username = $request->input('username', '');
        $content = $request->input('content', '');
        if(empty($content) ||empty($username)){
            abort(500, 'params error');
        }
        $msgService = new MsgService();
        $res = $msgService->send($username, $content, []);
        return response($res);
    }


    public function contacts(Request $request){
        $page = $request->input('page', 1);
        $size = $request->input('size', 20);
        $keyword = $request->input('keyword', '');
        $friends = BotService::getFriends($keyword);
        $total = count($friends);
        $friendSet = array_chunk($friends, $size);
        $friends = $friendSet[$page-1]??[];
        $res = [];
        foreach ($friends as $friend){
            $res []= [
                'UserName' => $friend['UserName'],
                'NickName' => $friend['NickName'],
                'RemarkName' => $friend['RemarkName'],
                'HeadImgUrl' => $friend['HeadImgUrl'],
                'ContactFlag' => $friend['ContactFlag'],
                'Signature' => $friend['Signature'],
                'Sex' => $friend['Sex'],
                'AttrStatus' => $friend['AttrStatus'],
                'SnsFlag' => $friend['SnsFlag'],
                'Province' => $friend['Province'],
                'City' => $friend['City']

            ];
        }
        return response()->json(['friends' => $res, 'total' => $total]);
    }

    public function groups(Request $request){
        $groups = BotService::getGroups();
        $res = [];
        foreach ($groups as $group){
            $res []= [
                'UserName' => $group['UserName'],
                'NickName' => $group['NickName'],
                'HeadImgUrl' => $group['HeadImgUrl'],
                'ContactFlag' => $group['ContactFlag'],
                'Signature' => $group['Signature'],
                'Sex' => $group['Sex'],
                'AttrStatus' => $group['AttrStatus'],
                'SnsFlag' => $group['SnsFlag'],
                'Province' => $group['Province'],
                'City' => $group['City'],
                'MemberCount' => $group['MemberCount'],
                'ChatRoomId' => $group['ChatRoomId'],
                'IsOwner' => $group['IsOwner']
            ];
        }
        return response()->json(['groups' => $res]);
    }

    public function groupUser(Request $request){
        $username = $request->input('user_name');
        $members = BotService::getGroupMemebers($username);
        $res = [];
        foreach ($members as $member){
            $res []= [
                'UserName' => $member['UserName'],
                'NickName' => $member['NickName']
            ];
        }
        return response()->json(['members' => $res, 'count' => count($res)]);
    }

    public static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            return true;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function testMsg(Request $request){
        $now = date('Y-m-d H:i:s');
        $msg = "当前时间: {$now}, 如您收到本条短信，则程序运行正常！";
        $msgService = new MsgService();
        $res = $msgService->send('filehelper', $msg, []);
        return response(['status' => true, 'msg' => $msg]);

    }

    public function clearAuth(Request $request){
        $botService = new BotService();
        $botService->exitBot();
        $user = UserService::getUser();
        $username = $user['name'];
        $vbotIndex = 'vbot_'.$username;
        $queueIndex = 'vbot_wait_exit';
        Redis::lpush($queueIndex, $vbotIndex);
        $times = 3;
        $res = false;
        while ($times){
            $times -- ;
            $vbot = $botService::getBot();
            if(! $vbot){
                $res = true;
                break;
            }
            sleep(2);
            continue;
        }
        return response()->json(['status' => $res ]);
    }
}
