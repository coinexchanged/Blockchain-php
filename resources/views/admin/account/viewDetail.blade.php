@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-block">
                    <input type="text" name="nickname" autocomplete="off" class="layui-input layui-disabled" value="{{ $results['account_number'] }}" placeholder="" disabled>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">记录</label>
                <div class="layui-input-block">
                    <input type="text" name="value" autocomplete="off" class="layui-input layui-disabled" value="{{ $results['value'] }}" placeholder=""  disabled>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">备注</label>
                <div class="layui-input-block">
                    <input type="text" name="info" autocomplete="off" class="layui-input layui-disabled" value="{{ $results['info'] }}" placeholder=""  disabled>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">时间</label>
                <div class="layui-input-block">
                    <input type="text" name="time" autocomplete="off" class="layui-input layui-disabled" value="{{ $results['created_time'] }}" placeholder=""  disabled>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
@stop