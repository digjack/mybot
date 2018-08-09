<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/8/10
 * Time: 0:42
 */

namespace App\Http\Service;

use App\GroupUser;
use App\Group;
use App\Plan;

class PlanService {

    //发送计划
    public function sendPlan($planId){
        $plan = Plan::findOrFail($planId);
        if($plan->status != Plan::STATUS_INIT){
            abort('400', 'status not init');
        }
        $plan->status = Plan::STATUS_SENDING;
        $plan->save();
        $params = json_decode($plan->params, true);
        $contacts = $this->listFriends($params);
        $msgService = new MsgService();
        $params = ['type' => MsgService::MSG_TYPE_PLAN, 'relate_id' => $planId];
        foreach ($contacts as $contact){
            if($msgService->send($contact['UserName'], $plan->content, $params)){
                $plan->success_count ++;
            }
        }
        $plan->status = Plan::STATUS_SUCCESS;
        $plan->save();
        return $plan;
    }

    public function countNum($params){
        $contacts = $this->listFriends($params);
        return count($contacts);
    }

    public function listFriends($params){
        $friends = BotService::getFriends();
        $result = [];
        foreach ($friends as $friend){
            if($params['sex'] == 'male' && $friend['Sex'] != BotService::SEX_MALE){
                continue;
            }
            if($params['sex'] == 'female' && $friend['Sex'] != BotService::SEX_FEMALE){
                continue;
            }
            if(! empty($params['province']) && ! in_array($friend['Province'], $params['province'])){
                continue;
            }
            if(! empty($params['local_label']) && ! $this->matchLabel($friend, $params['local_label'])){
                continue;
            }
            $result []= $friend;
        }
        return $result;
    }

    public function matchLabel($friend, $labelId){
        $userId = UserService::getUserId();
        $remarkName = $friend['RemarkName'];
        $nickName = $friend['NickName'];
        $label = Group::where(['user_id' => $userId, 'id' =>$labelId])->first();
        if(empty($label)){
            return false;
        }

        if($label->type == Group::TYPE_BY_REMARK && ! empty($remarkName)){
            $user = GroupUser::where(['remark_name' =>  $remarkName, 'group_id' => $labelId, 'is_delete' => 0])->first();
            if(! empty($user)){
                return true;
            }
        }

        if($label->type == Group::TYPE_BY_NICK && ! empty($nickName)){
            try{
                $user = GroupUser::where(['nick_name' =>  $nickName, 'group_id' => $labelId, 'is_delete' => 0])->first();
            }catch (\Exception $e){
                return false;
            }
            if(! empty($user)){
                return true;
            }
        }
        return false;
    }
}