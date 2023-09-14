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
        <div class="layui-inline">
            <label class="layui-form-label">币种</label>
            <div class="layui-input-inline" style="width:100px;">
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
            <div class="layui-input-inline" style="width:100px;">
                <select name="type" lay-verify="required">
                    <option value="-1">全部</option>
                    <option value="1">买入(做多)</option>
                    <option value="2">卖出(做空)</option>
                </select>
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">风险率</label>
            <div class="layui-input-inline" style="width:90px; margin-right: 0px">
                <select name="operate" lay-verify="required">
                    <option value="-1">全部</option>
                    <option value="1">&gt;=</option>
                    <option value="2">&lt;=</option>
                </select>
            </div>
            <div class="layui-input-inline" style="width:80px; margin-right: 0px">
                <input type="text" class="layui-input" name="hazard_rate" placeholder="输入数值">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
        <div class="layui-inline">
            <button class="layui-btn" lay-submit lay-filter="submit">查询</button>
        </div>
    </div>
</div>
<table id="data_table" lay-filter="data_table"></table>
@endsection
@section('scripts')
<script>
    layui.use(['table', 'layer', 'form'], function() {
        var table = layui.table
            ,layer = layui.layer
            ,form = layui.form
            ,$ = layui.$
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/hazard/lists'
            ,height: 'full'
            ,toolbar: true
            ,page: true
            ,totalRow: true
            ,cols: [[
                {type: 'checkbox'}
                ,{field: 'id', title: 'id', width: 80, totalRowText: '小计:'}
                ,{field: 'type_name', title: '方式', width: 150}
                ,{field: 'mobile', title: '电话', width: 120}
                ,{field: 'account_number', title: '交易账号', width: 150}
                ,{field: 'symbol', title: '交易账号', width: 150}
                ,{field: 'price', title: '价格', width: 150}
                ,{field: 'share', title: '手数', width: 90}
                ,{field: 'multiple', title: '倍数', width: 90}
                ,{field: 'caution_money', title: '保证金', width: 130}
                ,{field: 'profits', title: '盈亏', width: 150, sort: true, totalRow: true, templet: '<div><p class=""><span>@{{ Number(d.profits).toFixed(4) }}</span></p></div>'}
                ,{field: 'profits_total', title: '盈亏总额', width: 150, sort: true, totalRow: true, templet: '<div><p class=""><span>@{{ Number(d.profits_total).toFixed(4) }}</span></p></div>'}
                ,{field: 'caution_money_total', title: '保证金总额', width: 150, sort: true, totalRow: true, templet: '<div><p class=""><span>@{{ Number(d.caution_money_total).toFixed(4) }}</span></p></div>'}
                ,{field: 'hazard_rate', title: '风险率', width: 150, sort: true, templet: '<div><p class="number"><span>@{{ d.hazard_rate }}</span><span>%</span></p></div>'}
                //,{field: '', title: '爆仓价', width: 120}
                //,{fixed: 'right', title: '操作', width: 120}
            ]]
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