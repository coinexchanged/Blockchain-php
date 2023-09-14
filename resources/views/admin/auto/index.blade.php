@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加机器人','{{url('admin/auto_add')}}')">添加机器人</button>


    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_start" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_start == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="legal">
        @{{d.is_legal==1 ? '<span >'+'是'+'</span>' : '' }}
        @{{d.is_legal==0 ? '<span >'+'否'+'</span>' : '' }}

    </script>
    <script type="text/html" id="lever">
        @{{d.is_lever==1 ? '<span >'+'是'+'</span>' : '' }}
        @{{d.is_lever==0 ? '<span >'+'否'+'</span>' : '' }}

    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">

        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
        {{--<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="execute">执行上币脚本</a>--}}
    </script>

@endsection

@section('scripts')
    <script>

        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('admin/auto_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', Width:60, sort: true}
                    ,{field: 'sell_account', title: '卖家', Width:80}
                    ,{field: 'buy_account', title: '买家', minWidth:80}
                    ,{field: 'currency_name', title: '交易币', minWidth:80}
                    ,{field: 'legal_name', title: '法币', minWidth:80}
                    ,{field: 'min_price', title: '最低区间', minWidth:80}
                    ,{field: 'max_price', title: '最高区间', minWidth:80}
                    ,{field: 'min_number', title: '随机最小数量', minWidth:80}
                    ,{field: 'max_number', title: '随机最大数量', minWidth:80}
                    ,{field: 'need_second', title: '频率(秒)', minWidth:80}
                    ,{field: 'create_time', title: '添加时间', minWidth:80}
                    // ,{field: 'type', title: '基于BTC/ETH', minWidth:80, templet: '#typetml'}
                    // ,{field: 'is_legal', title: '是否法币', minWidth:80, templet: '#legal'}
                    // ,{field: 'is_lever', title: '是否杠杆币', minWidth:80, templet: '#level'}

                    ,{field:'is_start', title:'是否开启', minWidth:85, templet: '#switchTpl', unresize: true}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}

                ]]
            });
            //监听热卖操作
            form.on('switch(sexDemo)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/auto_start')}}',
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
                            url:'{{url('admin/currency_del')}}',
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
                    layer_show('编辑机器人','{{url('admin/auto_add')}}?id='+data.id);
                } else if (obj.event == 'execute'){
                    layer.confirm('确定执行上币脚本？', function(index){
                        $.ajax({
                            url:'{{url('admin/currency_execute')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                layer.msg(res.message);
                            }
                        });
                    });
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.account_number;
                table.reload('mobileSearch',{
                    where:{account_number:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

@endsection