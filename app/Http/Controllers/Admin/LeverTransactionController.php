<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Currency;
use App\LeverTransaction;

class LeverTransactionController extends Controller
{
    public function index()
    {
        $currencies = Currency::where('is_legal', 1)->get();
        return view('admin.lever.hazard.index', [
            'currencies' => $currencies,
        ]);
    }

    public function lists()
    {
        $limit = $request->input('limit', 10);
        $legal_id = $request->input('legal_id', 0);
        $type = $request->input('type', 0);
        $operate = $request->input('operate', -1);
        $hazard_rate = $request->input('hazard_rate', 0);
        $user_hazard = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->where(function ($query) use ($type, $legal_id) {
                ($type != -1 && in_array($type, [1, 2])) && $query->where('type', $type);
                $legal_id != -1 && $query->where('legal', $legal_id);
            })
            ->select('user_id')
            ->selectRaw('SUM((CASE `type` WHEN 1 THEN `update_price` - `price` WHEN 2 THEN `price` - `update_price` END) * `number`) AS `profits_total`')
            ->selectRaw('SUM(`caution_money`) AS `caution_money_total`')
            ->selectRaw('SUM(`origin_caution_money`) AS `origin_caution_money_total`')
            ->groupBy('user_id')
            ->paginate($limit);
        $items = $user_hazard->getCollection();
        $items->transform(function ($item, $key) use ($legal_id) {
            $user_wallet = UsersWallet::where('currency', $legal_id)
                ->where('user_id', $item->user_id)
                ->first();
            $item->setAppends(['mobile', 'account_number']);
            $hazard_rate = LeverTransaction::getWalletHazardRate($user_wallet);
            $balance = $user_wallet->lever_balance ?? 0;
            $item->setAttribute('lever_balance', $balance)
                ->setAttribute('hazard_rate', $hazard_rate);
            return $item;
        });
        if ($operate != -1 && !empty($hazard_rate)) {
            switch ($operate) {
                case 1:
                    $operate_symbol = '>=';
                    break;
                case 2:
                    $operate_symbol = '<=';
                    break;
                default:
                    $operate_symbol = null;
                    break;
            }
            $items = $items->where('hazard_rate', $operate_symbol, $hazard_rate);
        }
        $user_hazard->setCollection($items);
        return $this->layuiData($user_hazard);
    }
}
