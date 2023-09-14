<?php

namespace App;

use App\Scopes\SiteScope;
use Illuminate\Database\Eloquent\Model;


class AdminModule extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_module';
    public $timestamps = false;


    public function actions()
    {
        return $this->hasMany('App\AdminModuleAction');
    }
}
