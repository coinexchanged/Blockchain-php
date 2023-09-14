@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">账号</label>
            <div class="layui-input-block">
                <input type="text" name="account_number" readonly="readonly"  autocomplete="off" placeholder="" class="layui-input" value="{{$feedback->account_number}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">提交内容</label>
            <div class="layui-input-block">
                <textarea name="" id="" cols="90" rows="4">{{$feedback->content}}</textarea>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">回复内容</label>
            <div class="layui-input-block">
               <textarea name="reply_content" id="" cols="90" rows="10">{{$feedback->reply_content}}</textarea>
            </div>
        </div>
        <input type="hidden" name="id" value="{{$feedback->id}}">
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
                    url:'{{url('admin/feedback/reply')}}'
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