@extends('admin._layoutNew')
@section('page-head')
<style>
    li[hidden] {
        display: none;
    }
</style>
@stop
@section('page-content')
<div class="layui-form">
    <div class="layui-item">
        <div class="layui-inline" style="margin-left: 2px;">
            <label>风控类型</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="risk" lay-verify="required">
                    <option value="-2">全部</option>
                    <option value="0">无</option>
                    <option value="-1">亏损</option>
                    <option value="1">盈利</option>
                </select>
            </div>
        </div>
        <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="search"> <i class="layui-icon">&#xe615;</i> </button>
        <button class="layui-btn layui-btn-normal" type="button" id="risk_setup">设置</button>
    </div>
</div>
<table id="data_table" lay-filter="data_table"></table>
@stop
@section('scripts')
<script type="text/javascript">
    layui.use(['table', 'layer', 'form'], function() {
        var table = layui.table
            ,layer = layui.layer
            ,form = layui.form
            ,$ = layui.$
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/currency/micro_match_list/'
            ,height: 'full-80'
            ,toolbar: true
            ,page: true
            ,cols: [[
                {type: 'checkbox'}
                ,{field: 'id', title: 'id', width: 70}
                ,{field: 'legal_name', title: '法币', width: 80}
                ,{field: 'currency_name', title: '交易币', width: 80}
                ,{field: 'risk_group_result_name', title: '风控结果', width: 100}
                ,{field: 'create_time', title: '创建时间', width: 180}
            ]]
        });
        form.on('submit(search)', function(data){
            data_table.reload({
                where: data.field
            });
            return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
        });
        $('#risk_setup').click(function () {
            var checkStatus = table.checkStatus('data_table');
            var risk = $('select[name=risk]').val();
            try {
                if (checkStatus.data.length <= 0) {
                    throw '请先选择交易对再操作';
                }
                if (risk == -2) {
                    throw '请选择风控类型';
                }
                var ids = [];
                checkStatus.data.forEach(function (item, index, arr) {
                    ids.push(item.id);
                });
                // console.log(ids);
                $.ajax({
                    url: '/admin/currency/micro_risk'
                    ,type: 'POST'
                    ,data: {ids: ids, risk: risk}
                    ,success: function (res) {
                        layer.msg(res.message, {
                            time: 2000
                            ,end: function () {
                                data_table.reload();
                            }
                        });
                    }
                    ,error: function (res) {
                        layer.msg('网络错误');
                    }
                });
            } catch (error) {
                layer.msg(error)
            }
        });
    });
</script>
@stop