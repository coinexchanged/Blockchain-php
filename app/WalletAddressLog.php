<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;

use App\Users;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class WalletAddressLog extends Model
{
    protected $table = 'wallet_address_log';
    public $timestamps = false;
    const CREATED_AT = 'ctime';
    protected $appends = [
        'account_number',
        'account',
        'currency_name',//币种
        'manager_name',
    ];

    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    public function getManagerNameAttribute()
    {
        return $this->hasOne('App\Admin', 'id', 'manager_id')->value('username');

    }
    public function getAccountAttribute()
    {
        $value = $this->hasOne('App\Users', 'id', 'user_id')->value('phone');
        if (empty($value)) {
            $value = $this->hasOne('App\Users', 'id', 'user_id')->value('email');
        }
        return $value;
    }

    public function getCtimeAttribute()
    {
        $value = $this->attributes['ctime'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency_id')->value('name');
    }

    public static function insertLog($data = array())
    {
        $data = is_array($data) ? $data : func_get_args();
        $log = new self();
        $log->user_id = $data['user_id'] ?? false;;
        $log->ctime = $data['ctime'] ?? time();
        $log->currency_id = $data['currency_id'] ?? 0;
        $log->manager_id = $data['manager_id'] ?? 0;
        $log->old_address=$data['old_address'] ?? 0;
        $log->new_address=$data['new_address'] ?? 0;
        try {
            DB::transaction(function () use ($log) {
                $log->save();
            });
            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
            return false;
        }
    }

    public function user()
    {
        return $this->belongsTo('App\Users', 'user_id', 'id');
    }

    //关联钱包记录模型
    public function walletLog()
    {
        return $this->hasOne('App\WalletLog', 'account_log_id', 'id')->withDefault();
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
