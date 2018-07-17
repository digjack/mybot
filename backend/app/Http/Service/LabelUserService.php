<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/7/14
 * Time: 15:37
 */
namespace App\Http\Service;

use App\GroupUser;

class LabelUserService{

    //判断是否存在重复的用户
    public function isRepeat($labelId, $nickname, $remarkName){
        $user =  GroupUser::where('group_id', $labelId)
            ->where(function ($query) use ($nickname, $remarkName) {
                $query->where('nick_name', $nickname);
                if(! empty($remarkName)){
                    $query->orWhere('remark_name', $remarkName);
                }
            })
            ->where('is_delete', 0)
            ->first();
        if(! empty($user)){
            return true;
        }
        return false;
    }
}