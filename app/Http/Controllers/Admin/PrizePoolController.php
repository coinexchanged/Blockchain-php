<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\PrizePool;

class PrizePoolController extends Controller
{
    public function index()
    {
        $scene_list = PrizePool::enumScene();
        return view('admin.prizepool.index')->with('scene_list', $scene_list);
    }

    public function lists(Request $request)
    {
        $limit = $request->input('limit', 10);
        $prize_pool = PrizePool::whereHas('toUser', function ($query) use ($request) {
                $account_number = $request->input('account_number');
                if ($account_number) {
                    $query->where('account_number', $account_number)
                        ->orWhere('phone', $account_number)
                        ->orWhere('email', $account_number);
                }
            })->where(function ($query) use ($request) {
                $scene = $request->input('scene', -1);
                $start_time = strtotime($request->input('start_time', null));
                $end_time = strtotime($request->input('end_time', null));
                $scene != -1 && $query->where('scene', $scene);
                $start_time && $query->where('create_time', '>=', $start_time);
                $end_time && $query->where('create_time', '<=', $end_time);
            })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($prize_pool);
    }

    public function count(Request $request)
    {
        $count_data = PrizePool::selectRaw('1 as user_count')
            ->selectRaw('sum(`reward_qty`) as reward_qty')
            ->whereHas('toUser', function ($query) use ($request) {
                $account_number = $request->input('account_number');
                if ($account_number) {
                    $query->where('account_number', $account_number)
                        ->orWhere('phone', $account_number)
                        ->orWhere('email', $account_number);
                }
            })->where(function ($query) use ($request) {
                $scene = $request->input('scene', -1);
                $start_time = strtotime($request->input('start_time', null));
                $end_time = strtotime($request->input('end_time', null));
                $scene != -1 && $query->where('scene', $scene);
                $start_time && $query->where('create_time', '>=', $start_time);
                $end_time && $query->where('create_time', '<=', $end_time);
            })->groupBy('to_user_id')->get();
        $user_count = $count_data->pluck('user_count')->sum();
        $reward_total = 0;
        $count_data->pluck('reward_qty')->each(function ($item, $key) use (&$reward_total) {
            $reward_total = bc_add($reward_total, $item);
        });
        return response()->json([
            'user_count' => $user_count,
            'reward_total' => $reward_total,
        ]);
    }
}
