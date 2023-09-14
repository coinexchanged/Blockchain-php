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
            <label class="layui-form-label">用户</label>
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
                    <option value="1">正向险</option>
                    <option value="2">反向险</option>
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 50px;">
            <label>保单状态&nbsp;&nbsp;</label>
            <div class="layui-input-inline">
                <select name="status" id="status" class="layui-input">
                    <option value="-1">所有</option>
                    <option value="1">生效中</option>
                    <option value="0">已失效</option>
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
    <script type="text/html" id="insurance_type_t">
        @{{# if (d.insurance_type_type == 1) { }}
        <span style="color:#89deb3;">@{{d.insurance_type_name}}</span>
        @{{# } else if (d.insurance_type_type == 2) { }}
        <span style="color:#d67a7a;">@{{d.insurance_type_name}}</span>
        @{{# } }}
    </script>
    <script type="text/html" id="status_t">
        <span class="layui-badge @{{d.status == 1 ? 'layui-bg-green' : ''}}">@{{d.status_str}}</span>
    </script>
    <script type="text/html" id="claim_status_t">
        <span class=" @{{d.claim_status == 1 ? 'layui-badge layui-bg-green' : 'layui-bg-default'}}">@{{d.claim_status_str}}</span>
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
                ,url: '{{url('admin/insurance/order_lists')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:60, sort: true}
                    ,{field: 'mobile', title: '用户', minWidth:150}
                    ,{field: 'user_name', title: '姓名', minWidth:80}
                    ,{field: 'insurance_type_type', title: '保险类型', minWidth:80,templet:"#insurance_type_t"}
                    ,{field: 'amount', title: '受保金额', minWidth:80}
                    ,{field: 'insurance_amount', title: '保险金额', minWidth:80}
                    ,{field: 'status', title: '状态', minWidth:80,templet:"#status_t"}
                    ,{field: 'claim_status', title: '理赔状态', minWidth:80,templet:"#claim_status_t"}
                    ,{field: 'rescinded_type_str', title: '解约类型', minWidth:80}
                    ,{field: 'created_at', title: '申购时间', minWidth:150}
                    ,{field: 'rescinded_at', title: '解约时间', minWidth:150}

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


        });
    </script>

@endsection