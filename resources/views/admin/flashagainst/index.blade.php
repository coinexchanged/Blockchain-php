@extends('admin._layoutNew')

@section('page-head')
<style>
    p.percent {
        text-align: right;
        margin-right: 10px;
    }
    p.percent::after {
        content: '%';
    }
</style>
@endsection

@section('page-content')
    <form class="layui-form layui-form-pane layui-inline" action="">

        <div class="layui-inline" style="margin-left: 50px;">
            <label class="layui-form-label">交易账号</label>
            <div class="layui-input-inline">
                <input type="text" name="account_number" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-input-inline date_time111" style="margin-left: 50px;">
            <input type="text" name="start_time" id="start_time" placeholder="请输入开始时间" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-input-inline date_time111" style="margin-left: 50px;">
            <input type="text" name="end_time" id="end_time" placeholder="请输入结束时间" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-inline" style="margin-left: 50px;">
            <label>日志类型&nbsp;&nbsp;</label>
            <div class="layui-input-inline">
                <select name="status" id="status" class="layui-input">
                    <option value="-1">所有类型</option>
                    <option value="0">审核中</option>
                    <option value="1">已通过</option>
                    <option value="2">未通过</option>
                </select>
            </div>
        </div>
        <div class="layui-inline">
            <div class="layui-input-inline">
                <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
            </div>
        </div>



    </form>
    <table id="demo" lay-filter="test"></table>
@endsection

@section('scripts')
    <script type="text/html" id="barDemo">
    @{{d.status==0 ? '<a class="layui-btn layui-btn-xs" lay-event="del">确认</a><a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="edit">驳回</a>' : '' }}
        

    </script>

    <script>
        layui.use(['table','form','laydate'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            var laydate = layui.laydate;
            //第一个实例
            table.render({
                elem: '#demo'
                ,toolbar: '#toolbar'
                ,url: '{{url('admin/flashagainst/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:60, sort: true}
                    ,{field: 'mobile', title: '手机号', minWidth:80}
                    ,{field: 'l_currency', title: '转出币种', minWidth:80}
                    ,{field: 'r_currency', title: '转入币种', minWidth:80}
                    ,{field: 'num', title: '数量', minWidth:80}
                    ,{field: 'absolute_quantity', title: '到账数量', minWidth:80}
                    ,{field: 'market_price', title: '行情价格', minWidth:80}
                    ,{field: 'price', title: '用户输入价格', minWidth:80}
                    ,{field: 'status_name', title: '状态', minWidth:80}
                    ,{field: 'create_time', title: '创建时间', minWidth:80}
                    ,{field: 'review_time', title: '更新时间', minWidth:80}

                    //,{field: 'create_time', title: '添加时间', width:160}
                    ,{title:'操作',width:240,toolbar: '#barDemo'}
                ]]
            });
            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.account_number;
                var start_time =  $("#start_time").val()
                var end_time =  $("#end_time").val()
                var status = $('#status').val()
                table.reload('mobileSearch',{
                    where:{mobile:account_number,status:status,start_time:start_time,end_time:end_time},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });

            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的确认么', function(index){
                        $.ajax({
                            url:'{{url('admin/flashagainst/affirm')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if (res.type == 'error') {
                                    layer.msg(res.message);
                                } else {
                                    layer.msg(res.message);
                                    setTimeout(function () {
                                        location.reload()
                                    }, 2000)
                                }
                            }
                        });


                    });
                } else if(obj.event === 'edit'){
                    layer.confirm('是否驳回', function(index){
                        $.ajax({
                            url:'{{url('admin/flashagainst/reject')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    layer.msg(res.message);
                                    setTimeout(function () {
                                        location.reload()
                                    }, 2000)
                                }
                            }
                        });


                    });
                }
            });

            // //监听提交
            // form.on('submit(mobile_search)', function(data){
            //     var account_number = data.field.account_number;
            //     table.reload('mobileSearch',{
            //         where:{account_number:account_number},
            //         page: {curr: 1}         //重新从第一页开始
            //     });
            //     return false;
            // });

        });
    </script>

@endsection