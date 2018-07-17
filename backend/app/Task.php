<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    const RECEIVER_TYPE_FRIEND = 'friend';
    const RECEIVER_TYPE_GROUP = 'group';
    protected $table = 'task';
}
