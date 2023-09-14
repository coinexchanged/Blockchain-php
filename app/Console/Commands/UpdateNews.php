<?php

namespace App\Console\Commands;

use App\News;
use App\UserReal;
use Illuminate\Console\Command;

/**更新项目的新闻
 * Class UpdateNews
 *
 * @package App\Console\Commands
 */
class UpdateNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新项目的新闻';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected $searches = [
        'cfmcoin' => 'toex',
        //'cfmcoin.me'=>'coinbott.com'
        
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $news_list = News::get();

        foreach ($news_list as $news) {
            foreach ($this->searches as $k => $v) {
                $news->content = str_replace($k, $v, $news->content);
                $news->title = str_replace($k, $v, $news->title);
                $news->keyword = str_replace($k, $v, $news->keyword);
                $news->abstract = str_replace($k, $v, $news->abstract);
                $news->thumbnail = str_replace($k, $v, $news->thumbnail);
                $news->cover = str_replace($k, $v, $news->cover);
            }
            $news->save();
        }

        // $list = UserReal::get();

        // foreach ($list as $news) {
        //     foreach ($this->searches as $k => $v) {
        //         $news->front_pic = str_replace($k, $v, $news->front_pic);
        //         $news->reverse_pic = str_replace($k, $v, $news->reverse_pic);
        //         $news->hand_pic = str_replace($k, $v, $news->hand_pic);
              
        //     }
        //     $news->save();
        // }
    }
}