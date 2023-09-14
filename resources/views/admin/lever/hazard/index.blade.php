@extends('admin._layoutNew')

@section('page-head')
<style>
    .number {
        text-align: right;
        margin-right: 10px;
    }
    .layui-form-label {
        width: unset;
    }
</style>
@endsection

@section('page-content')
<div class="layui-form">
    <div class="layui-form-item">
        <input name="user_id" type="hidden" value="{{Request::get('user_id')}}">
        <div class="layui-inline">
            <label class="layui-form-label">法币</label>
            <div class="layui-input-inline" style="width:90px;">
                <select name="legal_id" lay-verify="required">
                    <option value="-1">无</option>
                    @foreach ($currencies as $key => $currency)
                        <option value="{{$currency->id}}">{{$currency->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">方向</label>
            <div class="layui-input-inline" style="width:120px;">
                <select name="type" lay-verify="required">
                    <option value="-1">全部</option>
                    <option value="1">买入(做多)</option>
                    <option value="2">卖出(做空)</option>
                </select>
            </div>
        </div>
        <!--
        <div class="layui-inline">
            <label class="layui-form-label">风险率</label>
            <div class="layui-input-inline" style="width:90px; margin-right: 0px">
                <select name="operate" lay-verify="required">
                    <option value="-1">范围</option>
                    <option value="1">&gt;=</option>
                    <option value="2">&lt;=</option>
                </select>
            </div>
            <div class="layui-input-inline" style="width:80px; margin-right: 0px">
                <input type="text" class="layui-input" name="hazard_rate" placeholder="输入数值">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
        -->
        <div class="layui-inline">
            <button class="layui-btn" lay-submit lay-filter="submit">查询</button>
        </div>
    </div>
</div>
<table id="data_table" lay-filter="data_table"></table>
@endsection
@section('scripts')
<script id="operate_bar" type="text/html">
    <button class="layui-btn layui-btn-danger layui-btn-xs" lay-event="handle">处理交易</button>
</script>
<script>
    layui.use(['table', 'layer', 'form'], function() {
        
        var table = layui.table
            ,layer = layui.layer
            ,form = layui.form
            ,$ = layui.$
        var user_id = $('input[name=user_id]').val();
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/hazard/lists'
            ,height: 'full'
            ,toolbar: true
            ,page: true
            ,totalRow: true
            ,cols: [[
                {field: 'id', title: 'id', width: 80, totalRowText: '小计:'}
                ,{field: 'symbol', title: '交易对', width: 110}
                ,{field: 'type_name', title: '方式', width: 80}
                ,{field: 'account_number', title: '交易账号', width: 130}
                ,{field: 'price', title: '开仓价格', width: 130, templet: '<div><p class="number"><span>@{{ Number(d.price).toFixed(6) }}</span></p></div>'}
                ,{field: 'update_price', title: '当前价格', width: 130, templet: '<div><p class="number"><span>@{{ Number(d.update_price).toFixed(6) }}</span></p></div>'}
                ,{field: 'share', title: '手数', width: 90, hide: true}
                ,{field: 'multiple', title: '倍数', width: 90}
                ,{field: 'number', title: '数量', width: 90}
                ,{field: 'caution_money', title: '保证金', width: 130, totalRow: true}
                ,{field: 'profits', title: '盈亏', width: 150, sort: true, totalRow: true, templet: '<div><p class=""><span>@{{ Number(d.profits).toFixed(4) }}</span></p></div>'}
                ,{fixed: 'right', title: '操作', toolbar: '#operate_bar'}
                //,{field: 'profits_total', title: '盈亏总额', width: 150, sort: true, totalRow: true, templet: '<div><p class=""><span>@{{ Number(d.profits_total).toFixed(4) }}</span></p></div>'}
                //,{field: 'caution_money_total', title: '保证金总额', width: 150, sort: true, totalRow: true, templet: '<div><p class=""><span>@{{ Number(d.caution_money_total).toFixed(4) }}</span></p></div>'}
                //,{field: 'hazard_rate', title: '风险率', width: 150, sort: true, templet: '<div><p class="number"><span>@{{ d.hazard_rate }}</span><span>%</span></p></div>'}
                //,{field: '', title: '爆仓价', width: 120}
                //,{fixed: 'right', title: '操作', width: 120}
            ]]
            ,where : {
                user_id: user_id
            }
        });
        table.on('tool(data_table)', function (obj) {
            var layEvent = obj.event
                ,data = obj.data
            if (layEvent == 'handle') {
                layer.open({
                    type: 2
                    ,title: '交易处理'
                    ,content: '/admin/hazard/handle' + '?id=' + data.id
                    ,area: ['380px', '220px']
                });
            }
        });
        form.on('submit(submit)', function (data) {
            var option = {
                where: data.field
            }
            data_table.reload(option);
        });
    });
</script>
@endsection