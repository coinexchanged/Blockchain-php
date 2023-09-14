@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_set">添加设置</button>


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
    <script>

        layui.use(['table','form'], function() {
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例

            $('#add_set').click(function() {
                layer_show('添加规则', '/admin/insurance_rules_add', 500, 380);
            });

            table.render({
                elem: '#demo'
                ,url: '{{url('admin/insurance_rules_list')}}' //数据接口
                ,page: true //开启分页
                ,height: 'full-100'
                ,id: 'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'insurance_name', title: '险种', width: 80}
                    ,{field: 'amount', title: '金额', minWidth: 80}
                    ,{field: 'place_an_order_max', title: '单笔最大金额限', minWidth: 80}
                    ,{field: 'existing_number', title: '持仓笔数', minWidth: 80}
                    ,{title:'操作', minWidth:100, toolbar: '#barDemo'}
                ]]
            });


            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'{{url('admin/insurance_rules_del')}}',
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
                    layer_show('编辑规则','{{url('admin/insurance_rules_add')}}?id='+data.id, 500, 380);
                }
            });


        });
    </script>

@endsection