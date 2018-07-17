<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\GroupUser;
use Session;
use App\Http\Service\LabelService;
use App\Http\Service\LabelUserService;
class LabelUserController extends Controller
{

    public function list(Request $request){
        $user = Session::get('user')->toArray();
        $labelId = $request->input('label_id');
        $users = GroupUser::where('group_id', $labelId)
            ->where('user_id', $user['id'])
            ->where('is_delete', '0')
            ->get()->toArray();
        $total = GroupUser::count();
        $res = ['list' => $users, 'total' => $total];
        return response()->json($res);
    }

    public function save(Request $request){
        $adminInfo = Session::get('user')->toArray();
        $labelId = $request->input('label_id', '');
        $members =  $request->input('member', '');
        $labelUserService = new LabelUserService();
        $addCount = 0;
        foreach ($members as $member){
            $nickName = $member['NickName'];
            $remarkName = $member['RemarkName']??'';
            if($labelUserService->isRepeat($labelId, $nickName, $remarkName)){
                continue;
            }
            $user = new GroupUser();
            $user->nick_name =$nickName;
            $user->remark_name = $remarkName;
            $user->group_id = $labelId;
            $user->user_id = $adminInfo['id'];
            $user->is_delete = 0;
            $user->save();
            $addCount++;
        }

        LabelService::syncLabelMemberCount($labelId);
        return response()->json(['status' => true, 'add_count' => $addCount]);
    }

    public function delete(Request $request, $id){
        $groupId = $request->input('label_id');
        $user = GroupUser::where('group_id', $groupId)
            ->where('id', $id)
            ->where('is_delete' , 0)
            ->first();
        if(empty($user)){
            return response()->json(['status' => false]);
        };
        $user->is_delete = 1;
        $user->save();
        $res = ['status' => true];
        LabelService::syncLabelMemberCount($groupId);
        return response()->json($res);
    }
}
