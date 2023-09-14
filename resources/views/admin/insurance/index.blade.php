@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_set">添加险种</button>


    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

@endsection

@section('scripts')
    <script type="text/html" id="status">
        <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" @{{ d.status == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="auto_claim">
        <input type="checkbox" name="auto_claim" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="auto_claim" @{{ d.auto_claim == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="is_t_add_1_t">
        <input type="checkbox" name="is_t_add_1" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_t_add_1" @{{ d.is_t_add_1 == 1 ? 'checked' : '' }}>
    </script>
    <script>

        layui.use(['table','form'], function() {
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例

            $('#add_set').click(function() {
                layer_show('添加险种', '/admin/insurance/add', 800, 600);
            });

            table.render({
                elem: '#demo'
                ,url: '{{url('admin/insurance/lists')}}' //数据接口
                ,page: true //开启分页
                ,height: 'full-100'
                ,id: 'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'name', title: '名称', width: 100}
                    ,{field: 'currency_name', title: '币种', width: 100}
                    ,{field: 'type_name', title: '类型', width: 100}
                    ,{field: 'auto_claim', title: '自动赔付', minWidth: 100,templet:'#auto_claim'}
                    ,{field: 'status', title: '状态', minWidth: 100, templet:'#status'}
                    ,{field: 'is_t_add_1', title: 'T+1生效', minWidth: 100, templet:'#is_t_add_1_t'}
                    ,{title:'操作', minWidth:100, toolbar: '#barDemo'}
                ]]
            });
            form.on('switch(status)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/insurance/change_status')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                        }
                    }
                });
            });
            form.on('switch(auto_claim)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/insurance/change_auto_claim')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                        }
                    }
                });
            });
            form.on('switch(is_t_add_1)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/insurance/change_t_add_1')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                        }
                    }
                });
            });

            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么?可能会导致系统崩溃！', function(index){
                        $.ajax({
                            url:'{{url('admin/insurance/del')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });


                    });
                } else if(obj.event === 'edit'){
                    layer_show('编辑险种','{{url('admin/insurance/add')}}?id='+data.id, 800, 600);
                }
            });


        });
    </script>

@endsection