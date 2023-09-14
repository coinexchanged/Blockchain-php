@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">结束时间</label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" id="test" value="{{ isset($result->lock_time) ? date('Y-m-d H:i:s', $result->lock_time) : '' }}" name="date">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">账户状态</label>
                <div class="layui-input-block">
                <input type="radio" name="status" class="sports" value="0" title="正常" {{ $result->status== 0 ? 'checked' : '' }}>
                <input type="radio" name="status" class="sports" value="1" title="锁定" {{ $result->status== 1 ? 'checked' : '' }}>
                </div>
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
         layui.use(['form', 'laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            laydate.render({
                elem: '#test'
                ,type: 'datetime'
                ,position: 'fixed'
                ,zIndex: 999999999
            });
            //监听提交
            form.on('submit(demo1)', function(data) {
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/user/lock')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res) {
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            //parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>
@endsection