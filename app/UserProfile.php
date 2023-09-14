<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}
