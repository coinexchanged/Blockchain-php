@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <header class="larry-personal-tit">
        <span>审核状态</span>
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            {{--<div class="layui-form-item">--}}
                {{--<label class="layui-form-label">购买数量</label>--}}
                {{--<div class="layui-input-block">--}}
                {{--<input type="text" name="buy_num" autocomplete="off" class="layui-input" value="@if (isset($result['buy_num'])){{$result['buy_num']}}@endif" placeholder="">--}}
                {{--</div>--}}
            {{--</div>--}}
             {{--<div class="layui-form-item">--}}
                {{--<label class="layui-form-label">状态</label>--}}
                {{--<div class="layui-input-inline">--}}
                    {{--<select name="status" class="" lay-filter="status" lay-verify="required">--}}

                            {{--<option value="1" @if(isset($result['status']) && $result['status'] ==1) selected @endif>提交成功，未确定</option>--}}
                        {{--<option value="2" @if(isset($result['status']) && $result['status'] ==2) selected @endif>审核通过，交易完成</option>--}}
                        {{--<option value="3" @if(isset($result['status']) && $result['status'] ==3) selected @endif>审核不通过</option>--}}



                        {{--@foreach ($list as $k => $v)--}}
                            {{--<option value="@if (isset($v['id'])){{ $v['id'] }}@endif" @if(isset($result['currency_id']) && $result['currency_id'] == $v['id']) selected @endif>@if (isset($v['name'])){{ $v['name'] }}@endif</option>--}}
                        {{--@endforeach--}}
                    {{--</select>--}}
                {{--</div>--}}
            {{--</div>--}}
        <div class="layui-form-item">
                <label class="layui-form-label">审核通过</label>
                <div class="layui-input-block">
                    <input type="text"  autocomplete="off" class="layui-input" value="杠杆划转{{$result['number']}}到C2C账户" readonly="readonly">
                </div>
            <input type="text" style="display: none " name="number"  class="layui-input" value="{{$result['number']}}" >
            {{--<input type="text" style="display: none " name="zhitui_integral"  class="layui-input" value="{{$result['zhitui_integral']}}">--}}
            <input type="text" style="display: none " name="user_id"  class="layui-input" value="{{$result['user_id']}}">
            </div>

             <input type="hidden" name="id" value="@if (isset($result['id'])){{$result['id']}}@endif">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="adminuser_submityes">审核通过</button>
                    <button class="layui-btn" lay-submit lay-filter="adminuser_submitno">审核不通过</button>
                    {{--<button type="reset" class="layui-btn layui-btn-primary">重置</button>--}}
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">

        layui.use(['form','upload','layer','laydate'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;
           var laydate = layui.laydate;

          laydate.render({
                elem: '#start_time',
                type: 'datetime'
            });
            laydate.render({
                elem: '#end_time',
                type: 'datetime'
            });
            form.on('submit(adminuser_submityes)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/levertolegal/postAddyes',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                        if(res.type == 'ok') {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }else{
                            return false;
                        }
                    }
                });
                return false;
            });


            form.on('submit(adminuser_submitno)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/levertolegal/postAddno',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                        if(res.type == 'ok') {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }else{
                            return false;
                        }
                    }
                });
                return false;
            });

        });


    </script>
@stop