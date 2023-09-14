@extends('admin._layoutNew')

@section('page-head')
<style>
    .order_type_rise {
        /*
        background-color:#89deb3;
        */
        color:#1aab65;
    }
    .order_type_fall {
        /*
        background-color:#d67a7a;
        */
        color:#de1919;
    }
    .order_type {
        /*
        color:#fff;
        */
        font-size: 10px;
        text-align: center;
    }
    .layui-item {
        margin: 10px;
    }
    .hidden {
        display: none;
    }
</style>
@endsection

@section('page-content')
<div class="layui-form">
    <div class="layui-item">
        <div class="layui-inline" style="margin-left: 10px;">
            <label>交易对</label>
            <div class="layui-input-inline" style="width: 120px">
                <select name="match_id" id="currency_match" class="layui-input">
                    <option value="-1">所有</option>
                    @foreach ($currency_matches as $key=> $currency_match)
                    <option value="{{ $currency_match->id }}">{{ $currency_match->symbol }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>支付货币</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="currency_id" id="currency_id" class="layui-input">
                    <option value="-1">所有</option>
                    @foreach ($currencies as $key => $currency)
                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>买卖类型</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="type" id="type" class="layui-input">
                    <option value="-1">所有</option>
                    <option value="1">买涨</option>
                    <option value="2">买跌</option>
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>保险交易</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="is_insurance" id="is_insurance" class="layui-input">
                    <option value="-1">所有</option>
                    <option value="2">反向</option>
                    <option value="1">正向</option>
                    <option value="0">否</option>
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>交易状态</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="status" id="status" class="layui-input">
                    <option value="-1">所有</option>
                    <option value="1">交易中</option>
                    <option value="2">平仓中</option>
                    <option value="3">已平仓</option>
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>预设</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="pre_profit_result" id="pre_profit_result" class="layui-input">
                    <option value="-2">所有</option>
                    <option value="-1">亏</option>
                    <option value="0">无</option>
                    <option value="1">盈</option>
                </select>
            </div>
            <button class="layui-btn layui-btn-primary" id="btn-set" type="button" style="padding:0px; margin-left: -4px; width: 30px;">
                <i class="layui-icon layui-icon-set-fill"></i>
            </button>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>结果</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="profit_result" id="profit_result" class="layui-input">
                    <option value="-2">所有</option>
                    <option value="-1">亏</option>
                    <option value="0">无</option>
                    <option value="1">盈</option>
                </select>
            </div>
        </div>
        <div class="layui-btn-group">
            <button class="layui-btn layui-btn-primary" id="spread" type="button" style="padding:0px; width: 30px;"><i class="layui-icon layui-icon-down"></i></button>
            <button class="layui-btn" id="btn-search" lay-submit lay-filter="btn-search"><i class="layui-icon layui-icon-search"></i></button>
        </div>
        
    </div>

    <div class="layui-item hidden" id="more">
        <div class="layui-inline" style="margin-left: 10px;">
            <label >开始日期：</label>
            <div class="layui-input-inline" style="width:170px;">
                <input type="text" class="layui-input" id="start_time" value="" name="start_time">
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label >结束日期：</label>
            <div class="layui-input-inline" style="width:170px;">
                <input type="text" class="layui-input" id="end_time" value="" name="end_time">
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>用户账号</label>
            <div class="layui-input-inline">
                <input type="text" name="account" placeholder="请输入手机号或邮箱" autocomplete="off" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>真实姓名</label>
            <div class="layui-input-inline">
                <input type="text" name="name" placeholder="请输入真实姓名" autocomplete="off" class="layui-input" value="">
            </div>
        </div>
    </div>
</div>

<table id="order_list" lay-filter="order_list"></table>
@endsection

@section('scripts')
<script type="text/html" id="barDemo">
    @{{d.status==1 ? '<a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="edit">编辑</a>' : '' }}
</script>

<script type="text/html" id="type_name">
    @{{# if (d.type == 1) { }}
        <div class="order_type order_type_rise">
            <span style="font-size: 13px; font-weight: bold;">@{{d.type_name}}</span><span style="font-size: 18px; font-weight: bold;">↑</span> 
        </div>
    @{{# } else { }}
        <div class="order_type order_type_fall">
            <span style="font-size: 13px; font-weight: bold;">@{{d.type_name}}</span><span style="font-size: 18px; font-weight: bold;">↓</span>
        </div>
    @{{# } }}
</script>

<script type="text/html" id="pre_profit_result_name">
    @{{# if (d.pre_profit_result == 1) { }}
        <span style="color:#89deb3;">@{{d.pre_profit_result_name}}</span>
    @{{# } else if (d.pre_profit_result == -1) { }}
        <span style="color:#d67a7a;">@{{d.pre_profit_result_name}}</span>
    @{{# } else { }}
        <span class="layui-badge-rim">@{{d.pre_profit_result_name}}</span>
    @{{# } }}
</script>

<script type="text/html" id="profit_result_name">
    <span class="layui-badge @{{d.profit_result == 1 ? 'layui-bg-green' : ''}}">@{{d.profit_result_name}}</span>
</script>

<script type="text/html" id="status_name">
    <span class="layui-badge @{{d.status == 1 ? '' : 'layui-bg-gray'}}">@{{d.status_name}}</span>
</script>

<script type="text/html" id="symbol_name">
    <span>@{{d.symbol_name}}</span><span style="color: #848484dd; font-size: 10px;" title="收益率:@{{d.profit_ratio}}%">-@{{d.seconds}}S</span>
</script>

<script type="text/html" id="seconds">
    <div>
        <span title="收益率:@{{d.profit_ratio}}%">@{{d.seconds}}</span>
    </div>
</script>

<script type="text/html" id="fact_profits">
    <div style="text-align: right; margin-right: 10px;">
    @{{# if (d.profit_result == 1) { }}
        <span style="color: #f00; font-weight: bolder;">@{{Number(d.fact_profits).toFixed(0)}}</span>
    @{{# } else { }}
        <span>@{{Number(d.fact_profits).toFixed(0)}}</span>
    @{{# } }}
    </div>
</script>

<script>
    window.onload = function() {
        document.onkeydown = function(event) {
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if (e && e.keyCode == 13) { // enter 键
                $('#btn-search').click();
            }
        };
        layui.use(['element', 'form', 'layer', 'table', 'laydate'], function() {
            var element = layui.element;
            var layer = layui.layer;
            var table = layui.table;
            var $ = layui.$;
            var form = layui.form;
            var laydate = layui.laydate;
            laydate.render({
                elem: '#start_time',
                type:'datetime'
            });
            laydate.render({
                elem: '#end_time',
                type:'datetime'
            });
            form.on('submit(btn-search)', function (data) {
                var option = {
                    where: data.field,
                    page: {curr: 1}
                }
                data_table.reload(option);
                return false;
            });
            $('#spread').click(function () {
                var icon = $(this).find('i');
                var order_list_height = $('div[lay-id=order_list]').height()
                var origin_height = $('div[lay-id=order_list] div.layui-table-box').height()
                if (icon.hasClass('layui-icon-up')) {
                    $('div[lay-id=order_list]').height(order_list_height + 30)
                    $('div[lay-id=order_list] div.layui-table-box').height(origin_height + 30)
                    icon.removeClass('layui-icon-up')
                    icon.addClass('layui-icon-down')
                } else {
                    $('div[lay-id=order_list]').height(order_list_height - 30)
                    $('div[lay-id=order_list] div.layui-table-box').height(origin_height - 30)
                    icon.removeClass('layui-icon-down')
                    icon.addClass('layui-icon-up')
                }
                $('#more').toggle();
            });

            var data_table =   table.render({
                elem: '#order_list',
                url: "/admin/micro_order_list",
                done: function(res, curr, count) {
                    $('tr:has(div.order_type_rise)').css('backgroundColor', '#f4fdf8');
                    $('tr:has(div.order_type_fall)').css('backgroundColor', '#fff8f8');
                },
                page: true,
                limit: 100,
                limits: [20, 50, 100, 500, 1000],
                toolbar: true,
                height: 'full-100',
                totalRow: true,
                cols: [[
                    {field: '', type: 'checkbox', width: 60}
                    ,{field: '', title: '序号', type: "numbers", width: 90}
                    ,{field: 'id', title: 'ID', width: 100}
                    ,{field: 'account', title: '用户账号', width: 130, sort: true, totalRowText: '小计'}
                    ,{field: 'real_name', title: '真实姓名', width: 100}
                    ,{field: 'symbol_name', title: '合约', width: 140, sort: true, templet: '#symbol_name'}
                    ,{field: 'currency_name', title: '币种', width: 80, sort: true}
                    ,{field: 'type_name', title: '类型', width: 80, templet: '#type_name', sort: true}
                    ,{field: 'seconds', title: '秒数', width: 80, templet: '#seconds', sort: true, hide: true}
                    ,{field: 'status_name', title: '交易状态', width: 100, sort: true, templet: '#status_name'}
                    ,{field: 'number', title: '数量', width: 90, templet: '<div><div style="text-align: right;">@{{Number.parseInt(d.number)}}</div></div>', totalRow: true}
                    ,{field: 'fee', title: '手续费', width: 100, totalRow: true, templet: '<div><div style="text-align: right;"><span>@{{Number(d.fee).toFixed(2)}}</span></div></div>'}
                    ,{field: 'pre_profit_result_name', title: '预设', width: 90, sort: true, templet: '#pre_profit_result_name', hide: false}
                    ,{field: 'profit_result_name', title: '结果', width: 90, sort: true, templet: '#profit_result_name', hide: false}
                    ,{field: 'fact_profits', title: '盈利', width: 100, sort: true, totalRow: true, templet: '#fact_profits'}
                    ,{field: 'open_price', title: '开仓价', width: 100, templet: '<div><div style="text-align: right;"><span>@{{Number(d.open_price).toFixed(4)}}</span></div></div>'}
                    ,{field: 'end_price', title: '平仓价', width: 100, templet: '<div><div style="text-align: right;"><span>@{{Number(d.end_price).toFixed(4)}}</span></div></div>'}
                    ,{field: 'created_at', title: '下单时间', width: 170, sort: true}
                    ,{field: 'updated_at', title: '更新日期', width: 170, sort: true, hide: true}
                    ,{field: 'handled_at', title: '平仓时间', width: 170, sort: true, hide: true}
                    ,{field: 'complete_at', title: '完成时间', width: 170, sort: true, hide: true}
                    //,{fixed: 'right', title: '操作', width: 100, align: 'center', toolbar: '#barDemo'}
                ]]
            });

            //监听工具条
            table.on('tool(order_list)', function(obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                if (layEvent === 'edit') { //编辑
                    layer_show('编辑交易', '/admin/micro_order_edit?id=' + data.id);
                }
            });

            table.on('checkbox(order_list)', function(obj) {
                // 选择事件
                console.log(obj)
            });

            $('#btn-set').click(function () {
                var checkStatus = table.checkStatus('order_list');
                var pre_profit_result = $('#pre_profit_result').val();
                var ids = [];
                try {
                    if (checkStatus.data.length <= 0) {
                        throw '请先选择交易';
                    }
                    if (pre_profit_result <= -2) {
                        throw '请选择交易的预处理风控类型';
                    }
                    checkStatus.data.forEach(function (item, index, arr) {
                        ids.push(item.id);
                    });
                    $.ajax({
                        url: '/admin/micro/batch_risk'
                        ,type: 'POST'
                        ,data: {risk: pre_profit_result, ids: ids}
                        ,success: function (res) {
                            layer.msg(res.message, {
                                time: 2000,
                                end: function () {
                                    if (res.type == 'ok') {
                                        data_table.reload();
                                    }
                                }
                            });
                        }
                        ,error: function (res) {
                            layer.msg('网络错误');
                        }
                    })
                    
                } catch (error) {
                    layer.msg(error);
                }
            });
        });
    }
</script>
@endsection