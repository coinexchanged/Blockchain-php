<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\{Agent, LeverTransaction};

class NewDoJie implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lever_ids;
   

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lever_ids) {

        $this->lever_ids=$lever_ids;
       

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        LeverTransaction::where('status' , LeverTransaction::CLOSED)
            ->where('settled' , 0)
            ->whereIn('id',$this->lever_ids)
            ->chunk(100, function($lever_transactions) {
                foreach ($lever_transactions as $key => $trade) {
                    try {
                        DB::transaction(function () use ($trade) {
                            //取出该用户的代理商关系数组
                            $agent_path=$trade->agent_path;
                            
                            $_p_arr =explode(',', $agent_path);

                            if (!empty($_p_arr)) {
                               
                                $da=[];
                                foreach($_p_arr as $k=>$v){
                                    $agent=Agent::getAgentById($v);
                                  
                                    $da[$k]['agent_id']=$v;
                                    $da[$k]['pro_loss']=$agent->pro_loss;
                                    $da[$k]['pro_ser']=$agent->pro_ser;

                                }

                                //极差收益
                                foreach ($da as $k=>$val){
                                     
                                    if($k==0){
                                        $pro_loss=$val['pro_loss'];
                                        $pro_ser=$val['pro_ser'];
                                    }else{
                                        $n=$k-1;
                                        $pro_loss=bc_sub($val['pro_loss'],$da[$n]['pro_loss']);
                                        $pro_ser=bc_sub($val['pro_ser'],$da[$n]['pro_ser']);
                                    }
 
                                    //头寸收益
                                    if ($pro_loss > 0){
                                        //盈亏收益 . 头寸收益是反的，需要取相反数
                                        $_base_money =bc_mul($trade->fact_profits , -1);
                                        $change = bc_mul($_base_money , $pro_loss/100);

                                        Agent::change_agent_money(
                                            $val['agent_id'] ,
                                            1  ,
                                            $change,
                                            $trade->id,
                                            '您的下级用户'.$trade->user_id.'的订单产生的头寸收益为'.$change.'。订单编号为'.$trade->id,
                                            $trade->user_id,
                                            $trade->legal
                                        );
                                    }

                                    //手续费收益
                                    if ($pro_ser >0){
                                        //手续费收益
                                        $change = bc_mul($trade->trade_fee ,$pro_ser/100);

                                        Agent::change_agent_money(
                                            $val['agent_id'],
                                            2  ,
                                            $change,
                                            $trade->id,
                                            '您的下级用户'.$trade->user_id.'的订单产生的手续费收益为'.$change.'。订单编号为'.$trade->id,
                                            $trade->user_id,
                                            $trade->legal
                                        );
                                    }

                                }
                            }
                            LeverTransaction::where('id' , $trade->id)->update(['settled' =>1]);
                        });

                    } catch (\Exception $e) {
                        echo 'File :' . $e->getFile() . PHP_EOL;
                        echo 'Line :' . $e->getLine() . PHP_EOL;
                        echo 'Msg :' . $e->getMessage(). PHP_EOL;
                    }
                }
            });


    }
}
