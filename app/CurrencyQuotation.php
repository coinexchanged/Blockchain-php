<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyQuotation extends Model
{
    protected $table = 'currency_quotation';
    public $timestamps = false;
    protected $appends = [
        'currency_name',
        'legal_name',
        'rmb_relation'
    ];

    public function getRmbRelationAttribute()
    {
        return $this->currency()->value('rmb_relation');
    }
    public function updateData($data)
    {
        self::unguard();
        $result = $this->fill($data)->save();
        self::reguard();
        return $result;
    }

    public static function getInstance($legal_id, $currency_id)
    {
        $quotation = self::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if (!$quotation) {
            $currency_match = CurrencyMatch::where('legal_id', $legal_id)
                ->where('currency_id', $currency_id)
                ->first();
            $quotation = new self();
            $quotation->match_id = $currency_match->id ?? 0;
            $quotation->legal_id = $legal_id;
            $quotation->currency_id = $currency_id;
            $quotation->change = '';
            $quotation->volume = 0;
            $quotation->now_price = 0;
        }
        $quotation->add_time = time();
        $result = $quotation->save();
        return $quotation;
    }

    public static function updateTodayPriceTable($data)
    {

        $quotation = self::getInstance($data['legal_id'], $data['currency_id']);
        if (!isset($data['change'])) {
            //获得开盘价
            $open_price = TransactionComplete::getOpenPrice($data['currency_id'], $data['legal_id']);
            //计算涨跌百分比
            $change_ratio = bc_mul(bc_div(bc_sub($data['now_price'], $open_price), $open_price), 100, 4);
            if (bc_comp($change_ratio, 0) > 0) {
                $change_ratio = '+' . $change_ratio;
            }
            $data['change'] = $change_ratio;
        }
        $result = $quotation->updateData($data);

        $quotation->setAttribute('type', 'daymarket');
        $quotation->addHidden('id');
        //推送数据
        $send_data = $quotation->toArray();
        UserChat::sendChat($send_data);
        return $result;
    }

    public function currency()
    {
        return $this->belongsTo('App\Currency', 'currency_id', 'id')->withDefault();
    }

    public function legal()
    {
        return $this->belongsTo('App\Currency', 'legal_id', 'id')->withDefault();
    }

    public function getCurrencyNameAttribute()
    {
        return $this->currency()->value('name');
    }

    public function getLegalNameAttribute()
    {
        return $this->legal()->value('name');
    }
}
