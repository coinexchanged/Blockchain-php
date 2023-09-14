@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">广告标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" autocomplete="off" placeholder="" class="layui-input" value="{{$res->title}}" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">描述</label>
            <div class="layui-input-block">
                <input type="text" name="describe" autocomplete="off" placeholder="" class="layui-input" value="{{$res->describe}}" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">广告位</label>
            <div class="layui-input-block">
                <select name="position_id" lay-filter="aihao">
                    <option value=""></option>
                    @foreach($position as $adsense)
                  
                    <option value="{{$adsense['id']}}" @if($res['position_id'] == $adsense['id']) selected @endif>{{$adsense['name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
       

        <div class="layui-form-item">
            <label class="layui-form-label">开始时间</label>
            <div class="layui-input-block">
                <input type="text" id="time1"  name="start_time" autocomplete="off" class="layui-input" value="@if(!empty($res->start_time)){{$res->start_time}}@endif" placeholder="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">结束时间</label>
            <div class="layui-input-block">
                <input type="text" id="time2"  name="end_time" autocomplete="off" class="layui-input" value="@if(!empty($res->end_time)){{$res->end_time}}@endif" placeholder="" lay-verify="required">
            </div>
         </div>


        <div class="layui-form-item">
            <label class="layui-form-label">链接</label>
            <div class="layui-input-block">
                <input type="text" name="url" autocomplete="off" placeholder="" class="layui-input" value="{{$res->url}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">排序</label>
            <div class="layui-input-block">
                <input type="text" name="sort" autocomplete="off" placeholder="" class="layui-input" value="{{$res->sort}}">
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">广告图片</label>
            <div class="layui-input-block">
                <button class="layui-btn" type="button" id="upload_test">选择图片</button>
                <br>
                <img src="@if(!empty($res->pic)){{$res->pic}}@endif" id="img_thumbnail" class="thumbnail" style="display: @if(!empty($res->pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                <input type="hidden" name="pic" id="thumbnail" value="@if(!empty($res->pic)){{$res->pic}}@endif">
            </div>
        </div>

        <input type="hidden" name="id" value="{{$res->id}}">
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
        layui.use('upload', function(){
            var upload = layui.upload;

            //执行实例
            var uploadInst = upload.render({
                elem: '#upload_test' //绑定元素
                ,url: '{{URL("api/upload")}}' //上传接口
                ,done: function(res){
                    //上传完毕回调
                    if (res.type == "ok"){
                        $("#thumbnail").val(res.message)
                        $("#img_thumbnail").show()
                        $("#img_thumbnail").attr("src",res.message)
                    } else{
                        alert(res.message)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            });
        });


        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);

        var start={
            elem:'#time1',
            type:'date',
            show:true,
            closeStop:'#time1'
        };
       var end={
            elem:'#time2',
            type:'date',
            show:true,
            closeStop:'#time2'
        };

       lay('#time1').on('click',function(){
        if($('#time2').val() != null  && $('#time2').val() !=undefined &&$('#time2').val() !=''){
            start.max = $('#time2').val();
        }
        laydate.render(start);
       });
       
       lay('#time2').on('click',function(){
        if($('#time1').val() != null  && $('#time1').val() !=undefined &&$('#time1').val() !=''){
            end.min = $('#time1').val();
        }
        laydate.render(end);
       });




            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/ad/edit')}}'
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