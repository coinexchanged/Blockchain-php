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
        <div class="layui-inline btn-group layui-btn-group">
            <button class="layui-btn layui-btn-primary cateManage">代数奖励设置</button>
        </div>
        <div class="layui-inline btn-group layui-btn-group">
            <button class="layui-btn layui-btn-primary cateManage1">升级设置</button>
        </div>
    <div class="layui-item " id="more">
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
        <button class="layui-btn" id="btn-search" lay-submit lay-filter="btn-search"><i class="layui-icon layui-icon-search"></i></button>
    </div>
</div>

<table id="order_list" lay-filter="order_list"></table>
@endsection

    @section('scripts')
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



            var showCateManage = function() {
                var index = layer.open({
                    title:'新闻分类管理'
                    ,type:2
                    ,content: '/admin/level_algebra_index'
                    ,area: ['800px', '600px']
                    ,maxmin: true
                    ,anim: 3
                    ,end : function() {
                        //弹窗关闭后回调，刷新主窗口的分类下拉列表
                        $.get('/admin/level_list/', function(returnData) {
                            form.render();
                        });
                    }
                });
                layer.full(index);
            };

            $('.cateManage').click(showCateManage);



            var showCateManage1 = function() {
                var index = layer.open({
                    title:'新闻分类管理'
                    ,type:2
                    ,content: '/admin/level_index'
                    ,area: ['800px', '600px']
                    ,maxmin: true
                    ,anim: 3
                    ,end : function() {
                        //弹窗关闭后回调，刷新主窗口的分类下拉列表
                        $.get('/admin/level_list/', function(returnData) {
                            form.render();
                        });
                    }
                });
                layer.full(index);
            };

            $('.cateManage1').click(showCateManage1);


            var data_table =   table.render({
                elem: '#order_list',
                url: "/admin/level_order_list",
                page: true,
                limit: 100,
                limits: [20, 50, 100, 500, 1000],
                toolbar: true,
                height: 'full-100',
                totalRow: true,
                cols: [[
                    {field: 'id', title: 'ID', minWidth:80, sort: true}
                    ,{field: 'mobile', title: '手机号账号', minWidth:80}
                    ,{field: 'touch_mobile', title: '触发账号', minWidth:80}
                    ,{field: 'algebra', title: '第几代', minWidth:80}
                    ,{field: 'value', title: '金额', minWidth:80}
                    ,{field: 'info', title: '说明', minWidth:80}
                    ,{field: 'created_at', title: '时间', minWidth:80}

                ]]
            });


        });
    }
</script>
@endsection