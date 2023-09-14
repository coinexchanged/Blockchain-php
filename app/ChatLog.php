<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    //
    protected $table = 'chat_log';

    protected $appends = ['from_user_head'];

    public function getFromUserHeadAttribute()
    {
        return $this->from_user()->value('head_portrait');
    }

    public function from_user()
    {
        return $this->belongsTo(Users::class, 'from_user');
    }

    public function to_user()
    {
        return $this->belongsTo(Users::class, 'to_user');
    }
}
