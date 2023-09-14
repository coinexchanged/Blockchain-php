<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LegalStore extends Model
{
    //
    protected $table = 'legal_store';

    public function getQueueableRelations()
    {
        // TODO: Implement getQueueableRelations() method.
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }

}
