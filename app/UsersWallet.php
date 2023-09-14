<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;

use App\Currency;
use App\Utils\ZtPay;
use Illuminate\Database\Eloquent\Model;

class UsersWallet extends Model
{

    protected $table = 'users_wallet';

    public $timestamps = false;

    /* const CREATED_AT = 'create_time'; */
    const CURRENCY_DEFAULT = "USDT";

    protected $hidden = [
        'private'
    ];

    protected $appends = [
        'currency_name',
        'currency_type',
        'is_legal',
        'is_lever',
        'is_match',
        'is_micro',
        'cny_price',
        'pb_price',
        'usdt_price'
    ];

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getCurrencyTypeAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('type');
    }

    // public function getExrateAttribute()
    // {
    // // $value = $this->attributes['create_time'];
    // return $ExRate = Setting::getValueByKey('ExRate',6.5);;
    // }
    public function getCurrencyNameAttribute()
    {
        return $this->currencyCoin()->value('name');
    }

    public function getIsLegalAttribute()
    {
        return $this->currencyCoin()->value('is_legal');
    }

    public function getIsLeverAttribute()
    {
        return $this->currencyCoin()->value('is_lever');
    }

    public function getIsMatchAttribute()
    {
        return $this->currencyCoin()->value('is_match');
    }

    public function getIsMicroAttribute()
    {
        return $this->currencyCoin()->value('is_micro');
    }

    public function currencyCoin()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }

    public static function create_uuid($prefix=""){
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr ( $chars, 0, 8 ) . ''
            . substr ( $chars, 8, 4 ) . ''
            . substr ( $chars, 12, 4 ) . ''
            . substr ( $chars, 16, 4 ) . ''
            . substr ( $chars, 20, 12 );
        return $prefix.$uuid ;
    }

    public static function makeWallet($user_id)
    {
        $currency = Currency::all();
        // $address_url = '/v3/wallet/address';
        // $project_name = config('app.name');
        // $http_client = app('LbxChainServer');
        // $response = $http_client->post($address_url, [
        //     'form_params' => [
        //         'userid' => $user_id,
        //         'projectname' => $project_name
        //     ]
        // ]);
        // $result = json_decode($response->getBody()->getContents());
        // if ($result->code != 0) {
        //     return false;
        // }
        // $address = $result->data;
        foreach ($currency as $key => $value) {
            $userWallet = new self();
            $userWallet->user_id = $user_id;

            $count = self::where(['user_id' => $user_id, 'currency' => $value->id])->count();
            if ($count > 0) {
                continue;
            }

            if (!in_array($value->id, [1, 3])) {
                $userWallet->address = self::create_uuid();
                $userWallet->create_time = time();
                $userWallet->private = 0;
                $userWallet->currency = $value->id;
                $userWallet->save();
                continue;
            }
            // if ($value->type == 'btc') {
            //     $userWallet->address = config('app.btc');
            //     $userWallet->private = '0';
            // } elseif ($value->type == 'usdt') {
            //     $userWallet->address = config('app.usdt');
            //     $userWallet->private = '0';
            // } elseif ($value->type == 'eth') {
            //     $userWallet->address = config('app.eth');
            //     $userWallet->private = '0';
            // } elseif ($value->type == 'erc20') {
            // 	if($value->name == 'BCH'){
            // 		$userWallet->address = config('app.bch');
            // 	}else{
            // 		$userWallet->address = config('app.erc20');
            //     	$userWallet->private = '0';
            // 	}

            // } else {
            //     continue;
            // }
            // if(empty($userWallet->address)){
            // 	continue;
            // }
            //弃用钱包 直接随机生成一个字符串
//            if ($value->type == 'usdt' || $value->id == 3) {
//                $address = self::getZtPayAddress();
//
//                $userWallet->address = $address;
//            } else if ($value->type == 'btc' || $value->id == 1) {
//                $address = self::getZtPayAddress('BTC');
//                $userWallet->address = $address;
//            } else {
//                $userWallet->address = md5("user_wallet_" . $user_id . '_currency_id' . $value->id);
//            }

            $userWallet->address = md5("user_wallet_" . $user_id . '_currency_id' . $value->id);
            $userWallet->address = $value->id == 3 ? ("0x{$userWallet->address}") : substr(base64_encode($userWallet->address), 0, 34);
            $userWallet->currency = $value->id;
            $userWallet->create_time = time();
            $userWallet->save(); // 默认生成所有币种的钱包
        }
        return true;
    }

    private static function getZtPayAddress($name = 'USDT_ERC20')
    {
        $data = array(
            'appid' => trim(env('ZTPAY_APPID')),
            'method' => "get_address",
            'name' => $name,
        );
        $data['sign'] = ZtPay::getSign($data);
//        var_dump();
        $res = ZtPay::http_request($data);
//        var_dump($res);
        if ($res['code'] == 0) {
            return $res['data']['address'];
        } else {
            return false;
        }
    }


    public function getUsdtPriceAttribute()
    {
        return $this->currencyCoin()->value('price') ?? 1;
    }

    public function getPbPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getPbPrice($currency_id);
    }

    public function getCnyPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getCnyPrice($currency_id);
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function getPrivateAttribute($value)
    {
        return empty($value) ? '' : decrypt($value);
    }

    public function setPrivateAttribute($value)
    {
        $this->attributes['private'] = encrypt($value);
    }

    public function getAccountNumberAttribute($value)
    {
        return $this->user()->value('account_number') ?? '';
    }
}
