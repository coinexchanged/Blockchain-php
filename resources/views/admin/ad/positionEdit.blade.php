@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="{{$res->name}}" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">描述</label>
            <div class="layui-input-block">
                <input type="text" name="describe" autocomplete="off" placeholder="" class="layui-input" value="{{$res->describe}}" >
            </div>
        </div>
        <!-- <div class="layui-form-item">
                <label class="layui-form-label">是否显示</label>
                <div class="layui-input-block">
                    <input type="radio" name="is_show" value="1" title="是" @if($res->is_show ==1) checked @endif >
                    <input type="radio" name="is_show" value="0" title="否" @if($res->is_show ==0) checked @endif>
                </div>
        </div> -->
         <input type="hidden" name="id" value="{{$res['id']}}">
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
                    url:'{{url('admin/ad/position_edit')}}'
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