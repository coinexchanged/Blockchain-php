@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_admin">添加数据</button>
    <div class="layui-inline">
        <label class="layui-form-label">昨日充值</label>
        <div class="layui-input-inline">
            <input type="text" name="" placeholder="" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" value="{{$yesterday_sum}}" disabled>
        </div>
    </div>
    <div class="layui-inline">
        <label class="layui-form-label">今日充值</label>
        <div class="layui-input-inline">
            <input type="text" name="" placeholder="" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" value="{{$today_sum}}"  disabled>
        </div>
    </div>
    <div class="layui-inline">
        <label class="layui-form-label">总充值</label>
        <div class="layui-input-inline">
            <input type="text" name="" placeholder="" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" value="{{$total_sum}}"  disabled>
        </div>
    </div>
    <div class="layui-form">
        <table id="userlist" lay-filter="userlist"></table>

        <script type="text/html" id="barDemo">
            <a class="layui-btn layui-btn-xs" lay-event="users_wallet">编辑</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
        </script>

@endsection

        @section('scripts')
            <script>
                window.onload = function() {
                    document.onkeydown=function(event){
                        var e = event || window.event || arguments.callee.caller.arguments[0];
                        if(e && e.keyCode==13){ // enter 键
                            $('#mobile_search').click();
                        }
                    };
                    layui.use(['element', 'form', 'layer', 'table'], function () {
                        var element = layui.element;
                        var layer = layui.layer;
                        var table = layui.table;
                        var $ = layui.$;
                        var form = layui.form;
                        form.on('submit(mobile_search)',function(obj){
                            var account_number =  $("input[name='account_number']").val()
                            tbRend("{{url('/admin/user/list')}}?account_number="+account_number);
                            return false;
                        });

                        $('#add_admin').click(function(){layer_show('添加数据', '/admin/user/falsedata_add');});

                        function tbRend(url) {
                            table.render({
                                elem: '#userlist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    { field: 'id', title: 'ID', width: 50}
                                    , {field:'address',title:'地址', width:300}
                                    , {field:'price',title:'金额', width:100}
                                    , {field:'time',title:'时间', width:200}
                                    , {fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                        tbRend("{{url('/admin/user/falsedata')}}");
                        //监听工具条
                        table.on('tool(userlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;
                            if (layEvent === 'delete') { //删除
                                layer.confirm('真的要删除吗？', function (index) {
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url: '{{url('admin/user/falsedata_del')}}',
                                        type: 'post',
                                        dataType: 'json',
                                        data: {id: data.id},
                                        success: function (res) {
                                            if (res.type == 'ok') {
                                                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                                layer.close(index);
                                            } else {
                                                layer.close(index);
                                                layer.alert(res.message);
                                            }
                                        }
                                    });
                                });
                            } else if (layEvent === 'users_wallet') { //编辑
                                var index = layer.open({
                                    title: '钱包列表'
                                    , type: 2
                                    , content: '{{url('/admin/user/falsedata_add')}}?id=' + data.id
                                    , maxmin: true
                                });
                                layer.full(index);
                            }
                        });
                    });
                }
            </script>

@endsection