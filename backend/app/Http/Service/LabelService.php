<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/7/14
 * Time: 14:47
 */
namespace App\Http\Service;

use App\Group;
use App\GroupUser;

class LabelService
{

    //同步标签会员数
    public static function syncLabelMemberCount($labelId){
        $label = Group::find($labelId);
        $count = GroupUser::where(['group_id' => $labelId, 'is_delete' => 0])->count('id');
        $label->member_count = $count;
        $label->save();
        return true;
    }
}