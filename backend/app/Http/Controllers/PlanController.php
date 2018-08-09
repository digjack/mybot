<?php

namespace App\Http\Controllers;

use App\Http\Service\UserService;
use Illuminate\Http\Request;

use App\Plan;
use App\Http\Service\PlanService;
use App\Http\Service\BotService;

class PlanController extends Controller
{
    //

    public function create(Request $request){
//        $userService = new UserService();
//        $userService->initUser(1);
        $planService = new PlanService();
        $sendTime = $request->input('send_time');
        if(empty($sendTime)){
            $sendTime = time();
        }

        $plan = new Plan();
        $plan->name = $request->input('name');
        $plan->user_id = UserService::getUserId();
        $plan->type = Plan::TYPE_FILTER;
        $plan->params = json_encode($request->input('params'));
        $plan->count = $planService->countNum($request->input('params'));
        $plan->success_count = 0;
        $plan->content = $request->input('content');
        $plan->send_time = date('Y-m-d H:i:s', $sendTime);
        $plan->send_now = 1;
        $plan->status = Plan::STATUS_INIT;
        $plan->is_delete = 0;
        $plan->save();
        return response()->json(['status' => true]);
    }

    //群发消息
    public function confirm(Request $request){
        $planId = $request->input('plan_id');
        $planService = new PlanService();
        $plan = $planService->sendPlan($planId);
        return response()->json(['status' => true, 'count' => $plan->count, 'success_count' => $plan->success_count]);
    }

    public function cancel(Request $request, $id){
        $userId = UserService::getUserId();
        $plan = Plan::where(['user_id' => $userId, 'id' => $id])->first();
        if(empty($plan)){
            abort(400, 'plan not exist');
        }
        if($plan->status != Plan::STATUS_INIT){
            abort(400, 'plan status not support cancel');
        }
        $plan->status = Plan::STATUS_CANCEL;
        $plan->save();
        return response()->json(['status' => true]);
    }

    public function countNum(Request $request){
        $params = $request->input('params');
        $planService = new PlanService();
        $num = $planService->countNum($params);
        return response()->json(['num' => $num]);
    }

    public function listPlan(Request $request){
        $userId = UserService::getUserId();
        $page = $request->input('page', 1);
        $size = $request->input('size', 30);
        $plans = Plan::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->skip(($page-1) * $size)
            ->take($size)
            ->get()
            ->toArray();
        foreach ($plans as &$plan){
            $plan['params'] = json_decode($plan['params'], true);
        }
        $total = Plan::count();
        return response()->json(['list' => $plans, 'total' => $total]);
    }

    //列出好友的省份
    public function listProvince(){
        $friends = BotService::getFriends();
        $provinces = [];
        foreach ($friends as $friend){
            if(! empty($friend['Province'])){
                $provinces []= $friend['Province'];
            }
        }
        return response()->json(array_values(array_unique($provinces)));
    }
}
