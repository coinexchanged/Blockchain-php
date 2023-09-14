@extends('admin._layoutNew')

@section('page-head')
<style>
.layui-table-total [data-field="reward_qty"] div {
    text-align:right;
}
.layui-table-total div {
    font-weight: bolder;
}
.layui-form-label {
    width: unset;
}
.block {
    border:1px solid #fff;
    height: 100px;
    background: #31d4d1;
    color: #fff;
    text-align: center;
}
.block .title {
    padding-top: 20px;
    font-size: 20px;
    font-weight: bold;
}
.block .num-value {
    padding-top: 10px;
    font-size: 16px;
}
.block .block-icon {
    float:left;
    width:50%;
}
.block .block-content {
    float:left;
    width:50%;
}
.block .main-icon {
    margin-top: 20px;
}
.block-icon .main-icon .layui-block-icon {
    font-size:60px;
}
</style>

@endsection

@section('page-content')
<div class="layui-fluid">
<div class="layui-row">
    <div class="layui-col-md12">
        <form class="layui-form">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">用户账号</label>
                    <div class="layui-input-inline" style="width: 150px;">
                        <input type="text" name="account_number" autocomplete="off" class="layui-input">
                    </div>
                </div>
                {{--<div class="layui-inline">--}}
                    {{--<label class="layui-form-label">奖励场景</label>--}}
                    {{--<div class="layui-input-inline" style="width: 130px;">--}}
                        {{--<select name="scene" id="scene">--}}
                            {{--<option value="-1">全部</option>--}}
                            {{--@foreach ($scene_list as $key => $scene)--}}
                            {{--<option value="{{$key}}">{{$scene}}</option>--}}
                            {{--@endforeach--}}
                        {{--</select>--}}
                    {{--</div>--}}
                {{--</div>--}}
                <div class="layui-inline">
                    <label class="layui-form-label">日期范围</label>
                    <div class="layui-input-inline" style="width: 150px;">
                        <input class="layui-input layui-date" type="text" name="start_time" autocomplete="off" >
                    </div>
                    <div class="layui-form-mid">-</div>
                    <div class="layui-input-inline" style="width: 150px;">
                        <input class="layui-input layui-date" type="text" name="end_time" autocomplete="off" >
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn" lay-submit lay-filter="submit"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="layui-row">
    <div class="layui-col-md4">
        <div class="block">
            <div class="block-icon">
                <p class="main-icon">
                    <i class="layui-icon layui-icon-rmb layui-block-icon"></i>
                </p>
            </div>
            <div class="block-content" id="reward_total">
                <p class="title">亏返总额:</p>
                <p class="num-value"></p>  
            </div>
        </div>
    </div>
    <div class="layui-col-md4">
        <div class="block">
            <div class="block-icon">
                <p class="main-icon">
                    <i class="layui-icon layui-icon-user layui-block-icon"></i>
                </p>
            </div>
            <div class="block-content" id="user_num">
                <p class="title">亏返用户:</p>
                <p class="num-value"></p>  
            </div>
        </div>
    </div>
    <div class="layui-col-md4">
        <div class="block">
            <div class="block-icon">
                <p class="main-icon">
                    <i class="layui-icon layui-icon-log layui-block-icon"></i>
                </p>
            </div>
            <div class="block-content" id="data_count">
                <p class="title">记录条数:</p>
                <p class="num-value"></p>  
            </div>
        </div>
    </div>
</div>

<table id="data_table" lay-filter="data_table"></table>
@endsection
@section('scripts')
<script type="text/html" id="reward_qty">
    <div style="text-align:right; margin-right: 10px">
        <span>@{{ Number(d.value).toFixed(4)}}</span>
    </div>
</script>
<script>
    layui.use(['table', 'layer', 'form', 'laydate'], function() {
        var table = layui.table
            ,layer = layui.layer
            ,form = layui.form
            ,laydate = layui.laydate
            ,$ = layui.$
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/profits/lists'
            ,height: 'full-200'
            ,toolbar: true
            ,page: true
            ,totalRow: true
            ,cols: [[
                {field: 'id', title: 'id', width: 80}
                ,{field: 'account_number', title: '用户', width: 120, totalRowText: '小计:'}
//                ,{field: 'scene_name', title: '奖励场景', width: 140}
//                ,{field: 'reward_type', title: '奖励类型', width: 90, hide: true}
                ,{field: 'value', title: '亏返数量', width: 110, totalRow: true, templet: '#reward_qty'}
                ,{field: 'info', title: '亏返说明', width: 400}
//                ,{field: 'from_user_name', title: '触发用户', width: 120}
                ,{field: 'created_time', title: '亏返时间', width: 170}
            ]],
            done: function (res, curr, count) {
                $('#data_count .num-value').text(count);
                $.ajax({
                    url: '/admin/profits/count'
                    ,type: 'GET'
                    ,data: $('form.layui-form').serialize()
                    ,success: function (res) {
                        $('#reward_total .num-value').text(res.reward_total);
                        $('#user_num .num-value').text(res.user_count);
                    }
                });
                
            }
        });
        form.on('submit(submit)', function (data) {
            data_table.reload({
                where:data.field
                ,page: {
                    curr: 1
                }
            });
            return false;
        });
        $('input.layui-date').each(function () {
            laydate.render({
                elem: this
                ,type: 'datetime'
                ,format: 'yyyy-MM-dd HH:mm:ss'
            });
        });
        
    });
</script>
@endsection