@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        {{--<button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加承兑商','{{url('admin/acceptor_add')}}')">添加承兑商</button>--}}

        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline" style="margin-left: 50px;">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>


    
        </form>
        {{--<button class="layui-btn layui-btn-normal" onclick="javascrtpt:window.location.href='{{url('/admin/feedback/csv')}}'">导出用户</button>--}}
    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="detail">查看</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
    <script type="text/html" id="is_reply">
        @{{d.is_reply==1 ? '<span class="layui-badge layui-bg-green">'+'已回复'+'</span>' : '' }}
        @{{d.is_reply==0 ? '<span class="layui-badge layui-bg-red">'+'未回复'+'</span>' : '' }}

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
                ,url: "{{url('admin/candytransfer/list')}}" //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'from_user_phone', title: '转出用户', ninWidth:100}
                    ,{field: 'to_user_phone', title: '转入用户', ninWidth:100}
//                    ,{field: 'content', title: '提交内容', ninWidth:100}

                    ,{field: 'transfer_qty', title: '转账数量', ninWidth:80}
                    ,{field: 'transfer_rate', title: '手续费率(百分比)', ninWidth:80}
                    ,{field: 'transfer_fee', title: '手续费', ninWidth:80}
                    ,{field: 'create_time', title: '转账时间', ninWidth:80}



//                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}

                ]]
            });
            //监听热卖操作
            // form.on('switch(sexDemo)', function(obj){
            //     var id = this.value;
            //     $.ajax({
            //         url:'{{url('admin/product_hot')}}',
            //         type:'post',
            //         dataType:'json',
            //         data:{id:id},
            //         success:function (res) {
            //             if(res.error != 0){
            //                 layer.msg(res.msg);
            //             }
            //         }
            //     });
            // });

            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:"{{url('admin/feedback/del')}}",
                            type:'get',
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
                    layer_show('编辑承兑商','{{url('admin/feedback/detail')}}?id='+data.id);
                } else if(obj.event === 'detail'){
                    layer_show('查看详情','{{url('admin/feedback/detail')}}?id='+data.id,800,600);
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