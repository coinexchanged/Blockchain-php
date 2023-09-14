<?php
/**
 * create by vscode
 * @author lion
 */
namespace App;


use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';
    public $timestamps = false;
    protected $appends = ['role_name'];

    public function getRoleNameAttribute(){
        return $this->hasOne('App\AdminRole','id','role_id')->value('name');
    }
}
