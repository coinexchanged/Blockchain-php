<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
      <legend>用户邀请关系图</legend>
    </fieldset>
     
    <div style="display: inline-block; padding: 10px; overflow: auto;">
      <ul id="demo2"></ul>
    </div>

<?php $__env->stopSection(); ?>

 <?php $__env->startSection('scripts'); ?>
    <script>

    window.onload = function() {
                   
            layui.use(['element', 'layer', 'table','tree'], function () {
                var element = layui.element;
                var layer = layui.layer;
                var table = layui.table;
                var $ = layui.$; 
    
                $.ajax({

                        url:'<?php echo e(url('admin/invite/getTree')); ?>',
                        type:'get',
                        dataType:'json',
                        success:function (res) {
                           


                              layui.tree({
                                elem: '#demo2' //指定元素
                                ,nodes: res.data
                              });


                            
                           
                            }
                     });

  
   
                    });
                }
            </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>