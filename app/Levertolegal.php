<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Levertolegal extends Model
{
    protected $table = 'lever_tolegal';
    public $timestamps = false;

    public function getQuotesAttribute(){
        return unserialize($this->attributes['quotes']);
    }

}
