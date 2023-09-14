<?php

namespace App;

use App\Scopes\SiteScope;
use Illuminate\Database\Eloquent\Model;


class AdminRole extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_role';
    public $timestamps = false;

}
