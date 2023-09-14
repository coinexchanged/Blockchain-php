@extends('admin._layoutNew')
@section('page_head')

@stop

@section('page-content')

   

    <table class="layui-hide" id="adminUsers" lay-filter="adminList"></table>


    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" @{{ d.status == 1 ? 'checked' : '' }}>
    </script>
  <!--   <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">修改</a>
        <a class="layui-btn layui-btn-xs" lay-event="detail">明细</a>
    </script> -->
@stop
@section('scripts')
    <script type="text/javascript">
        window.onload = function () {
            layui.use(['layer', 'table'], function () { //独立版的layer无需执行这一句
                var $ = layui.jquery;
                var layer = layui.layer; //独立版的layer无需执行这一句
                var table = layui.table;
                var form = layui.form;
                var id="{{$id}}";
                table.render({
                    elem: '#adminUsers',
                    url: '/admin/crowd/detail_lists?id='+id,
                    page: true,
                    cols: [[
                        {field: 'id', title: 'ID', minWidth: 100, sort: true},
                        {field: 'nickname', title: '用户名', minWidth: 150},
                        {field: 'attend_qty', title: '数量', minWidth: 150},
                        {field: 'hold_qty', title: '占有数量', minWidth: 150},
                        {field: 'price', title: '价格', minWidth: 150},
                        {field: 'should_money', title: '应付金额', minWidth: 150},
                        {field: 'pay_money', title: '实付金额', minWidth: 150},
                        {field: 'price', title: '对应余额', minWidth: 150},
                        {field: 'created_time', title: '开始时间', minWidth: 150},
                        {field: 'handle_time', title: '处理时间', minWidth: 150},
                       {field: 'status', title: '状态', minWidth: 150},
                   
                    ]]
                });

                form.on('switch(status)', function(obj){
                    var id = obj.value;
                    $.ajax({
                        url:'{{url('/admin/crowd/status')}}',
                        type:'post',
                        dataType:'json',
                        data:{id:id},
                        success:function(res){
                            layer.msg(res.message);
                        }
                    });
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
                            layer_show('修改设置', '/admin/crowd/add?id=' + data.id);
                    }else if(layEvent === 'detail'){ //编辑
                        //do something
                            layer_show('众筹明细', '/admin/crowd/detail?id=' + data.id);
                    }
                });


            });


        }

    </script>

@stop