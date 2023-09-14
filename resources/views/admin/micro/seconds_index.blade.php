@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_set">添加设置</button>


    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        {{--<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">调节账户</a>--}}
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

@endsection

@section('scripts')
    <script type="text/html" id="status">
        <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" @{{ d.status == 1 ? 'checked' : '' }}>
    </script>
    <script>

        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例

            $('#add_set').click(function(){layer_show('添加管理员', '/admin/micro_seconds_add');});

            table.render({
                elem: '#demo'
                ,url: '{{url('admin/micro_seconds_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,height: 'full-100'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', minWidth:80, sort: true}
                    ,{field: 'seconds', title: '秒数', minWidth:80}
                    ,{field: 'status', title: '状态', minWidth:80,templet: '#status'}
                    ,{field: 'profit_ratio', title: '收益率', minWidth:80}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}

                ]]
            });
            //监听是否显示操作
            form.on('switch(status)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/micro_seconds_status')}}',
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
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'{{url('admin/micro_seconds_del')}}',
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
                    layer_show('编辑商家','{{url('admin/micro_seconds_add')}}?id='+data.id);
                }
            });


        });
    </script>

@endsection