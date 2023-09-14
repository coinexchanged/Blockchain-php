<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Users;

class Card extends Controller
{
    public function set_card(Request $request)
    {
        $user_id = Users::getUserId();
        $username = $request->get('username', '');
        $country = $request->get('country', '');
        $currency = $request->get('currency', '');
        $sum = $request->get('sum', 0);
        
        if($username == '' || $country == '' || $currency == '' || $sum == 0) return $this->error('请填写完整表单');
        $first_user = DB::table("users")->where("account_number",$username)->where("id",$user_id)->first();
        if(!$first_user) return $this->error('帐号错误');
        $first_card = DB::table("card_issuance")->where("uid",$user_id)->first();
        if($first_card) return $this->error('你已经申请过了');
        $sum = floor($sum*1000)/1000;
        $data = [
            'uid' => $first_user->id,
            'username' => $username,
            'country' => $country,
            'currency' => $currency,
            'sum' => $sum
        ];
        DB::table("card_issuance")->insert($data);
        return $this->success("提交成功");
    }
    
    public function get_card()
    {
        $user_id = Users::getUserId();
        $first_card = DB::table("card_issuance")->where("uid",$user_id)->first();
        return $this->success($first_card);
    }
}