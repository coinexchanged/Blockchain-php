@extends('admin._layoutNew')

@section('page-head')
<style>
    .layui-form-label {
        width: 150px;
    }
    .layui-input-block {
        margin-left: 180px;
    }
</style>
@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" autocomplete="off" placeholder="" class="layui-input" value="{{$result->title}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">头像</label>
            <div class="layui-input-block">
                <input type="text" name="avatar" autocomplete="off" placeholder="" class="layui-input" value="{{$result->avatar}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">团队人数</label>
            <div class="layui-input-block">
                <input type="text" name="team_num" autocomplete="off" placeholder="" class="layui-input" value="{{$result->team_num}}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">总收益</label>
            <div class="layui-input-block">
                <input type="text" name="all_income" autocomplete="off" placeholder="" class="layui-input" value="{{$result->all_income}}">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">准确率</label>
            <div class="layui-input-block">
                <input type="text" name="accuracy" autocomplete="off" placeholder="" class="layui-input" value="{{$result->accuracy}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">交易单数</label>
            <div class="layui-input-block">
                <input type="text" name="order_num" autocomplete="off" placeholder="" class="layui-input" value="{{$result->order_num}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">盈利单数</label>
            <div class="layui-input-block">
                <input type="text" name="profit_num" autocomplete="off" placeholder="" class="layui-input" value="{{$result->profit_num}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">亏损单数</label>
            <div class="layui-input-block">
                <input type="text" name="loss_num" autocomplete="off" placeholder="" class="layui-input" value="{{$result->loss_num}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">跟随人数</label>
            <div class="layui-input-block">
                <input type="text" name="follow_num" autocomplete="off" placeholder="" class="layui-input" value="{{$result->follow_num}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">跳转链接</label>
            <div class="layui-input-block">
                <input type="text" name="url" autocomplete="off" placeholder="" class="layui-input" value="{{$result->url}}">
            </div>
        </div>
        <input type="hidden" name="id" value="{{$result->id}}">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">提交修改</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/analysis/postedit')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>

@endsection
