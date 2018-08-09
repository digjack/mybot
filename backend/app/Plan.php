<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    const STATUS_INIT = 0;
    const STATUS_SENDING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;
    const STATUS_CANCEL = 4;
    const TYPE_FILTER = 1;
    const TYPE_NICK = 2;
    protected $table = 'plan';
}
