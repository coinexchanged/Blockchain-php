<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currency';
    public $timestamps = false;
    protected $appends = ['to_pb_price'];
    protected $hidden = ['key'];

    /**
     * 定义一对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quotation()
    {
        return $this->hasMany(CurrencyMatch::class, 'legal_id', 'id');
    }

    public function microNumbers()
    {
        return $this->hasMany(MicroNumber::class)->orderBy('number', 'asc');
    }

    // public function getExRateAttribute()
    // {
    //     return Setting::getValueByKey('ExRate', 6.5);
    // }

    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }

    public static function getNameById($currency_id)
    {
        $currency = self::find($currency_id);
        return $currency->name;
    }

    // public function getUsdtPriceAttribute()
    // {
    //     $last_price = 1;
    //     $currency_id = $this->attributes['id'];
    //     $last = TransactionComplete::orderBy('id', 'desc')
    //         ->where("currency", $currency_id)
    //         ->where("legal", 1)->first();//1是PB
    //     if (!empty($last)) {
    //         $last_price = $last->price;
    //     }
    //     if ($currency_id == 1) {
    //         $last_price = 1;
    //     }
    //     return $last_price;
    // }


    //获取币种相对于人民币的价格
    public static function getCnyPrice($currency_id)
    {
        $rate = Setting::getValueByKey('USDTRate', 7.08);
        $usdt = Currency::where('name', 'USDT')->select(['id'])->first();
        $last = MarketHour::orderBy('id', 'desc')
            ->where("currency_id", $currency_id)
            ->where("legal_id", $usdt->id)->first();
        if (!empty($last)) {
            $cny_Price = $last->highest * $rate; //行情表里面最近的数据的最高值
        } else {
            //$cny_Price = 1; //如果不存在交易对，默认为1
            //如果不存在行情，取币种默认价格
            $currency = Currency::where('id', $usdt->id)->first();
            $cny_Price = $currency->price * $rate;

        }
        if ($currency_id == $usdt->id) {
            $cny_Price = 1 * $rate;
        }

        return $cny_Price;
    }


    public function getRmbRelationAttribute()
    {
        $rate = Setting::getValueByKey('USDTRate', 7.08);
        return $rate;
    }

    public function getToPbPriceAttribute()
    {
        $currency_id = $this->id;
        $ptb = Currency::where('name', UsersWallet::CURRENCY_DEFAULT)->first();
        $last = MarketHour::orderBy('id', 'desc')
            ->where("currency_id", $currency_id)
            ->where("legal_id", $ptb->id)->first();
        if (!empty($last)) {
            $Price = $last->highest; //行情表里面最近的数据的最高值
        } else {
            $Price = $ptb->price; //如果不存在交易对，默认为1
        }
        if ($currency_id == $ptb->id) {
            $Price = 1;
        }
        $to_pb_price = bcdiv($this->price, $Price, 8);
        return $to_pb_price;
    }
    //获取币种相对于平台币的价格
    public static function getPbPrice($currency_id)
    {
        $ptb = Currency::where('name', UsersWallet::CURRENCY_DEFAULT)->first();
        $last = MarketHour::orderBy('id', 'desc')
            ->where("currency_id", $currency_id)
            ->where("legal_id", $ptb->id)->first();
        if (!empty($last)) {
            $Price = $last->highest; //行情表里面最近的数据的最高值
        } else {
            $Price = $ptb->price; //如果不存在交易对，默认为1
        }
        if ($currency_id == $ptb->id) {
            $Price = 1;
        }

        return $Price;
    }

    public function getOriginKeyAttribute($value)
    {
        $private_key = $this->attributes['key'] ?? '';
        return $private_key != '' ? decrypt($private_key) : '';
    }

    public function getKeyAttribute($value)
    {
        return $value == '' ?: '********';
    }

    public function setKeyAttribute($value)
    {
        if ($value != '') {
            return $this->attributes['key'] = encrypt($value);
        }
    }
    
    public static function getIdByName($currency_name)
    {
        $currency = self::where('name',strtoupper($currency_name))->first();
        return $currency;
    }
}
