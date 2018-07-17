<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Msg extends Model
{
    const TYPE_SINGLE = 0;
    const TYPE_TASK = 1;
    const TYPE_PLAN = 2;
    const TYPE_SYSTEM = 3;
    const STATUS_WAIT = 0;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 4;
    protected $table = 'msg';
}
