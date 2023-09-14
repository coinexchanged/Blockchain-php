<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;

class Analyst extends Controller
{
    public function get_analyst_list()
    {
        $all = DB::table("analyst")->get();
        $a = [];
        foreach ($all as $k => $v){
            $all2 = DB::table("history_record")->where("cid",$v->id)->get();
            $v->data=$all2;
            array_push($a,$v);
        }
        return $this->success($a);
    }
}