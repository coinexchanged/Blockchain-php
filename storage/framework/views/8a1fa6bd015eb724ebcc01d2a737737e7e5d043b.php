<?php $__env->startSection('page-head'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <div style="">


        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline">
                <label class="layui-form-label">用户ID</label>
                <div class="layui-input-inline">
                    <input type="text" name="uid" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-inline">
                    <select name="status" class="layui-input">
                        <option value="1" selectde>申请中</option>
                        <option value="2">已发放</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i
                                class="layui-icon">&#xe615;</i></button>
                </div>
            </div>


        </form>
    </div>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="statustml">
        {{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'申请中'+'</span>' : '' }}
        {{d.status==2 ? '<span class="layui-badge layui-bg-red">'+'申请成功'+'</span>' : '' }}
    </script>
    <script type="text/html" id="barDemo">
        {{d.status==1 ?'<a class="layui-btn layui-btn-xs" lay-event="faka">发放</a>':''}}
        <a class="layui-btn layui-btn-xs" lay-event="delete">删除</a>
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
                , url: "<?php echo e(url('admin/faka/list')); ?>" //数据接口
                , page: true //开启分页
                , id: 'mobileSearch'
                , cols: [[ //表头
                    {field: 'id', title: 'ID', width: 80, sort: true}
                    , {field: 'uid', title: '用户ID', width: 80}
                    , {field: 'username', title: '帐号', width: 120}
                    , {field: 'country', title: '国家', minWidth: 80}
                    , {field: 'currency', title: '币种', minWidth: 80}
                    , {field: 'sum', title: '转账金额', minWidth: 80}
                    , {field: 'card', title: '卡号', minWidth: 80}
                    , {field: 'bank', title: '银行', minWidth: 80}
                    , {field: 'name', title: '姓名', minWidth: 80}
                    , {field: 'country2', title: '发卡国家', minWidth: 80}
                    , {field: 'currency2', title: '发卡币种', minWidth: 80}
                    , {field: 'remarks', title: '备注', minWidth: 120}
                    , {field: 'status', title: '状态', minWidth: 80, templet: '#statustml'}
                    , {field: 'time', title: '提交时间', minWidth: 180,templet:function(obj,row){
                            return getTime(obj.time);
                        }}
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
                            url: '<?php echo e(url('admin/faka/delete')); ?>',
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
                } else if (obj.event === 'faka') {
                    layer_show('发卡', '/admin/faka/edit?id=' + data.id);
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

        function getTime(nS) {
            var date=new Date(parseInt(nS)* 1000);
            var year=date.getFullYear();
            var mon = date.getMonth()+1;
            var day = date.getDate();
            var hours = date.getHours();
            var minu = date.getMinutes();
            var sec = date.getSeconds();
            return year+'-'+mon+'-'+day+' '+hours+':'+minu+':'+sec;
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>