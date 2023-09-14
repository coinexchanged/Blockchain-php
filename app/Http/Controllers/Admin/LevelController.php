<?php

namespace App\Http\Controllers\Admin;

use App\Algebra;
use App\Level;
use App\UserAlgebra;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\MicroNumber;
use App\MicroOrder;
use App\MicroSecond;
use App\Currency;
use App\CurrencyMatch;
use App\Setting;
use App\UsersWallet;
use App\Users;

class LevelController extends Controller
{
    public function index()
    {
        return view('admin.level.index');
    }

    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new Level();
        } else {
            $result = Level::find($id);
        }

        return view('admin.level.add')->with('result', $result);
    }

    public function postAdd(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', 0);
            $name = $request->get('name', '');
            $fill_currency = $request->get('fill_currency', '');
            $direct_drive_price = $request->get('direct_drive_price', '');
            $direct_drive_count = $request->get('direct_drive_count', '');
            $max_algebra = $request->get('max_algebra', '');
            $level = $request->get('level', '');
            if (empty($id)) {
                $levelModel = new Level();
            } else {
                $levelModel = Level::find($id);
                if ($levelModel == null) {
                    return redirect()->back();
                }
            }
            $levelModel->name = $name;
            $levelModel->fill_currency = $fill_currency;
            $levelModel->direct_drive_price = $direct_drive_price;
            $levelModel->direct_drive_count = $direct_drive_count;
            $levelModel->max_algebra = $max_algebra;
            $levelModel->level = $level;

            $levelModel->save(); //保存币种
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new Level();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function del(Request $request)
    {
        $id = $request->get('id', 0);
        $result = Level::find($id);
        if (empty($result)) {
            return $this->error('参数错误');
        }
        try {
            $result->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }


    //micro_seconds

    public function algebraIndex()
    {
        return view('admin.level.algebra_index');
    }

    public function algebraAdd(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new Algebra();
        } else {
            $result = Algebra::find($id);
        }
        //        $currencies = Currency::where('is_micro',1)->get();

        return view('admin.level.algebra_add')->with('result', $result);
    }

    public function algebraPostAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $name = $request->get('name', '');
        $algebra = $request->get('algebra', '');
        $rate = $request->get('rate', '');

        if (empty($id)) {
            $result = new Algebra();
        } else {
            $result = Algebra::find($id);
            if ($result == null) {
                return redirect()->back();
            }
        }
        $result->name = $name;
        $result->algebra = $algebra;
        $result->rate = $rate;

        DB::beginTransaction();
        try {
            $result->save(); //保存币种
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function algebraLists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new Algebra();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function algebraDel(Request $request)
    {
        $id = $request->get('id', 0);
        $result = Algebra::find($id);
        if (empty($result)) {
            return $this->error('参数错误');
        }
        try {
            $result->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function levelOrderIndex(){

          return view('admin.level.orders');
    }

    public function levelOrderList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $ent_time=$request->get('end_time','');
        $start_time=$request->get('start_time','');
        $account=$request->get('account','');
        $result = new UserAlgebra();
        $result = $result->where(function ($query) use ($ent_time,$start_time,$account){

            if (!empty($ent_time)){
                $query->where('created_at','<=',$ent_time);
        }

            if (!empty($start_time)){
                $query->where('created_at','>=',$start_time);
            }
            if (!empty($account)){
                $user=Users::where('phone',$account)->Orwhere('email',$account)->first();
                if (!empty($account)){
                    $query->where('user_id',$user->id);
                }
            }
        })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }
}
