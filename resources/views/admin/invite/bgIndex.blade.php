@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div class="layui-inline btn-group layui-btn-group">
          
           <button class="layui-btn layui-btn-primary" id="addbg">添加背景图</button>
    </div>
   
    <div class="layui-form">
        <table id="accountlist" lay-filter="accountlist"></table>
        <script type="text/html" id="barDemo">
            <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
            <a class="layui-btn layui-btn-xs" lay-event="del">删除</a>
        </script>
        <script type="text/html" id="FcardTpl">
        <img src="@{{ d.pic }}" width="100" height="100">
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

                        $('#addbg').click(function(){layer_show('添加背景图', '/admin/invite/edit');});
                       
                        function tbRend(url) {
                            table.render({
                                elem: '#accountlist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    { field: 'id', title: 'ID',  minWidth: 100}
                                    , {field:'pic',title: '背景图',minWidth: 200,templet:'#FcardTpl'}
                                    
                                    , {field:'create_time',title:'创建时间', minWidth:300}
                                  ,{fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                        
                        tbRend("{{url('/admin/invite/bg_list')}}");
                        
                        //监听工具条
                        table.on('tool(accountlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;

                            if (layEvent === 'del') { //删除
                                layer.confirm('真的要删除吗？', function (index) {
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url: "{{url('admin/invite/bgdel')}}",
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
                            }else if (layEvent === 'edit'){ //编辑
                                layer_show('编辑背景图','{{url('admin/invite/edit')}}?id='+data.id);
                            }
                        });
                    });
                }
            </script>

@endsection