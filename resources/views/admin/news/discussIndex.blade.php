@extends('admin._layoutNew')
@section('page-head')
<!--头部-->
<link rel="stylesheet" href="{{URL("/admin/css/diypaginate.css")}}">
@endsection
@section('page-content')
<div>
  <span class="layui-breadcrumb" lay-separator="->">
    <a href="javascript:;">{{ $news->cate->name }}</a>
    <a><cite>{{ $news->title }}</cite></a>
  </span>
</div>
<table class="layui-table" lay-even>
  <colgroup>
    <col width="60">
    <col width="100">
    <col width="180">
    <col width="400">
    <col width="60">
    <col width="60">
    <col width="180">
    <col width="80">
    <col width="180">
    <col>
  </colgroup>
  <thead>
    <tr>
      <th>ID</th>
      <th>账号</th>
      <th>昵称</th>
      <th>内容</th>
      <th>支持</th>
      <th>反对</th>
      <th>发布时间</th>
      <th>显示状态</th>
      <th>操作</th>
    </tr> 
  </thead>
  <tbody>
    @forelse ($newsDiscuss as $key => $discuss)
    <tr>
      <td align="center">{{ $discuss->id }}</td>
      <td>{{ $discuss->user->mobile }}</td>
      <td>{{ $discuss->user->nickname }}</td>
      <td>{{ $discuss->content }}</td>
      <td>{{ $discuss->support }}</td>
      <td>{{ $discuss->oppose }}</td>
      <td>{{ $discuss->create_time }}</td>
      <td align="center">@if ($discuss->status == 1)<i class="layui-icon" style="color:#5FB878;">&#xe616;</i>@else<i class="layui-icon">&#x1007;</i>@endif</td>
      <td>
        <button class="layui-btn layui-btn-xs statusToggle" data-id="{{ $discuss->id }}">@if($discuss->status == 1)隐藏@else显示@endif</button>
        <button class="layui-btn layui-btn-danger layui-btn-xs discussDel" data-id="{{ $discuss->id }}">删除</button>        
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="9" align="center">没有数据</td>
    </tr>
    @endforelse
  </tbody>
</table>
<div>
{!! $newsDiscuss->render() !!}
<ul class="page_info">
  <li>共{{$newsDiscuss->total()}}条数据，本页{{$newsDiscuss->count()}}条，{{$newsDiscuss->currentPage()}}/@if ($newsDiscuss->count() ==0 || $newsDiscuss->total()==0) 1 @else{{ceil($newsDiscuss->total()/$newsDiscuss->count())}}@endif页</li>
</ul>
</div>

@endsection
@section('scripts')
<script type="text/javascript" src="{{URL("/admin/js/newsDiscussIndex.js?v=time()")}}"></script>
@endsection