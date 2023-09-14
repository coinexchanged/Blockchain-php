@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <form class="layui-form" method="POST">
        <div class="layui-inline">
            <label class="layui-form-label">数据统计</label>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">后台充值原生</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_cny" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$balance}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">以太坊充值原生</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$exchange}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">总会员</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$all_users}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">今日新增会员</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$new_users}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">VIP1会员</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$vip1_users}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">VIP2会员</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$vip2_users}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">小矿场主</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$USER_LEVEL_GROUP}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">大矿场主</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$USER_LEVEL_COUNTY_AGENT}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">小矿池主</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$USER_LEVEL_CITY_AGENT}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">大矿池主</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$USER_LEVEL_PROVINCIAL_AGENT}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">1000矿机总数</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$ltc2}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">10000矿机总数</label>
            <div class="layui-input-inline" style="width: 360px">
                <input type="text" name="hdl_radio" autocomplete="off" style="color: red !important;" class="layui-input layui-disabled" disabled value="{{$ltc1}}">
            </div>
        </div>
    </form>
@stop
@section('scripts')

@stop