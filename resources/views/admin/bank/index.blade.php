@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加收款方式','{{url('admin/bank/add')}}')">添加收款方式</button>

        {{--<form class="layui-form layui-form-pane layui-inline" action="">--}}
            {{--<div class="layui-inline" style="margin-left: 50px;">--}}
                {{--<label class="layui-form-label">用户名</label>--}}
                {{--<div class="layui-input-inline">--}}
                    {{--<input type="text" name="account_number" autocomplete="off" class="layui-input">--}}
                {{--</div>--}}
            {{--</div>--}}
            {{--<div class="layui-inline">--}}
                {{--<div class="layui-input-inline">--}}
                    {{--<button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>--}}
                {{--</div>--}}
            {{--</div>--}}



        {{--</form>--}}
    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_display" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_display == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        {{--<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">调节账户</a>--}}
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
<script type="text/html" id="logo">
    <img src="@{{d.logo}}" >
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
                ,url: '{{url('admin/bank/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', minWidth:80, sort: true}
                    ,{field: 'name', title: '支付方式', minWidth:80}
                    ,{field: 'logo', title: '图标', minWidth:80,templet: '#logo'}
                    // ,{field: 'sort', title: '排序', minWidth:80}
                    ,{field: 'sort', title: '排序', minWidth:80}
                    // ,{field: 'create_time', title: '添加时间', minWidth:80}
                    // ,{field: 'wechat_nickname', title: '微信昵称', minWidth:80}
                    // ,{field: 'wechat_account', title: '微信账号', minWidth:80}
                    // ,{field: 'ali_nickname', title: '支付宝昵称', minWidth:80}
                    // ,{field: 'ali_account', title: '支付宝账号', minWidth:80}
                    // ,{field: 'bank_name', title: '银行名称', minWidth:80}
                    // ,{field:'is_display', title:'是否显示', minWidth:85, templet: '#switchTpl', unresize: true}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}

                ]]
            });
            //监听热卖操作
            form.on('switch(sexDemo)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/currency_display')}}',
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
                            url:'{{url('admin/bank/del')}}',
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
                    layer_show('编辑币种','{{url('admin/bank/add')}}?id='+data.id);
                } else if(obj.event === 'detail'){
                    layer_show('调节账户','{{url('admin/adjust_account')}}?id='+data.id,800,600);
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