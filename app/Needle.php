<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Needle extends Model
{
    //
    protected $table = 'needle';

    public  function  setItimeAttribute($value)
    {
        $this->attributes['itime'] = strtotime($value);
    }

    public  function  getItimeAttribute($value)
    {
        return date('Y-m-d H:i:s',$this->attributes['itime']);
    }

    public function getQueueableRelations()
    {
        // TODO: Implement getQueueableRelations() method.
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
