@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">参数</label>
            <div class="layui-input-block">
                <input type="text" name="key" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$setting->key}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">参数值</label>
            <div class="layui-input-block">
                <input type="text" name="value" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$setting->value}}">
            </div>
        </div>

       
        <div class="layui-form-item">
            <label class="layui-form-label">参数说明</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" id="reserve_time" name="notes" value="{{$setting->notes}}">
            </div>
        </div>

        <input type="hidden" name="id" value="{{$setting->id}}">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
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
                    url:'{{url('admin/setting/postadd')}}'
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