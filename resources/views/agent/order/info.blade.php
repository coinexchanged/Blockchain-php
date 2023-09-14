@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-header">订单编号 ： {{$info['id']}}</div>
                <div class="layui-card-header">订单状态 ：<span class="layui-badge">已平仓</span></div>
                <div class="layui-card-header">最新进度 ：<span class="layui-badge-rim">{{$info['complete_time']}}</span><span class="layui-badge layui-bg-blue">平仓</span></div>
                <br>
                <br>
                
                <br>
                <br>
            </div>
        </div>


        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-body" >
                    <div class="layui-row">
                        <div class="layui-col-sm4">
                            <div class="layui-card-header">交易信息</div>
                            <ul class="layui-timeline">

                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            买卖类型 ： {{$info['type_name']}}
                                        </div>
                                    </div>
                                </li>
                               

                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            用户 ： {{$info['account_number']}}
                                        </div>
                                    </div>
                                </li>
                               
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            交易对 ： {{$info['symbol'] }}
                                        </div>
                                    </div>
                                </li>
                               
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            手数 ： {{$info['share'] }}
                                        </div>
                                    </div>
                                </li>
                              
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            倍数 ： {{$info['multiple'] }}
                                        </div>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                        <div class="layui-col-sm4">
                            <div class="layui-card-header">资金信息</div>
                            <ul class="layui-timeline">

                               
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            初始保证金 ： {{$info['origin_caution_money'] }}
                                        </div>
                                    </div>
                                </li>
                               
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            当前可用保证金 ： {{$info['caution_money'] }}
                                        </div>
                                    </div>
                                </li>
                                
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            最终盈亏 ： {{$info['fact_profits'] }}
                                        </div>
                                    </div>
                                </li>
                               
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            交易手续费 ： {{$info['trade_fee'] }}
                                        </div>
                                    </div>
                                </li>
                               
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            隔夜费率 ： {{$info['overnight'] }}
                                        </div>
                                    </div>
                                </li>
                               
                            </ul>
                        </div>
                        <div class="layui-col-sm4">
                            <div class="layui-card-header">状态及时间</div>
                            <ul class="layui-timeline">
                               
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            交易状态 ： {{$info['status_name'] }}
                                        </div>
                                    </div>
                                </li>
                             
                              
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            创建时间 ： {{$info['time'] }}
                                        </div>
                                    </div>
                                </li>
                              
                               
                                <li class="layui-timeline-item">
                                    <i class="layui-icon layui-timeline-axis"></i>
                                    <div class="layui-timeline-content layui-text">
                                        <div class="layui-timeline-title">
                                            平仓时间 ： {{$info['complete_time'] }}
                                        </div>
                                    </div>
                                </li>
                               
                                
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
