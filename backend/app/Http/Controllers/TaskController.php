<?php
/**
 * Created by PhpStorm.
 * User: banli
 * Date: 2018/7/7
 * Time: 1:23
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\Group;
use Illuminate\Support\Facades\Redis;
use Session;
use App\Http\Service\TaskService;
use App\Http\Service\MsgService;
use App\Http\Service\BotService;
class TaskController extends Controller
{
    public function list(Request $request){
        $groupMp = new Group();
        $user = Session::get('user')->toArray();
        $tasks = Task::where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->orderBy('id', 'DESC')
            ->get()->toArray();
        foreach ($tasks  as $index =>  $task){
            if($task['receiver_type'] == Task::RECEIVER_TYPE_GROUP){
                $label = Group::find($task['receiver']);
                $tasks[$index]['group_name'] = $label?$label->name:'未命名';
            }
        }
        $total = Task::count();
        $res = ['list' => $tasks, 'total' => $total];
        return response()->json($res);
    }

    public function save(Request $request){
        $user = Session::get('user')->toArray();
        $id = $request->input('id', '');
        if(! empty($id)){
            $task = Task::find($id);
        }else{
            $task = new Task();
        }
        $task->name = $request->input('name', '');
        $task->user_id = $user['id'];
        $task->date_cycle = $request->input('date_cycle', '');
        $task->next_date = TaskService::countNextDate($task->date_cycle);
        $task->max_count = $request->input('max_count', 0);
        $task->receiver_type = $request->input('receiver_type');
        $task->receiver = $request->input('receiver');
        $task->content = $request->input('content');
        $task->status = $request->input('status', 2);
        $task->is_delete = 0;
        $task->save();
        return response()->json(['status' => true, 'task_id' => $task->id]);
    }

    public function delete(Request $request, $taskId){
        $task  = Task::find($taskId);
        $task->is_delete = 1;
        $task->save();
        return response()->json(['status'=> true]);
    }

}