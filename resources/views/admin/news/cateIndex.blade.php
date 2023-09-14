@extends('admin._layoutNew')
@section('page-head')
<style>
    #pull_right{
        text-align:center;
    }
    .pull-right {
        /*float: left!important;*/
    }
    .pagination {
        display: inline-block;
        padding-left: 0;
        margin: 20px 0;
        border-radius: 4px;
    }
    .pagination > li {
        display: inline;
    }
    .pagination > li > a,
    .pagination > li > span {
        position: relative;
        float: left;
        padding: 6px 12px;
        margin-left: -1px;
        line-height: 1.42857143;
        color: #428bca;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #ddd;
    }
    .pagination > li:first-child > a,
    .pagination > li:first-child > span {
        margin-left: 0;
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }
    .pagination > li:last-child > a,
    .pagination > li:last-child > span {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }
    .pagination > li > a:hover,
    .pagination > li > span:hover,
    .pagination > li > a:focus,
    .pagination > li > span:focus {
        color: #2a6496;
        background-color: #eee;
        border-color: #ddd;
    }
    .pagination > .active > a,
    .pagination > .active > span,
    .pagination > .active > a:hover,
    .pagination > .active > span:hover,
    .pagination > .active > a:focus,
    .pagination > .active > span:focus {
        z-index: 2;
        color: #fff;
        cursor: default;
        background-color: #428bca;
        border-color: #428bca;
    }
    .pagination > .disabled > span,
    .pagination > .disabled > span:hover,
    .pagination > .disabled > span:focus,
    .pagination > .disabled > a,
    .pagination > .disabled > a:hover,
    .pagination > .disabled > a:focus {
        color: #777;
        cursor: not-allowed;
        background-color: #fff;
        border-color: #ddd;
    }
    .clear{
        clear: both;
    }

</style>
@endsection
@section('page-content')
    <div class="">
        <button class="layui-btn layui-btn-primary" id="newsCateAdd">添加分类</button>
    </div>
    <table class="layui-table">
        <colgroup>
            <col width="60">
            <col width="180">
            <!--<col width="100">-->
            <col width="100">
            <col width="180">
            <col width="180">
        </colgroup>
        <thead>
        <tr>
            <th>ID</th>
            <th>名称</th>
            <th>显示顺序</th>
            <th>帮助中心是否显示</th>
            <th>添加时间</th>
            <th>修改时间</th>
            <th>操作</th>           
        </tr> 
        </thead>
        <tbody>
            @forelse ($data['newsCate'] as $key => $newsCate)
            <tr>
            <td align="center">{{ $newsCate->id }}</td>
            <td>{{ $newsCate->name }}</td>
            <td align="center">{{ $newsCate->sorts }}</td>
            
            <td align="center">
                @if ($newsCate->is_show == 1)<i class="layui-icon layui-icon-ok-circle" style="color:#5FB878;"></i>@else<i class="layui-icon layui-icon-close-fill"></i>@endif
            </td>
           
            <td>{{ $newsCate->create_time }}</td>
            <td>{{ $newsCate->update_time }}</td>
            <td>
                <button class="layui-btn layui-btn-xs layui-btn-warm newsCateEdit" data-id="{{ $newsCate->id }}">编辑</button>
                <button class="layui-btn layui-btn-xs layui-btn-danger newsCateDel" data-id="{{ $newsCate->id }}">删除</button>
            </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" align="center">没有数据</td>
            </tr>
            @endforelse
        
        </tbody>
    </table>
    <div>
    {!! $data['newsCate']->render() !!}
    </div>
@endsection
@section('scripts')
<script src="{{URL("/admin/js/newsCate.js?v=").time()}}"></script>
@endsection