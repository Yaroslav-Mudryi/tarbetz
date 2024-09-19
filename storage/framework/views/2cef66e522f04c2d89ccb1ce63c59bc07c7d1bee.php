<?php $__env->startSection('title',trans('Blog Details')); ?>

<?php $__env->startSection('content'); ?>
    <!-- blog details -->
    <section class="blog-details blog-list">
        <div class="container">
            <div class="row gy-5 g-lg-5">
                <div class="col-lg-8">
                    <div class="blog-box row">
                        <div class="col-md-12 img-box">
                            <img
                                src="<?php echo e($singleItem['image']); ?>"
                                class="img-fluid"
                                alt="<?php echo e($singleItem['title']); ?>"/>
                        </div>
                        <div class="col-md-12 text-box">
                            <a href="javascript:void(0)" class="title">
                                <?php echo app('translator')->get($singleItem['title']); ?>
                            </a>
                            <div class="date-author">
                           <span class="author">
                              <i class="fas fa-dot-circle"></i><?php echo app('translator')->get('Admin'); ?>
                           </span>
                                <span class="float-end"><?php echo app('translator')->get($singleItem['date']); ?></span>
                            </div>
                            <p>
                                <?php echo app('translator')->get($singleItem['description']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h4><?php echo app('translator')->get('Related Posts'); ?></h4>
                    <?php if(isset($popularContentDetails['blog'])): ?>
                        <?php $__currentLoopData = $popularContentDetails['blog']->sortDesc(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="related-post">
                                <div class="img-box">
                                    <img
                                        class="img-fluid"
                                        src="<?php echo e(getFile(config('location.content.path') . @$data->content->contentMedia->description->image)); ?>"
                                        alt="..."/>
                                </div>
                                <div class="text-box">
                                    <a href="<?php echo e(route('blogDetails', [slug(optional($data->description)->title), $data->content_id])); ?>" class="title">
                                        <?php echo app('translator')->get(@$data->description->title); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($theme.'layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/starpvfh/public_html/resources/views/themes/betting/blogDetails.blade.php ENDPATH**/ ?>