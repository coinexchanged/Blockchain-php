@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
{{--        <div class="layui-form-item">--}}
{{--            <label class="layui-form-label">交易币</label>--}}
{{--            <div class="layui-input-inline">--}}
{{--                <select name="base" lay-filter="" lay-search>--}}
{{--                    <option value=""></option>--}}

{{--                    @foreach($currencies as $currency)--}}
{{--                    <option value="{{$currency->currency_id}}" @if($currency->currency_id == $result->base) selected @endif>{{$currency->currency_name}}</option>--}}
{{--                    @endforeach--}}
{{--                </select>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="layui-form-item">--}}
{{--            <label class="layui-form-label">法币</label>--}}
{{--            <div class="layui-input-inline">--}}
{{--                <select name="target" lay-filter="">--}}
{{--                    <option value=""></option>--}}
{{--                    @if(!empty($currencies))--}}
{{--                    @foreach($legals as $legal)--}}
{{--                        <option value="{{$legal->id}}" @if($legal->id == $result->target) selected @endif>{{$legal->name}}</option>--}}
{{--                    @endforeach--}}
{{--                        @endif--}}
{{--                </select>--}}
{{--            </div>--}}
{{--        </div>--}}


        <div class="layui-form-item">
            <label class="layui-form-label">上涨幅度</label>
            <div class="layui-input-inline">
                <input type="text" name="float_up" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->float_down)){{$result->float_down}}@endif">
            </div>
        </div>

<input type="hidden" name="rid" value="@if(!empty($result->rid)){{$result->rid}}@else{{$rid}}@endif">

        <div class="layui-form-item">
            <label class="layui-form-label">拐点时间</label>
            <div class="layui-input-inline">
{{--                <input type="text" name="itime" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->itime)){{$result->itime}}@endif">--}}
                <input class="layui-input itime" name="itime"  lay-verify="required" placeholder="请选择时间" type="text" value="{{date('Y-m-d H:i')}}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">拐点结束时间</label>
            <div class="layui-input-inline">
                {{--                <input type="text" name="itime" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->itime)){{$result->itime}}@endif">--}}
                <input class="layui-input etime" name="etime"  lay-verify="required" placeholder="请选择时间" type="text" value="">
            </div>
        </div>

        <input type="hidden" name="id" value="@if(!empty($result->id)){{$result->id}}@endif">
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

            laydate.render({
                elem:'.itime',
                type:'datetime',
                format:'yyyy-MM-dd HH:mm'
            });

            laydate.render({
                elem:'.etime',
                type:'datetime',
                format:'yyyy-MM-dd HH:mm'
            });

            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/robot/sche_add')}}'
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
