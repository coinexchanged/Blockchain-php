<?php
/**
 * create by vscode
 * @author lion
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends ShopModel
{

    protected $table = 'news_category';

    protected $dateFormat = 'U';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }

    public function getUpdateTimeAttribute()
    {
        $value = $this->attributes['update_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }
    /**
     *定义分类和新闻的一对多关联
     */
    
    public function news()
    {
        return $this->hasMany('App\News', 'c_id');
    }
    protected static function boot(){
        
    }
}
