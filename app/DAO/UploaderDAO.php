<?php

namespace App\DAO;

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
use App\Setting;

class UploaderDAO
{
    
    private static $stateMap = [ //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    ];

    /**
     * 上传错误检查
     * @param string $errCode
     * @return string
     */
    public static function getStateInfo($errCode)
    {
        return !self::$stateMap[$errCode] ? self::$stateMap["ERROR_UNKNOWN"] : self::$stateMap[$errCode];
    }

    /**
     * 文件上传
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    public static function fileUpload($file, $scene = '')
    {
        //读取上传参数
        $upload_file_size = intval(Setting::getValueByKey('upload_file_size', 0));
        $upload_file_ext_list = Setting::getValueByKey('upload_file_ext_list');
        $upload_file_ext_list == '' && $upload_file_ext_list = 'png,jpg,jpeg,gif,bmp';
        //文件大小判断
        $upload_file_size *= 1048576; //文件上传最大
        $filesize = $file->getSize();
        $filename = $file->getFilename();
        $origin = $file->getClientOriginalName();
        $ext = $file->guessExtension();
        empty($scene) || $scene .= '/';

        //文件扩展名判断
        $upload_file_ext_list = explode(',', strtolower(str_replace(' ', '', $upload_file_ext_list)));

        if (!in_array($ext, $upload_file_ext_list)) {
            return [
                'state' => self::getStateInfo('ERROR_TYPE_NOT_ALLOWED'),
                'url' => '',
                'title' => $filename,
                'original' => $origin,
                'type' => '.' . $ext,
                'size' => $filesize,
            ];
        }
        //文件大小校验
        if ($upload_file_size > 0 && $filesize > $upload_file_size) {
            return [
                'state' => self::getStateInfo('ERROR_SIZE_EXCEED'),
                'url' => '',
                'title' => $filename,
                'original' => $origin,
                'type' => '.' . $ext,
                'size' => $filesize,
            ];
        }
        //读取存储参数
        $use_qiniu_storage = Setting::getValueByKey('use_qiniu_storage', 0);
        $qiniu_url = Setting::getValueByKey('qiniu_url', '');
        $access_key = Setting::getValueByKey('qiniu_access_key', '');
        $secret_key = Setting::getValueByKey('qiniu_secret_key', '');
        $bucket_name = Setting::getValueByKey('qiniu_bucket_name', '');

        $url = $use_qiniu_storage ? $qiniu_url : url('');

        if ($use_qiniu_storage) {
            $file_obj = new \SplFileObject($file->getPathname());
            $file_content = $file_obj->fread($file->getSize());
            $upManager = new UploadManager();
            $auth = new Auth($access_key, $secret_key);
            $token = $auth->uploadToken($bucket_name);
            list($ret, $error) = $upManager->put($token, $filename, $file_content);
            if ($error) {
                return [
                    'state' => is_string($error) ? $error : $error->message(),
                    'url' => '',
                    'title' => $filename,
                    'original' => $origin,
                    'type' => '.' . $ext,
                    'size' => $filesize,
                ];
            }
            $file_url = $url . '/' . $ret['key'];
        } else {
            $path = '/upload/' . $scene . date('Ymd') . '/';
            $full_path = public_path() . $path;
            file_exists($path) || @mkdir($full_path, 0777, true);
            $file->move($full_path);
            
            if($scene =='admin/'){
                $file_url =$path . $filename;
            }else{
                $file_url = $url . $path . $filename;
            }
            
        }
        return [
            'state' => self::getStateInfo(0),
            'url' => $file_url,
            'title' => $filename,
            'original' => $origin,
            'type' => '.' . $ext,
            'size' => $filesize,
        ];
    }
}
