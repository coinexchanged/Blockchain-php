@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加商家','{{url('admin/legalstore/add')}}')">添加商家</button>


    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.status == 1 ? 'checked' : '' }}>
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
                ,url: '{{url('admin/legalstore/list_data')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', Width:50, sort: true}
                    ,{field: 'name', title: '商家名称', Width:150}
                    ,{field: 'bank_name', title: '开户行', minWidth:150}
                    ,{field: 'bank_user', title: '开户人', minWidth:50}
                    ,{field: 'bank_account', title: '银行账号', minWidth:50}
                    ,{field: 'min_num', title: '最小充值数量', minWidth:80}
                    ,{field: 'max_num', title: '最大充值数量', minWidth:80}
                    ,{field: 'min_num_wid', title: '最小提现数量', minWidth:80}
                    ,{field: 'max_num_wid', title: '最大提现数量', minWidth:80}
                    ,{title:'操作',toolbar: '#barDemo'}

                ]]
            });
            //监听热卖操作
            form.on('switch(sexDemo)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/robot/start')}}',
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

            table.on('tool(test)', function(obj) {
                var data = obj.data;
                if (obj.event === 'del') {
                    layer.confirm('真的删除行么', function (index) {
                        $.ajax({
                            url: '{{url('admin/robot/delete')}}',
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.type == 'error') {
                                    layer.msg(res.message);
                                } else {
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });


                    });
                } else if (obj.event === 'edit') {
                    layer_show('编辑商家', '{{url('admin/legalstore/add')}}?id=' + data.id);
                } else if (obj.event == 'execute') {
                    layer.confirm('确定执行上币脚本？', function (index) {
                        $.ajax({
                            url: '{{url('admin/currency_execute')}}',
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                layer.msg(res.message);
                            }
                        });
                    });
                } else if (obj.event == 'open')
                {
                    layer_show('预设的行情拐点', '{{url('admin/robot/sche')}}?rid=' + data.id);
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
