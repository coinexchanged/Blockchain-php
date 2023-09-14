<?php $__env->startSection('page-head'); ?>
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page-content'); ?>
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
            <?php $__empty_1 = true; $__currentLoopData = $data['newsCate']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $newsCate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
            <td align="center"><?php echo e($newsCate->id); ?></td>
            <td><?php echo e($newsCate->name); ?></td>
            <td align="center"><?php echo e($newsCate->sorts); ?></td>
            
            <td align="center">
                <?php if($newsCate->is_show == 1): ?><i class="layui-icon layui-icon-ok-circle" style="color:#5FB878;"></i><?php else: ?><i class="layui-icon layui-icon-close-fill"></i><?php endif; ?>
            </td>
           
            <td><?php echo e($newsCate->create_time); ?></td>
            <td><?php echo e($newsCate->update_time); ?></td>
            <td>
                <button class="layui-btn layui-btn-xs layui-btn-warm newsCateEdit" data-id="<?php echo e($newsCate->id); ?>">编辑</button>
                <button class="layui-btn layui-btn-xs layui-btn-danger newsCateDel" data-id="<?php echo e($newsCate->id); ?>">删除</button>
            </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="6" align="center">没有数据</td>
            </tr>
            <?php endif; ?>
        
        </tbody>
    </table>
    <div>
    <?php echo $data['newsCate']->render(); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(URL("/admin/js/newsCate.js?v=").time()); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>