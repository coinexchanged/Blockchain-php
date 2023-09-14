<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HuobiSymbol extends Model
{
    public $timestamps = false;

    public static function getSymbolsData()
    {
        self::unguard();
        foreach ($symbols as $key => $value) {
            $huobi_symbol = new self();
            $huobi_symbol->fill($value)->save();
        }
        self::reguard();
        return true;
    }
}
