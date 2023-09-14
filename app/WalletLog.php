<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletLog extends Model
{
    //
    protected $table = "wallet_log";

    public $timestamps = false;

    const LEGAL_BALANCE = 1;//法币余额
    const ASSETS_BALANCE = 2;//币币类型
    const LEVER_BALANCE = 3;//杠杆交易
    const MICRO_BALANCE = 4;//秒合约交易
    const INSURANCE_BALANCE = 5;//保险交易
    /**
     * 模型日期的存储格式
     *
     * @var string
     */
    protected $dateFormat = 'U';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 一对一关联account_log模型
     */
    public function accountLog()
    {
        return $this->belongsTo('App\Accountlog','account_log_id','id')->withDefault();
    }
    /**
     * 一对一关联users_wallet模型
     */
    public function UsersWallet(){
        return $this->belongsTo('App\UsersWallet','wallet_id','id')->withDefault();
    }
}
