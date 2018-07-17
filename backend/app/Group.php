<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    //
    const TYPE_BY_NICK = 1;
    const TYPE_BY_REMARK = 2;
    const TYPE_BY_WE_GROUP = 3;
    protected $table = 'group';
}
