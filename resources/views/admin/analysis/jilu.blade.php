@extends('admin._layoutNew')
@section('page-head')
    <!--头部-->
    <style>
        .btn-group {
            top: -2px;
        }

        .cateManage {
            float: left;
        }

        .btn-search {
            left: -10px;
            position: relative;
            background: #e0e0e0;
        }

        #pull_right {
            text-align: center;
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

        .clear {
            clear: both;
        }

    </style>
@endsection

@section('page-content')
    <button type="button" class="layui-btn" onclick="add()">添加</button>
    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="statustml">
        @{{d.type==1 ? '买入' : '' }}
        @{{d.type==2 ? '卖出' : '' }}
    </script>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="bianji">编辑</a>
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="delete">删除</a>
    </script>
@endsection

@section('scripts')
    <script type="text/javascript">
        layui.use(['element', 'form', 'table', 'layedit', 'laypage', 'layer'], function () {
            var element = layui.element, form = layui.form, $ = layui.$, layedit = layui.layedit,
                laypage = layui.laypage;

            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                ,where: {id: "{{$cid}}"}
                , url: "{{url('admin/analysis/jilu_list')}}" //数据接口
                , page: true //开启分页

                , id: 'mobileSearch'
                , cols: [[ //表头
                    {field: 'id', title: 'ID', width: 80, sort: true}
                    , {field: 'name', title: '币种', minWidth: 120}
                    , {field: 'type', title: '买卖', minWidth: 120, toolbar: '#statustml'}
                    , {field: 'price', title: '价格', minWidth: 120}
                    , {field: 'open', title: '开仓价', minWidth: 120}
                    , {field: 'level', title: '平仓价', minWidth: 120}
                    , {field: 'income', title: '盈利', minWidth: 120}
                    , {title: '操作', minWidth: 120, toolbar: '#barDemo'}

                ]]
            });


            $(document).off('mousedown','.layui-table-grid-down').on('mousedown','.layui-table-grid-down',function(event){
                table._tableTrCurr = $(this).closest('td');
            });

            $(document).off('click','.layui-table-tips-main [lay-event]').on('click','.layui-table-tips-main [lay-event]',function(event){
                var elem = $(this);
                var tableTrCurr = table._tableTrCurr;
                if(!tableTrCurr){
                    return;
                }
                var layerIndex = elem.closest('.layui-table-tips').attr('times');
                // 关闭当前这个显示更多的tip
                layer.close(layerIndex);
                table._tableTrCurr.find('[lay-event="' + elem.attr('lay-event') + '"]').first().click();
            });


            table.on('tool(test)', function (obj) {
                var data = obj.data;
                if (obj.event === 'delete') {
                    layer.confirm('真的删除行么', function (index) {
                        $.ajax({
                            url: '{{url('admin/analysis/jilu_delete')}}',
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.type == 'error') {
                                    layer.msg(res.message);
                                } else {
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });
                    });
                } else if (obj.event === 'bianji') {
                    layer_show('历史记录编辑', '/admin/analysis/jilu_edit?id=' + data.id);
                }
            });
        });

        function add() {
            layer_show('历史记录添加', '/admin/analysis/jilu_add?cid={{$cid}}');
        }
    </script>
@endsection
