<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Needle as NeedleModel;

class Exchange extends Controller
{
    //
    public function index()
    {
        $news = self::needleList(0, 10);
        $count = count($news);
        $data = [
            'count'=> $count,
            'news' => $news
        ];
        return view('admin.needle.index', [
            'data'=> $data,
        ]);
    }

    public static function needleList($cId = 0,$num = 0)
    {
        $news_query = NeedleModel::where(function ($query) use ($cId) {
            $cId > 0 && $query->where('id', $cId);
        })->orderBy('id', 'desc');
        $news = $num != 0 ? $news_query->paginate($num) : $news_query->get();
        return $news;
    }
}
