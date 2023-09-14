@extends('admin._layoutNew')
@section('page-head')
<link rel="stylesheet" type="text/css" href="{{URL("admin/css/personal.css")}}" media="all">
@endsection
@section('page-content')
	<form class="layui-form" method="POST">
		{{ csrf_field() }}
        <input type="hidden" name="id" value="@if (isset($id)){{ $id }}@endif" >
		<div class="layui-form-item">
			<label class="layui-form-label">分类名称</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="name" lay-verify="required" placeholder="请输入分类名称" type="text" value="@if (isset($name)){{ $name }}@endif">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">显示顺序</label>
			<div class="layui-input-block">
				<input class="layui-input" placeholder="请输入文章关键字" type="text" name="sorts" value="@if(isset($sorts)){{ $sorts }}@else{{0}}@endif">
			</div>
		</div>
		
		<div class="layui-form-item">
			<label class="layui-form-label">是否显示</label>
			<div class="layui-input-block">
                <input type="radio" name="is_show" value="1" title="是" @if (isset($is_show))  @if ($is_show == 1) checked @endif @else checked @endif >
                <input type="radio" name="is_show" value="0" title="否" @if (isset($is_show) && $is_show == 0) checked @endif>
			</div>
		</div>
		
		<div class="layui-form-item">
			<div class="layui-input-block">
				<button class="layui-btn" lay-submit="" lay-filter="submit">立即提交</button>
				<button type="reset" class="layui-btn layui-btn-primary">重置</button>
		    </div>
		</div>
	</form>
@endsection
@section('scripts')
<script type="text/javascript" src="{{URL("/admin/js/newsCateForm.js")}}"></script>
@endsection