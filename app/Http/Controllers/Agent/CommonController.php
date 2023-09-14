<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use App\Currency;

class CommonController extends Controller
{
    public function legalCurrency()
    {
        $currencies = Currency::where('is_legal', 1)->get();
        return $this->ajaxReturn($currencies);
    }
}
