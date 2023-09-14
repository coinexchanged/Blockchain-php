<?php
/**
 * Created by PhpStorm.
 * User: 杨圣新
 * Date: 2018/10/26
 * Time: 16:39
 */

namespace App\Http\Controllers\Admin;


use App\Currency;
use App\CurrencyMatch;
use App\Robot;
use App\RobotPlan;
use App\Users;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class RobotController extends Controller
{

    /**添加一个机器人
     * @return \Illuminate\Http\JsonResponse
     */
    public function add()
    {
        if (request()->isMethod('GET')) {

            $id = request()->input('id', 0);
            if (empty($id)) {
                $result = new Robot();
            } else {
                $result = Robot::find($id);
            }
            $currencies = Currency::where('is_display', 1)->where('is_legal', 0)->orderBy('id', 'desc')->get();
            $legals     = Currency::where('is_display', 1)->where('is_legal', 1)->orderBy('id', 'desc')->get();

            return view('admin.robot.add')->with(['currencies' => $currencies, 'legals' => $legals, 'result' => $result]);
        }

        if (request()->isMethod('POST')) {
            $data['huobi_currency']          = request()->input('huobi_currency', '');
            $data['mult']         = request()->input('mult', '');
            $data['currency_id']       = request()->input('currency_id', 0);
            $data['legal_id']          = request()->input('legal_id', 0);
            $data['number_max']        = request()->input('number_max', 0);
            $data['number_min']        = request()->input('number_min', 0);
            $data['second']            = request()->input('second', '');
            $data['float_number_up']   = request()->input('float_number_up', '0.00000');
            $data['float_number_down'] = request()->input('float_number_down', '0.00000');

//            foreach ($data as $v) if (!$v) return $this->error('请填写完整表单');

//            $buy_user  = Users::where('phone', $data['buy_user'])->first();
//            $sell_user = Users::where('phone', $data['sell_user'])->first();
//
//            if (!$buy_user) return $this->error('找不到买家');
//            if (!$sell_user) return $this->error('找不到卖家');
            if (!is_numeric($data['number_max']) || !is_numeric($data['number_min'])) return $this->error('上下限只能是数字');
            if ($data['number_max'] < $data['number_min']) return $this->error('错误的上下限');
            if (!is_numeric($data['float_number_up']) || !is_numeric($data['float_number_down'])) return $this->error('价格浮动数只能是数字');
            if ($data['float_number_up'] < 0 || $data['float_number_down'] < 0) return $this->error('价格浮动数不能为负数');


            $id = request()->input('id', 0);

            if ($id) {
                $robot = Robot::find($id);
            } else {
                $robot              = new Robot();
                $robot->create_time = time();
            }

            $data['sell'] = request()->input('sell', 0);
            $data['buy']  = request()->input('buy', 0);

            DB::beginTransaction();
            try {
                $robot->buy_user_id ='auto';
                $robot->sell_user_id='auto';
                $robot->huobi_currency       = $data['huobi_currency'];
                $robot->mult      = $data['mult'];
                $robot->currency_id       = $data['currency_id'];
                $robot->legal_id          = $data['legal_id'];
                $robot->number_max        = $data['number_max'];
                $robot->number_min        = $data['number_min'];
                $robot->second            = $data['second'];
                $robot->float_number_down = $data['float_number_down'];
                $robot->float_number_up   = $data['float_number_up'];
                $robot->sell              = $data['sell'];
                $robot->buy               = $data['buy'];

                $info = $robot->save();
                if (!$info) throw new \Exception('保存失败');

                DB::commit();
                return $this->success('保存成功');
            } catch (\Exception $e) {
                DB::rollback();
                return $this->error($e->getMessage());
            }
        }
    }

    public function scheAdd()
    {
        if (request()->isMethod('GET')) {

            $id = request()->input('id', 0);
            if (empty($id)) {
                $result = new Robot();
            } else {
                $result = Robot::find($id);
            }
            $currencies = CurrencyMatch::where('market_from', 0)->orderBy('id', 'desc')->get();
            $legals     = Currency::where('is_display', 1)->where('is_legal', 1)->orderBy('id', 'desc')->get();

            return view('admin.robot.scheadd')->with(['currencies' => $currencies,'rid'=>request()->get('rid'), 'legals' => $legals, 'result' => $result]);
        }

        if (request()->isMethod('POST')) {
            $data = request()->post();



            $id = request()->input('id', 0);

            if ($id) {
                $robot = RobotPlan::find($id);
            } else {
                $robot  = new RobotPlan();
            }


            DB::beginTransaction();
            try {

                $robot->itime = strtotime($data['itime']);
                $robot->etime = strtotime($data['etime']);

                $robot->base = $data['base']??'';
                $robot->target = $data['target']??'';
                $robot->remark=$data['remark']??'没有描述';
                $robot->float_down=$data['float_down']??0;
                $robot->float_up=$data['float_up'];
                $robot->max_price=$data['max_price']??0;
                $robot->min_price=$data['min_price']??0;
                $robot->rid = $data['rid'];

                $info = $robot->save();
                if (!$info) throw new \Exception('保存失败');

                DB::commit();
                return $this->success('保存成功');
            } catch (\Exception $e) {
                DB::rollback();
                return $this->error($e->getMessage());
            }
        }
    }

    /**返回页面
     *
     */
    public function list()
    {
        return view('admin.robot.list');
    }

    public function sche()
    {
        return view('admin.robot.schelist',[
            'rid'=> request()->get('rid'),
        ]);
    }

    /**返回列表数据
     * @return \Illuminate\Http\JsonResponse
     */
    public function listData()
    {
        $limit = request()->input('limit', 10);
        $list  = Robot::paginate($limit);
        return $this->layuiData($list);
    }
    public function scheData()
    {
        $limit = request()->input('limit', 10);
        $list  = RobotPlan::where('rid',request()->input('rid'))->paginate($limit);
        return $this->layuiData($list);
    }


    public function delete()
    {
        $id    = request()->input('id', 0);
        $robot = Robot::find($id);

        if (!$robot) return $this->error('找不到这个机器人');

        if ($robot->status == Robot::START) return $this->error('机器人正在运行,不能删除');

        DB::beginTransaction();
        try {
            $info = $robot->delete();
            if (!$info) throw new \Exception('删除失败');

            DB::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }
    }

    public function scheDelete()
    {
        $id    = request()->input('id', 0);
        $robot = RobotPlan::find($id);

        if (!$robot) return $this->error('找不到这个机器人');

        DB::beginTransaction();
        try {
            $info = $robot->delete();
            if (!$info) throw new \Exception('删除失败');

            DB::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }
    }

    public function start()
    {
        $id    = request()->input('id', 0);
        $robot = Robot::find($id);
        $robot->status=$robot->status==Robot::START?Robot::STOP:Robot::START;
        $robot->save();
    }


}
