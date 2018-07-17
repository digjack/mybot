<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/7/7
 * Time: 1:59
 */

namespace App\Http\Service;

use App\Task;
use App\GroupUser;
use App\Group;
use Log;
use App\Msg;

class TaskService {
    public static function countNextDate($dateCycle){
        $cron = \Cron\CronExpression::factory($dateCycle);
        $nextDate = $cron->getNextRunDate()->format('Y-m-d H:i:s');
        return $nextDate;
    }

    //处理任务发送消息
    public function handleTask(){
        $needHandleTasks = Task::where('next_date', '<=', date('Y-m-d H:i:s'))
            ->whereRaw('success_count < max_count')
            ->where('status', 2)
            ->where('is_delete', 0)
            ->get();
        $userService = new UserService();
        foreach ($needHandleTasks as $task){
            $userService->initUser($task->user_id);
            $this->taskWorker($task);
            Task::find($task->id)->increment('success_count');
            $task->next_date = TaskService::countNextDate($task->date_cycle);
            $task->save();
        }
    }

    public function taskWorker($task){
        $userNames = $this->getUserIds($task->receiver_type, $task->receiver);
        foreach ($userNames as $userName){
            $msgService = new MsgService();
            $now = date('Y-m-d H:i:s');
            Log::info("user_name: {$userName}, content: { $task->content}, now: {$now} \n");
            $params = ['type' => Msg::TYPE_TASK, 'relate_id' => $task->id];
            $res = $msgService->send($userName, $task->content, $params);
            Log::info(var_export($res, true));
        }
    }

    public function getUserIds($type, $id){
        $botService = new BotService();
        $userNames = [];
        if($type == 'group'){
            $label = Group::find($id);
            if(empty($label)){
                return false;
            }
            if($label->type == Group::TYPE_BY_NICK){
                $nicks = GroupUser::where('group_id', $id)
                    ->where('is_delete', 0)
                    ->pluck('nick_name');
                foreach ($nicks as $nick){
                    $userName  = $botService->getUserNameByNick($nick);
                    if(empty($userName)){
                        continue;
                    }
                    $userNames []= $userName;
                }
                return $userNames;
            }
            if($label->type == Group::TYPE_BY_REMARK){
                $remarkNames  = GroupUser::where('group_id', $id)
                    ->where('is_delete', 0)
                    ->pluck('remark_name');
                foreach ($remarkNames as $remarkName){
                    $userName  = $botService->getUserNameByRemark($remarkName);
                    if(empty($userName)){
                        continue;
                    }
                    $userNames []= $userName;
                }
                return $userNames;
            }
            if($label->type == Group::TYPE_BY_WE_GROUP){
                $nicks = GroupUser::where('group_id', $id)
                    ->where('is_delete', 0)
                    ->pluck('nick_name');
                foreach ($nicks as $nick){
                    $userName  = $botService->getUserNameByNick($nick, 'groups');
                    if(empty($userName)){
                        continue;
                    }
                    $userNames []= $userName;
                }
                return $userNames;
            }

        }else{
            $nicks = explode(';',$id);
            foreach ($nicks as $nick){
                $userName  = $botService->getUserNameByNick($nick);
                if(empty($userName)){
                    continue;
                }
                $userNames []= $userName;
            }
            return $userNames;
        }
    }
}