<?php
//https://segmentfault.com/a/1190000002946586  优化参考

require __DIR__.'/vendor/autoload.php';

use Hanson\Vbot\Foundation\Vbot as Bot;
use Vbot\Blacklist\Blacklist;
use Vbot\GuessNumber\GuessNumber;
use Hanson\MyVbot\MessageHandler;
use Hanson\MyVbot\Observer;

$maxProcessNum = 10;
$workers = [];

$redis = new Redis();
$redis->pconnect('127.0.0.1', 6379);
$key = "vbot_wait_init";


swoole_process::signal(SIGCHLD, function($sig) use ($workers) {
    // 回收子进程
    while($ret =  swoole_process::wait(false)){
        $pid = $ret['pid'];
        $indexs = array_combine(array_column($workers, 'pid'), array_keys($workers));
        if(! empty($indexs[$pid])){
            $vbotIndex = $indexs[$pid];
            exitVbot($vbotIndex);
        }
        echo "用户{$vbotIndex}退出完成\n";
        return true;
    }
});


//清理cookie进程
$exitProcess = new Swoole\Process('exitProcess', false);
$exitProcess->name('exitProcess');
$exitProcess->start();


while (true) {
    $vbotIndex = $redis->rpop($key);
    if(count($workers) >= $maxProcessNum){
        echo "进程数超限,最大限制{$maxProcessNum}个\n";
        continue;
    }
    var_dump($vbotIndex);
    if (empty($vbotIndex)) {
        sleep(3);
        continue;
    }
    exec("pkill {$vbotIndex}");
    if(! empty($workers[$vbotIndex])){
        swoole_process::kill($workers[$vbotIndex]['pid']);
        echo "进程{$workers[$vbotIndex]['pid']}退出完成\n";
        exitVbot($vbotIndex);
    }
    $process = new Swoole\Process('process', false);
    $process->name($vbotIndex);
    $pid = $process->start();
    $workers[$vbotIndex]['process'] = $process;
    $workers[$vbotIndex]['pid'] = $pid;
    $process->write($vbotIndex);
}

function exitVbot($vbotIndex){
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $bot = $redis->get($vbotIndex);
    $bot = json_decode($bot, true);
    $bot['status'] = 'exit';
    $redis->set($vbotIndex, json_encode($bot));
    return true;
}

function exitProcess(){
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = 'vbot_wait_exit';
    while (true) {
        $vbotIndex = $redis->rpop($key);
        if(empty($vbotIndex)){
            sleep(3);
            continue;
        }
        echo "开始清理授权: {$vbotIndex}\n";
        $cookieFile = "/service/vbot/task/tmp/cookies/{$vbotIndex}";
        $dir = "/service/vbot/task/tmp/{$vbotIndex}";
        deleteDir($dir);
        @unlink($cookieFile);
        $redis->del($vbotIndex);
        echo "清理授权完成: {$vbotIndex}\n";
    }
}

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        return true;
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}



function process($worker){
    $vbotIndex = $worker->read();
    echo PHP_EOL. "From Master: {$vbotIndex}; current_pid: {$worker->pid}\n";
    define('VBOT_REDIS_INDEX', $vbotIndex);
    $config = require_once __DIR__.'/config.php';
    $config['session'] = $vbotIndex;
    $config['swoole']['port'] = rand(20000, 30000);
    $config['swoole']['pid'] = $worker->pid;
    $path = $config['path'].$vbotIndex;
    if (!file_exists($path) && !is_dir($path)) {
        mkdir($path);
    }
    $config['path'] = $path.'/';
    $robot = new Bot($config);
    $robot->messageHandler->setHandler([MessageHandler::class, 'messageHandler']);
//    $robot->messageExtension->load([]);
    $robot->observer->setQrCodeObserver([Observer::class, 'setQrCodeObserver']);
    $robot->observer->setLoginSuccessObserver([Observer::class, 'setLoginSuccessObserver']);
    $robot->observer->setReLoginSuccessObserver([Observer::class, 'setReLoginSuccessObserver']);
    $robot->observer->setExitObserver([Observer::class, 'setExitObserver']);
    $robot->observer->setFetchContactObserver([Observer::class, 'setFetchContactObserver']);
    $robot->observer->setBeforeMessageObserver([Observer::class, 'setBeforeMessageObserver']);
    $robot->observer->setNeedActivateObserver([Observer::class, 'setNeedActivateObserver']);
    $robot->server->serve();
    //向主进程管道中写入数据
//    $worker->exit(0);
}
