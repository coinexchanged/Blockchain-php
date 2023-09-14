<?php
/**
 * create by vscode
 * @author lion
 */
namespace App;


use Illuminate\Database\Eloquent\Model;

class News extends ShopModel
{
    protected $table = 'news';
    //自动时间戳
    protected $dateFormat = 'U';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected static $langList = [
        'zh' => '中文简体',
        'en' => '英文',
        'hk' => '中文繁体',
        'jp' => '日语',
        'kr' =>'韩语',
        'spa'=>'西班牙语'
    ];

    public static function getLangeList()
    {
        return self::$langList;
    }
    /**
     * 定义新闻和分类的一对多相对关联
     */

    public function cate()
    {
        return $this->belongsTo('App\NewsCategory', 'c_id');
    }

    /**
     * 定义新闻和评论的一对多关联
     */

    public function discuss()
    {
        return $this->hasMany('App\NewsDiscuss', 'n_id');
    }
    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }
    public function getThumbnailAttribute()
    {
        $thumbnail = $this->attributes['thumbnail'];
        return $thumbnail ? $thumbnail : URL("images/zwtp.png");
    }

    public function getUpdateTimeAttribute()
    {
        $value = $this->attributes['update_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }
    protected static function boot(){


    }

}
