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
            <label class="layui-form-label">姓名</label>
            <div class="layui-input-block">
                <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">国家</label>
            <div class="layui-input-block">
                <input type="text" name="country2" autocomplete="off" placeholder="" class="layui-input" value="{{$result->country2}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行</label>
            <div class="layui-input-block">
                <input type="text" name="bank" autocomplete="off" placeholder="" class="layui-input" value="{{$result->bank}}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">币种</label>
            <div class="layui-input-block">
                <input type="text" name="currency2" autocomplete="off" placeholder="" class="layui-input" value="{{$result->currency2}}">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">备注</label>
            <div class="layui-input-block">
                <input type="text" name="remarks" autocomplete="off" placeholder="" class="layui-input" value="{{$result->remarks}}">
            </div>
        </div>
        <input type="hidden" name="id" value="{{$result->id}}">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即发放</button>
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
                    url:'{{url('admin/faka/postedit')}}'
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
