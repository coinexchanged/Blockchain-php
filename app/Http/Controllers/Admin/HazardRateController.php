<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\LeverTransaction;
use App\UsersWallet;
use App\Currency;
use App\CurrencyMatch;
use App\MarketHour;
use App\CurrencyQuotation;

class HazardRateController extends Controller
{
    public function index()
    {
        $currencies = Currency::where('is_legal', 1)->get();
        return view('admin.lever.hazard.index')->with('currencies', $currencies);
    }

    public function handle()
    {
        return view('admin.lever.hazard.handle');
    }

    public function postHandle(Request $request)
    {
        $trade_id = $request->input('id', 0);
        $update_price = $request->input('update_price', 0);
        $write_market = $request->input('write_market', 0);
        if ($trade_id <= 0 || $update_price <= 0) {
            return $this->error('参数不合法');
        }
        $time = microtime(true);
        $trade = LeverTransaction::where('status', LeverTransaction::TRANSACTION)->find($trade_id);
        if (!$trade) {
            return $this->error('交易不存在或已平仓');
        }
        $legal_id = $trade->legal;
        $legal = Currency::find($legal_id);
        $currency_id = $trade->currency;
        $currency = Currency::find($currency_id);
        DB::beginTransaction();
        try {
            //MarketHour::batchWriteMarketData($currency_id, $legal_id, 0, $update_price, 3, intval($time));
            MarketHour::batchEsearchMarket($currency->name, $legal->name, $update_price, intval($time)); //更新esearch行情价格
            $result = CurrencyQuotation::getInstance($legal_id, $currency_id)->updateData(['now_price' => $update_price]); //更新数据库价格
            if (!$result) {
                throw new \Exception('更新每日价格失败');
            }
            LeverTransaction::newPrice($legal_id, $currency_id, $update_price, $time);
            DB::commit();
            return $this->success('向系统发送价格成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function lists(Request $request)
    {
        $limit = $request->input('limit', 10);
        $legal_id = $request->input('legal_id', -1);
        $type = $request->input('type', -1);
        $operate = $request->input('operate', -1);
        $hazard_rate = $request->input('hazard_rate', 0);
        $user_id = $request->input('user_id', 0);

        $user_hazard = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->where(function ($query) use ($user_id, $type, $legal_id) {
                !empty($user_id) && $query->where('user_id', $user_id);
                ($type != -1 && in_array($type, [1, 2])) && $query->where('type', $type);
                $legal_id != -1 && $query->where('legal', $legal_id);
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $items = $user_hazard->getCollection();
        $items->transform(function ($item, $key) use ($legal_id) {
            $user_wallet = UsersWallet::where('currency', $legal_id)
                ->where('user_id', $item->user_id)
                ->first();
            $item->setAppends(['symbol', 'mobile', 'account_number', 'type_name', 'profits']);
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

    public function total()
    {
        $currencies = Currency::where('is_legal', 1)->get();
        return view('admin.lever.hazard.total')->with('currencies', $currencies);
    }

    public function totalLists(Request $request)
    {
        $limit = $request->input('limit', 10);
        $legal_id = $request->input('legal_id', -1);
        $type = $request->input('type', -1);
        $operate = $request->input('operate', -1);
        $hazard_rate = $request->input('hazard_rate', 0);
        /*
        SELECT
            `user_id`,
            SUM((case `type` when 1 then update_price-price when 2 then price-update_price END)) AS profits_total,
            SUM(caution_money) AS caution_money_total
        FROM lever_transaction
        WHERE `status`=0
        GROUP BY user_id
         */
        if($legal_id == -1){
            $legal_id=Currency::where('name','USDT')->first()->id??-1;
        }
        $user_hazard = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->where(function ($query) use ($type, $legal_id) {
                ($type != -1 && in_array($type, [1, 2])) && $query->where('type', $type);
                $legal_id != -1 && $query->where('legal', $legal_id);
            })
            ->select('user_id')
            ->selectRaw('SUM((CASE `type` WHEN 1 THEN `update_price` - `price` WHEN 2 THEN `price` - `update_price` END)  * `number`) AS `profits_total`')
            ->selectRaw('SUM(`caution_money`) AS `caution_money_total`')
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
                ->setAttribute('hazard_rate', floatval($hazard_rate));
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
