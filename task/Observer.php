<?php

namespace Hanson\MyVbot;

use Hanson\Vbot\Support\File;
use Redis;

class Observer
{
    public static function setQrCodeObserver($qrCodeUrl)
    {
//        $redis = new Redis();
//        $redis->pconnect('127.0.0.1', 6379);
//        $bot = $redis->get(VBOT_REDIS_INDEX);
//        if(empty($bot)){
//            $bot = [
//                'qr_url' => $qrCodeUrl,
//                'status' => 'wait'
//            ];
//        }else{
//            $bot = json_decode($bot, true);
//            $bot['qr_url'] = $qrCodeUrl;
//            $bot['status'] = 'wait';
//        }
//        $redis->set(VBOT_REDIS_INDEX, json_encode($bot));
        vbot('console')->log('二维码链接：'.$qrCodeUrl, '自定义消息');
    }

    public static function setLoginSuccessObserver()
    {
        $redis = new Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $bot = $redis->get(VBOT_REDIS_INDEX);
        $bot = json_decode($bot, true);
        $bot['status'] = 'success';
        $redis->set(VBOT_REDIS_INDEX, json_encode($bot));
        vbot('console')->log('登录成功', '自定义消息');
    }

    public static function setReLoginSuccessObserver()
    {
        $redis = new Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $bot = $redis->get(VBOT_REDIS_INDEX);
        $bot = json_decode($bot, true);
        $bot['status'] = 'success';
        $redis->set(VBOT_REDIS_INDEX, json_encode($bot));
        vbot('console')->log('免扫码登录成功', '自定义消息');
    }

    public static function setExitObserver()
    {
        $redis = new Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $bot = $redis->get(VBOT_REDIS_INDEX);
        $bot = json_decode($bot, true);
        $bot['status'] = 'exit';
        $redis->set(VBOT_REDIS_INDEX, json_encode($bot));
        vbot('console')->log('退出程序', '自定义消息');
    }

    public static function setFetchContactObserver(array $contacts)
    {
        $redis = new Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $bot = $redis->get(VBOT_REDIS_INDEX);
        $bot = json_decode($bot, true);
        $bot['contacts'] = $contacts;
        $myInfo = vbot('myself');
        $bot['myself'] = $myInfo;
        $config = vbot('config');
        $bot['swoole_port'] =$config['swoole']['port'];
        $redis->set(VBOT_REDIS_INDEX, json_encode($bot));
        vbot('console')->log('获取好友成功', '自定义消息');
//        File::saveTo(__DIR__.'/group.json', $contacts['groups']);
    }

    public static function setBeforeMessageObserver()
    {
        vbot('console')->log('准备接收消息', '自定义消息');
    }

    public static function setNeedActivateObserver()
    {
        vbot('console')->log('准备挂了，但应该能抢救一会', '自定义消息');
    }
}
