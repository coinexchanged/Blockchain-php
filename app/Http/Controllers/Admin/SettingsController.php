<?php

namespace App\Http\Controllers\Admin;


use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class SettingsController extends Controller{

    public function index(){
        $bouns = Setting::getValueByKey('user_bonus');
        $ecology = Setting::getValueByKey('ecology_bonus');
        $bonus = json_decode($bouns);
        $ecology = json_decode($ecology);
        return view('admin.settings.index',['bouns'=>$bonus,'ecology'=>$ecology]);
    }
    public function base()
    {
        $rate_exchange     = Setting::getValueByKey('rate_exchange');
        $company_eth_address       = Setting::getValueByKey('company_eth_address');
        $lock_daily_return       = Setting::getValueByKey('lock_daily_return');
        $version       = Setting::getValueByKey('version','1.0');
        $transaction_fee       = Setting::getValueByKey('transaction_fee',[]);
        $transaction_fee = @json_decode($transaction_fee,true);

        $results = array(
            'rate_exchange'     => $rate_exchange,
            'lock_daily_return'     => $lock_daily_return,
            'company_eth_address'       => $company_eth_address,
            'version'       => $version,
        );
        return view('admin.settings.base', ['results' => $results,"transaction_fee"=>$transaction_fee]);
    }
    public function setBase(Request $request)
    {
        $data = $request->all();

        $transaction_fee = array(
            "service_one_min"=>$data["service_one_min"],
            "service_one_max"=>$data["service_one_max"],
            "service_one_proportion"=>$data["service_one_proportion"],
            "service_two_min"=>$data["service_two_min"],
            "service_two_max"=>$data["service_two_max"],
            "service_two_proportion"=>$data["service_two_proportion"],
            "service_three_min"=>$data["service_three_min"],
            "service_three_max"=>$data["service_three_max"],
            "service_three_proportion"=>$data["service_three_proportion"],
        );

        foreach ($data as $key => $value) {
            if (isset($transaction_fee[$key])){
                continue;
            }
            switch ($key) {
                case 'rate_exchange':
                    break;
                case 'company_eth_address':
                    break;
                case 'lock_daily_return':
                    break;
                case 'version':
                    break;
            }
            Setting::updateValueByKey($key, $value);
        }

        $transaction_fee = json_encode($transaction_fee);
        Setting::updateValueByKey("transaction_fee", $transaction_fee);

        return $this->success('操作成功');
    }
    public function Insert(){
        $bouns = Input::get('bonus','');
        $total_arr = Input::get('total_arr','');
        $ecology = Input::get('ecology','');
        $ecology_arr = Input::get('ecology_arr','');

        $total_arr = json_encode($total_arr);
        $ecology_arr = json_encode($ecology_arr);

        if(empty($bouns) || empty($total_arr)){
            return $this->error('日均收益参数错误');
        }
        if(empty($ecology) || empty($ecology_arr)){
            return $this->error('推广奖励参数错误');
        }
//        $first_result = Setting::where('key',$bouns)->where('value',$total_arr)->first();
//        if(!empty($first_result)){
//            return $this->success('已设置过该奖金');
//        }
//        $second_result = Setting::where('key',$ecology)->where('value',$ecology_arr)->first();
//        if(!empty($second_result)){
//            return $this->success('已设置过该推广奖励');
//        }
        try{
            Setting::updateValueByKey($bouns,$total_arr);
            Setting::updateValueByKey($ecology,$ecology_arr);
            return $this->success('设置成功');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
?>