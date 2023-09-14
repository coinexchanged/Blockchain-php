@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">

        <div class="layui-form-item">
            <label class="layui-form-label">选择法币</label>
            <div class="layui-input-block">
                <select name="legal_id" lay-filter="type">
                    @foreach($rest as $legal)
                        <option value="{{$legal->id}}" >{{$legal->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">币种</label>
            <div class="layui-input-block">
                <select name="currency_id" lay-filter="type">
                    @foreach($list as $currency)
                        <option value="{{$currency->id}}">{{$currency->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">类型</label>
            <div class="layui-input-block">
                <select name="type" lay-filter="type">
                  <option value="0" > 历史数据</option>
                    <option value="5" >分时</option>
                    <option value="6" > 5分种</option>
                    <option value="1" > 15分种</option>
                    <option value="7" >30分种</option>
                  <option value="2" >一小时</option>
                  {{--<option value="3" >四小时</option>--}}
                  <option value="4" >一天</option>
                    <option value="8" >一周</option>
                    <option value="9" >一月</option>

                </select>

            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开盘价</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="end_time" name="start_price" value="" placeholder="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">收盘价</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="end_time" name="end_price" value="" placeholder="">
            </div>
        </div>
         <div class="layui-form-item">
            <label class="layui-form-label">最高价</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="end_time" name="highest" value="" placeholder="">
            </div>
        </div>
          <div class="layui-form-item">
            <label class="layui-form-label">最低价</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="end_time" name="mminimum" value="" placeholder="">
            </div>
        </div>
           <div class="layui-form-item">
            <label class="layui-form-label">总量</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="end_time" name="number" value="" placeholder="">
            </div>
        </div>
           <div class="layui-form-item">
                <label class="layui-form-label">时间</label>
                <div class="layui-input-inline">
                    <input type="text" name="start_time" autocomplete="off" class="layui-input" value="" placeholder="" lay-verify="required" id="start_time">
                </div>
                
            </div>



       
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
                  laydate.render({
                elem: '#start_time',
                type: 'datetime'
            });
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/market_add')}}'
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