<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use Session;
use App\Http\Service\UserService;

class LabelController extends Controller
{
    //
    public function list(Request $request){
        $type = $request->input('type', [1, 2]);
        $user = UserService::getUser();
        $labels = Group::where('user_id', $user['id'])
            ->where('is_delete', '0')
            ->whereIn('type', $type)
            ->get()->toArray();
        $total = Group::count();
        $res = ['list' => $labels, 'total' => $total];
        return response()->json($res);
    }

    public function save(Request $request){
        $user = UserService::getUser();
        $userId = $user['id'];
        $labelId = $request->input('id', '');
        if(! empty($labelId)){
            $label = Group::find($labelId);
        }else{
            $label = new Group();
        }
        $label->name = $request->input('name', '');
        $label->user_id = $userId;
        $label->type = $request->input('type', '1');
        $label->is_delete = 0;
        $label->save();
        return response()->json(['status' => true, 'label_id' => $label->id]);
    }

    public function delete(Request $request, $id){
        $label = Group::find($id);
        $label->is_delete = 1;
        $label->save();
        return response()->json(['status' => true, 'label_id' => $label->id]);
    }
}
