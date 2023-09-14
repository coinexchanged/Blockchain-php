@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    
    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_position">添加广告位</button>
    
        <table id="userlist" lay-filter="userlist"></table>

        <script type="text/html" id="barDemo">
            
           
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="edit">编辑</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
        </script>
      
        <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_show" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_show == 1 ? 'checked' : '' }}>
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

                        $('#add_position').click(function(){layer_show('添加广告位', '/admin/ad/position_edit');});
                        
                        function tbRend(url) {
                            table.render({
                                elem: '#userlist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    { field: 'id', title: 'ID', width: 100}
                                    
                                    , {field:'name',title:'名称', width:150}
                                    , {field:'describe',title:'描述', width:150}
                                    
                                    , {field:'create_time',title:'添加时间', width:200}

                                    
                                    ,{field:'is_show', title:'是否显示', width:90, templet: '#switchTpl'}
                                   
                                    , {fixed: 'right', title: '操作', width: 280, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                        tbRend("{{url('/admin/ad/position_list')}}");

                        //监听是否显示操作
                        form.on('switch(sexDemo)', function(obj){
                            var id = this.value;
                            
                            $.ajax({
                                url:'{{url('admin/ad/position_show')}}',
                                type:'post',
                                dataType:'json',
                                data:{id:id},
                                success:function (res) {
                                    layer.msg(res.message);
                                   
                                }
                            });
                        });

                        //监听工具条
                        table.on('tool(userlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;
                            if (layEvent === 'delete') { //删除
                                layer.confirm('真的要删除吗？', function (index) {
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url: "{{url('admin/ad/position_del')}}",
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
                                layer_show('编辑广告位','{{url('admin/ad/position_edit')}}?id='+data.id);
                            }
                        });
                    });
                }
            </script>

@endsection