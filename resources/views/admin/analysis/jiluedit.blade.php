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
            <label class="layui-form-label">币种</label>
            <div class="layui-input-block">
                <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">买卖</label>
            <div class="layui-input-block">
                <input type="text" name="type" autocomplete="off" placeholder="1:买入 2:卖出" class="layui-input" value="{{$result->type}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">价格</label>
            <div class="layui-input-block">
                <input type="text" name="price" autocomplete="off" placeholder="" class="layui-input" value="{{$result->price}}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">开仓价</label>
            <div class="layui-input-block">
                <input type="text" name="open" autocomplete="off" placeholder="" class="layui-input" value="{{$result->open}}">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">平仓价</label>
            <div class="layui-input-block">
                <input type="text" name="level" autocomplete="off" placeholder="" class="layui-input" value="{{$result->level}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">盈利</label>
            <div class="layui-input-block">
                <input type="text" name="income" autocomplete="off" placeholder="" class="layui-input" value="{{$result->income}}">
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
                    url:'{{url('admin/analysis/post_jilu_edit')}}'
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
