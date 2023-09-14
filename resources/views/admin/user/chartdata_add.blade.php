@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')

    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">时间1</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="time_one" type="text" value="@if(isset($data["time_one"])){{$data["time_one"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">金额1</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="price_one" type="number" value="@if(isset($data["price_one"])){{$data["price_one"]}}@endif" >
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">时间2</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="time_two" type="text" value="@if(isset($data["time_two"])){{$data["time_two"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">金额2</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="price_two" type="number" value="@if(isset($data["price_two"])){{$data["price_two"]}}@endif" >
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">时间3</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="time_three" type="text" value="@if(isset($data["time_three"])){{$data["time_three"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">金额3</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="price_three" type="number" value="@if(isset($data["price_three"])){{$data["price_three"]}}@endif" >
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">时间4</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="time_four" type="text" value="@if(isset($data["time_four"])){{$data["time_four"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">金额4</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="price_four" type="number" value="@if(isset($data["price_four"])){{$data["price_four"]}}@endif" >
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">时间5</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="time_five" type="text" value="@if(isset($data["time_five"])){{$data["time_five"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">金额5</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="price_five" type="number" value="@if(isset($data["price_five"])){{$data["price_five"]}}@endif" >
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">时间6</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="time_six" type="text" value="@if(isset($data["time_six"])){{$data["time_six"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">金额6</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="price_six" type="number" value="@if(isset($data["price_six"])){{$data["price_six"]}}@endif" >
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">时间7</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="time_seven" type="text" value="@if(isset($data["time_seven"])){{$data["time_seven"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">金额7</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="price_seven" type="number" value="@if(isset($data["price_seven"])){{$data["price_seven"]}}@endif" >
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">天数</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="time_day" type="text" value="@if(isset($data["time_day"])){{$data["time_day"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">值</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="value_day" type="number" value="@if(isset($data["value_day"])){{$data["value_day"]}}@endif" >
                    </div>
                </div>
            </div>



            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">月份1</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="month_one" type="text" value="@if(isset($data["month_one"])){{$data["month_one"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">值1</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="month_one_value" type="number" value="@if(isset($data["month_one_value"])){{$data["month_one_value"]}}@endif" >
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">月份2</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="month_two" type="text" value="@if(isset($data["month_two"])){{$data["month_two"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">值2</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="month_two_value" type="number" value="@if(isset($data["month_two_value"])){{$data["month_two_value"]}}@endif" >
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">月份3</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime" lay-verify="required" name="month_three" type="text" value="@if(isset($data["month_three"])){{$data["month_three"]}}@endif">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">值3</label>
                    <div class="layui-input-inline">
                        <input class="layui-input newsTime"  name="month_three_value" type="number" value="@if(isset($data["month_three_value"])){{$data["month_three_value"]}}@endif" >
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="adminrole_submit">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">

        layui.use(['form','upload','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;
            form.on('submit(adminrole_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/user/chart_data',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                    }
                });
                return false;
            });

        });


    </script>
@stop