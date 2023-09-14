@extends('admin._layoutNew')
@section('page-head')
<!--头部-->
<style>
.btn-group {
    top: -2px;
}
#newsAdd {
    float: left;
}
.cateManage {
    float: left;
}
.btn-search {
    left: -10px;
    position: relative;
    background: #e0e0e0;
}
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
    <div class="layui-form layui-form-pane">     
        <div class="layui-form-item">
            <div class="operate_bar">
                <div class="layui-inline btn-group layui-btn-group">
                    <button class="layui-btn layui-btn-primary cateManage">管理分类</button>
                    <button class="layui-btn layui-btn-primary" id="newsAdd">发布新闻</button>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">分类过滤</label>
                    <div class="layui-input-inline">
                        <select name="cate" lay-verify="required" lay-filter="cate">
                            @if (count($data['cateList']) >0)
                            <option value="0">所有分类</option>
                            @foreach ($data['cateList'] as $cate)
                            <option value="{{ $cate->id }}" @if (\Illuminate\Support\Facades\Input::get("c_id") == $cate->id) selected @endif>{{ $cate->name }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>    
                <div class="layui-inline">
                    <label class="layui-form-label">语言过滤</label>
                    <div class="layui-input-inline">
                        <select name="lang" lay-verify="required" lay-filter="lang">
                            @if (count($lang_list) > 0)
                            <option value="0">所有语言</option>
                            @foreach ($lang_list as $key => $lang)
                            <option value="{{ $key }}" @if (\Illuminate\Support\Facades\Input::get("lang") == $key) selected @endif>{{ $lang }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>               
                <div class="layui-inline">
                    <label class="layui-form-label">关键字</label>
                    <div class="layui-input-inline">
                        <input type="text" name="keyword" required  lay-verify="required" placeholder="请输入关键字 " autocomplete="off" class="layui-input" value="">
                    </div>
                    <button class="layui-btn btn-search"> <i class="layui-icon">&#xe615;</i> </button>
                </div>
            </div>                       
        </div>
    </div>
    <table class="layui-table" lay-even>
        <colgroup>
            <col width="60">
            <col width="200">
            <col width="100">
            <col width="90">
            <col width="180">
            <col width="180">
            <col width="210">
        </colgroup>
        <thead>
        <tr>
            <th>ID</th>
            <th>标题</th>
            <th>分类</th>
            <th>语言</th>
            <th>发布人</th>
            <th>发布时间</th>
            <th>最后修改时间</th>
            <th>操作</th>           
        </tr> 
        </thead>
        <tbody>
        
            @forelse ($data['news'] as $key => $news)
            <tr>
            <td align="center">{{ $news->id }}</td>
            <td>{{ $news->title }}</td>
            <td>{{ $news->cate['name'] }}</td>
            <td>{{$news->lang}}</td>
            <td>{{ $news->author }}</td>
            <td>{{ $news->create_time }}</td>
            <td>{{ $news->update_time }}</td>
            <td>
                <!--<button class="layui-btn layui-btn-xs layui-btn-primary newsPreview" data-id="{{ $news->id }}">预览</button>-->
                <!-- <button class="layui-btn layui-btn-xs layui-btn-primary newsDiscuss" data-id="{{ $news->id }}">评论</button> -->
                <button class="layui-btn layui-btn-xs layui-btn-warm newsEdit" data-id="{{ $news->id }}">编辑</button>
                <button class="layui-btn layui-btn-xs layui-btn-danger newsDel" data-id="{{ $news->id }}">删除</button>
            </td>
            </tr>
            @empty
            <tr><td colspan="7" align="center">没有数据</td></tr>
            @endforelse        
        </tbody>
    </table>
    <div>
    
    {!! $data['news']->render() !!}
    </div>
@endsection
@section('scripts')
<script type="text/javascript" src="{{URL("/admin/js/newsIndex.js?v=").time()}}"></script>
@endsection