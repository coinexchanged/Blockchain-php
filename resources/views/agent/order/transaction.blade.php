@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto"
                 lay-filter="layadmin-userfront-formlist">
                <form class="layui-form  layui-inline" action="">

                    <div class="layui-inline">
                        <label class="layui-form-label">用户账号</label>
                        <div class="layui-input-block">
                            <input type="text" name="account_number" autocomplete="off" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-inline">
                        <label class="layui-form-label">法币</label>
                        <div class="layui-input-inline" style="width:130px;">
                            <select name="legal" id="type_type">
                                <option value="-1" class="ww">全部</option>
                                @foreach ($legal_currencies as $currency)
                                    <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">交易币</label>
                        <div class="layui-input-inline" style="width:130px;">
                            <select name="currency" id="type_type">
                                <option value="-1" class="ww">全部</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">开始日期：</label>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="text" class="layui-input" id="start_time" value="" name="start_time">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结束日期：</label>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="text" class="layui-input" id="end_time" value="" name="end_time">
                        </div>
                    </div>

                    <div class="layui-inline">
                        <button class="layui-btn" lay-submit="" lay-filter="mobile_search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                    </div>


                </form>

            </div>
            <div class="layui-card-body">
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                    <ul class="layui-row layui-col-space10 layui-this">
                        <li class="layui-col-xs3">
                            <a href="javascript:;" class="layadmin-backlog-body"
                               style="color: #fff;background-color: #01AAED;">
                                <h3>撮合交易合计：</h3>
                                <p><cite style="color:#fff" id="sum">0.0000000</cite></p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="layui-card-body">
                <table id="demo" lay-filter="test"></table>
            </div>
        </div>
    </div>

   

@endsection

@section('scripts')
    <script>

        layui.use(['table','form','laydate'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            var laydate = layui.laydate;
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('agent/order/transaction_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width: 90, sort: true}
                    ,{field: 'currency_name', title: '交易币', width: 90}
                    ,{field: 'legal_name', title: '法币', width: 90}
                    ,{field: 'account_number', title: '买家', width: 120}
                    ,{field: 'from_number', title: '卖家', width: 120}
                    ,{field: 'price', title: '价格', width: 120}
                    ,{field: 'number', title: '数量', width: 100}
                    ,{field: 'time', title: '创建时间', width: 180}
                ]], done: function(res){
                    $("#sum").text(res.extra_data);
                }
            });
           


            //监听提交
            form.on('submit(mobile_search)', function(data){
               
                table.reload('mobileSearch',{
                    where: data.field,
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

@endsection