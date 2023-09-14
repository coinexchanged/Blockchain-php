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
                <input type="text" name="mobile" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-inline" style="margin-left: 50px;">
            <label class="layui-form-label">姓名</label>
            <div class="layui-input-inline">
                <input type="text" name="name" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 50px;">
            <label>保险类型&nbsp;&nbsp;</label>
            <div class="layui-input-inline">
                <select name="type" id="type" class="layui-input">
                    <option value="-1">所有类型</option>
                   
                    @foreach ($ins_type as $key=> $ins)
                    <option value="{{ $ins->id }}">{{ $ins->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 50px;">
            <label>理赔状态&nbsp;&nbsp;</label>
            <div class="layui-input-inline">
                <select name="apply_status" id="apply_status" class="layui-input">
                    <option value="-1">所有</option>
                   
                    <option value="0">理赔中</option>
                    <option value="1">已赔付</option>
                    <option value="2">已拒绝</option>
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
   
        @{{d.apply_status==0 ? '<a class="layui-btn layui-btn-xs" lay-event="affirm">理赔</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="reject">拒绝</a>' : '' }}

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
                ,url: '{{url('admin/claim_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:60, sort: true}
                    ,{field: 'mobile', title: '用户', minWidth:80}
                    ,{field: 'user_name', title: '姓名', minWidth:80}
                    ,{field: 'user_insurance_id', title: '保险单', minWidth:80}
                    ,{field: 'insurance_type_name', title: '保险类型', minWidth:80}
                    ,{field: 'amount', title: '保险金额', minWidth:80}
                    ,{field: 'status_name', title: '理赔状态', minWidth:80}
                    ,{field: 'compensate', title: '理赔金额', minWidth:80}
                    ,{field: 'created_at', title: '申请时间', minWidth:80}
                    ,{field: 'updated_at', title: '处理时间', minWidth:80}
                  
                    ,{title:'操作',width:240,toolbar: '#barDemo'}
                ]]
            });
            //监听提交
            form.on('submit(mobile_search)', function(data){
                console.log(data.field);
                table.reload('mobileSearch',{
                    where:data.field,
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
                if(obj.event === 'affirm'){
                    layer.confirm('确认理赔吗', function(index){
                        $.ajax({
                            url:'{{url('admin/claim_affirm')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                layer.msg(res.message);
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    table.reload('mobileSearch',{
                                        page: {curr: 1}         //重新从第一页开始
                                    });                                }
                            }
                        });


                    });
                } else if(obj.event === 'reject'){
                    layer.confirm('是否驳回', function(index){
                        $.ajax({
                            url:'{{url('admin/claim_reject')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                layer.msg(res.message);
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    setTimeout(function () {
                                        location.reload()
                                    }, 2000)
                                }
                            }
                        });


                    });
                }
            });

        });
    </script>

@endsection