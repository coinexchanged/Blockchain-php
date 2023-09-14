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
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'申请中'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge layui-bg-red">'+'申请成功'+'</span>' : '' }}
    </script>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="bianji">编辑</a>
        <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="jilu">历史记录</a>
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
                , url: "{{url('admin/analysis/list')}}" //数据接口
                , page: true //开启分页
                , id: 'mobileSearch'
                , cols: [[ //表头
                    {field: 'id', title: 'ID', width: 80, sort: true}
                    , {field: 'title', title: '标题', width: 120}
                    , {field: 'avatar', title: '头像链接', width: 120}
                    , {field: 'team_num', title: '团队人数', minWidth: 80}
                    , {field: 'all_income', title: '总收益', minWidth: 80}
                    , {field: 'accuracy', title: '准确率', minWidth: 80}
                    , {field: 'order_num', title: '交易单数', minWidth: 80}
                    , {field: 'profit_num', title: '盈利单数', minWidth: 80}
                    , {field: 'loss_num', title: '亏损单数', minWidth: 80}
                    , {field: 'follow_num', title: '跟随人数', minWidth: 80}
                    , {field: 'url', title: '跳转链接', minWidth: 120}
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
                            url: '{{url('admin/analysis/delete')}}',
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
                    layer_show('编辑', '/admin/analysis/edit?id=' + data.id);
                } else if (obj.event === 'jilu') {
                    layer_show('历史记录', '/admin/analysis/jilu?id=' + data.id);
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function (data) {
                table.reload('mobileSearch', {
                    where: {uid: data.field.uid, status: data.field.status},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
        });

        function add() {
            layer_show('分析师添加', '/admin/analysis/add');
        }
    </script>
@endsection
