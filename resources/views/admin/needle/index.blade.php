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
                    <button class="layui-btn layui-btn-primary" id="newsAdd">添加针</button>
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
            <th>插入时间（分钟单位）</th>
            <th>开</th>
            <th>高</th>
            <th>低</th>
            <th>收</th>
            <th>交易对</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>

            @forelse ($data['news'] as $key => $news)
            <tr>
            <td align="center">{{ $news->id }}</td>
            <td>{{ $news->itime }}</td>
            <td>{{ $news->open }}</td>
            <td>{{ $news->high }}</td>
            <td>{{ $news->low }}</td>
            <td>{{ $news->close }}</td>
                <td>{{ $news->base }}/{{ $news->target }}</td>
            <td>
               <button class="layui-btn layui-btn-xs layui-btn-danger newsDel" data-id="{{ $news->id }}">删除</button>
            </td>
            </tr>
            @empty
            <tr><td colspan="8" align="center">没有数据</td></tr>
            @endforelse
        </tbody>
    </table>
    <div>

    {!! $data['news']->render() !!}
    </div>
@endsection
@section('scripts')
<script type="text/javascript">
    layui.use(['element', 'form', 'layedit', 'laypage', 'layer'], function() {
        var element = layui.element, form = layui.form, $ = layui.$, layedit = layui.layedit, laypage = layui.laypage;


        $('#newsAdd').click(function() {


                    var index = layer.open({
                        title:'添加针'
                        ,type:2
                        ,content: '/admin/needle/add'
                        ,area: ['800px', '600px']
                        ,maxmin: true
                        ,anim: 3
                    });
                    layer.full(index);

        });

        $('.newsDel').click(function (){
            let id=$(this).attr('data-id');
            layer.load(2);
            $.ajax({
                type: 'DELETE'
                ,url: '/admin/needle/del'
                ,data: {id:id}
                ,success: function(data) {
                    if(data.type == 'ok') {
                        layer.msg(data.message, {
                            icon: 1,
                            time: 1000,
                            end: function() {
                                // var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                // parent.layer.close(index);
                                window.location.reload();
                            }
                        });
                    } else {
                        layer.msg(data.message, {icon:2});
                    }
                }
                ,error: function(data) {
                    //重新遍历获取JSON的KEY
                    var str = '服务器验证失败！';
                    for(var o in data.responseJSON.errors) {
                        str += data.responseJSON.errors[o];
                    }
                    layer.msg(str, {icon:2});
                }
            });
        });


    });
</script>
@endsection
