<?php
namespace App\DAO;

use App\Activity;
use App\ActivityItem;
use App\Utils\RPC;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;

/**
 * Created by PhpStorm.
 * User: joey
 * Date: 2018/1/31
 * Time: 15:54
 */
class ExpressADO
{
    /**
     *  获取物流详情
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    public static function getContetn($ShipperCode, $LogisticCode)
    {
        $kdniao = Config::get("express.kdniao");
        if (empty($ShipperCode) || empty($LogisticCode))
            return "";
        $requestData= "{'OrderCode':'','ShipperCode':'".$ShipperCode."','LogisticCode':'".$LogisticCode."'}";
        $datas = array(
            'EBusinessID' => $kdniao["EBusinessID"],
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = ExpressADO::encrypt($requestData, $kdniao["AppKey"]);
        $result = ExpressADO::sendPost($kdniao["ReqURL"], $datas);
        $result = json_decode($result,true);
        return $result["Traces"];
    }
    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    public static function sendPost($url, $datas)
    {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }
    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    public static function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }

}