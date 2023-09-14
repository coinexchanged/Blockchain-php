@extends('admin._layoutNew')
@section('page_head')

@stop

@section('page-content')

    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_admin">添加管理员</button>

    <table class="layui-hide" id="adminUsers" lay-filter="adminList"></table>


    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">修改</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
@stop
@section('scripts')
    <script type="text/javascript">
        window.onload = function () {
            layui.use(['layer', 'table'], function () { //独立版的layer无需执行这一句
                var $ = layui.jquery;
                var layer = layui.layer; //独立版的layer无需执行这一句
                var table = layui.table;
                var form = layui.form;
                $('#add_admin').click(function(){layer_show('添加管理员', '/admin/manager/add');});
                table.render({
                    elem: '#adminUsers',
                    url: '/admin/manager/users',
                    page: false,
                    cols: [[
                        {field: 'id', title: 'ID', minWidth: 100, sort: true},
                        {field: 'username', title: '用户名', minWidth: 150},
                        {field: 'role_name', title: '角色', minWidth: 150},
                        {fixed: 'right', title: '操作', minWidth: 150, align: 'center', toolbar: '#barDemo'}
                    ]]
                });



                //监听工具条
                table.on('tool(adminList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data; //获得当前行数据
                    var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                    var tr = obj.tr; //获得当前行 tr 的DOM对象

                    if(layEvent === 'del'){ //删除
                        layer.confirm('真的要删除吗？', function(index){
                            //向服务端发送删除指令
                            $.ajax({
                                url:'/admin/manager/delete',
                                type:'post',
                                dataType:'json',
                                data:{id:data.id},
                                success:function(res){
                                    if(res.type=='ok'){
                                        obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                        layer.msg(res.message);
                                        layer.close(index);
                                    }else{
                                        layer.close(index);
                                        layer.alert(res.message);
                                    }
                                }
                            });
                        });
                    } else if(layEvent === 'edit'){ //编辑
                        //do something
                            layer_show('修改管理员', '/admin/manager/add?id=' + data.id);

                    }
                });


            });


        }

    </script>

@stop