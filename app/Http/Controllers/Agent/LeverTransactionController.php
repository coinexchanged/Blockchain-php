<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2018/12/4
 * Time: 17:17
 */

namespace App\Http\Controllers\Agent;


use App\LeverTransaction;

class LeverTransactionController extends Controller
{

    public function lists()
    {
        $user_id = request()->input('user_id', 0);
        $status  = request()->input('status', -1);
        $type    = request()->input('type', -1);

        $where = [];

        $where[] = ['user_id',$user_id];
        if ($status!=-1) $where[] = ['status', $status];
        if ($type!=-1) $where[] = ['type', $type];

        $list = LeverTransaction::where($where)->orderBy('id', 'DESC')->paginate();
        return $this->layuiData($list);
    }

}