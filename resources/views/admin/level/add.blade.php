@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">充币数量</label>
            <div class="layui-input-block">
                <input type="text" name="fill_currency" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->fill_currency}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">直推数量</label>
            <div class="layui-input-block">
                <input type="text" name="direct_drive_count" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->direct_drive_count}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">直推金额</label>
            <div class="layui-input-block">
                <input type="text" name="direct_drive_price" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->direct_drive_price}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最大代数</label>
            <div class="layui-input-block">
                <input type="text" name="max_algebra" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->max_algebra}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">级别</label>
            <div class="layui-input-block">
                <input type="text" name="level" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->level}}">
            </div>
        </div>



        <input type="hidden" name="id" value="{{$result->id}}">
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
                    url:'{{url('admin/level_add')}}'
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