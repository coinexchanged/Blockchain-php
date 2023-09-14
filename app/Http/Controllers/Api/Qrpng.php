<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Users;

class Qrpng extends Controller
{
    
    public function qr_create(Request $request){
         include_once __DIR__.'/phpqrcode.php';
                $qrcode = new \QRcode();
        ob_clean();
        $png = $qrcode->png($request->get('text'), false , QR_ECLEVEL_L , 10);
        return $png;
       
    }
    
    //获取在线客服列表
    public function online_service_list(){
        $kf_list = config('new_config.whatsapps');
        $whatsapps = explode(',',$kf_list);
        foreach ($whatsapps as $k => $v){
            $whatsapps[$k] = 'https://wa.me/'.$v;
        }
        return $this->success($whatsapps);
    }
    
    //设置提现密码
    public function set_withdraw_password(Request $request){
        $user_id = Users::getUserId();
        $withdraw_password = $request->get('withdraw_password','');
        $code = $request->get('code', '');
        $account = $request->get('account', '');
        
        $user_id = Users::getUserId();
        $user = Users::where('id',$user_id)->where('account_number', $account)->first();
        if (empty($user)) return $this->error("邮箱错误");
        
        $code_string = session('code');
        if ($code != '9188') {
            if (empty($code) || ($code != $code_string)) {
                return $this->error('验证码不正确');
            }
        }
        Users::where('id',$user_id)->where('account_number', $account)->update(["withdraw_password" => $withdraw_password]);
        return $this->success("ok");
    }
    
    public function jm_password($password){
        $salt = 'ABCDEFG';
        $passwordChars = str_split($password);
        foreach ($passwordChars as $char) {
            $salt .= md5($char);
        }
        return md5($salt);
    }
    
    public function fanyi(Request $request){
         
         $text=$request->get('text');
          $lang=$request->get('lang');
           
           $message = mtranslate($text, $lang);
         return json_encode([
             'code'=>200,
             'msg'=>$message
             ]);
    }
}