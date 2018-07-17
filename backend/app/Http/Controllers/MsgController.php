<?php

namespace App\Http\Controllers;

use App\Http\Service\UserService;
use Illuminate\Http\Request;
use App\Msg;

class MsgController extends Controller
{
    //

    public function list(Request $request){
        $userId = UserService::getUserId();
        $page = $request->input('page', 1);
        $size = $request->input('size', 30);
        $logs = Msg::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->skip(($page-1) * $size)
            ->take($size)
            ->get()
            ->toArray();
        $total = Msg::count();
        return response()->json(['list' => $logs, 'total' => $total]);
    }
}
