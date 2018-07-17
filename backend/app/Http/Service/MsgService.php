<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/7/3
 * Time: 0:31
 */
namespace App\Http\Service;

use App\Msg;

class MsgService {

    public function send($username, $content, $params = []){
        $msg = $this->logMsg($username, $content, $params);
        $bot = BotService::getBot();
        $content = str_replace(',','，', $content);
        $params = [
            'action' => 'send',
            'params' => [
                "type" => "text",
                'username' => $username,
                'content' => $content
            ]
        ];

        $port = $bot['swoole_port']??8866;
        try{
            $res = self::curl('http://127.0.0.1:'.$port, json_encode($params), true);
            $msg->status = Msg::STATUS_SUCCESS;
        }catch (\Exception $e){
            $msg->status = Msg::STATUS_FAIL;
            $msg->msg = $e->getMessage();
            abort(400, $e->getMessage());
        }
        $msg->save();
        return $res;
    }

    public function logMsg($username, $content, $params){
        $msg = new Msg();
        $user = UserService::getUser();
        $weInfo = BotService::myWeInfo();
        $msg->user_id = $user['id'];
        $msg->from_nick = $weInfo['nickname'];
        $msg->to_nick = BotService::getNickByUserName($username);
        $msg->to_username = $username;
        $msg->to_remarkname = BotService::getRemarkNameByUserName($username);
        $msg->type = $params['type']??Msg::TYPE_SINGLE;
        $msg->relate_id = $params['relate_id']??0;
        $msg->status = Msg::STATUS_WAIT;
        $msg->send_time = date('Y-m-d H:i:s');
        $msg->content = $content;
        $msg->save();
        return $msg;
    }
    public static function curl($url, $params = false, $ispost = 0, $https = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        curl_close($ch);
        return $response;
    }
}