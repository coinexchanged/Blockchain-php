<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    protected $table = 'market';
    public $timestamps = false;

    public function getQuotesAttribute(){
        return unserialize($this->attributes['quotes']);
    }

}
