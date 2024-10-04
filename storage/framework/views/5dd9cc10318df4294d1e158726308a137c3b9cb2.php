<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('Banner Settings'); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="alert alert-warning mb-4" role="alert">
            <i class="fas fa-info-circle mr-2"></i> <?php echo app('translator')->get('After changes image. Please clear your browser\'s cache to see changes.'); ?>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card card-primary shadow">
                    <div class="card-body">


                        <form action="<?php echo e(route('admin.breadcrumbUpdate')); ?>" method="post"
                              enctype="multipart/form-data">
                            <?php echo method_field('put'); ?>
                            <?php echo csrf_field(); ?>
                            <div class="row justify-content-center">


                                <div class="col-md-4">
                                    <div class="form-group">
                                        <h5 class="text-dark"><?php echo app('translator')->get('Login/Register Image'); ?></h5>
                                        <div class="image-input ">
                                            <label for="image-upload" id="image-label"><i
                                                    class="fas fa-upload"></i></label>
                                            <input type="file" name="loginImage" placeholder="<?php echo app('translator')->get('Choose image'); ?>"
                                                   id="loginImage">
                                            <img id="loginImage_preview_container" class="preview-image"
                                                 src="<?php echo e(getFile(config('location.logo.path').'loginImage.png') ? : 0); ?>"
                                                 alt="preview image">
                                        </div>
                                        <?php $__errorArgs = ['loginImage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-danger"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <h5 class="text-dark"><?php echo app('translator')->get('Banner Image'); ?></h5>
                                        <div class="image-input">
                                            <label for="image-upload" id="image-label"><i
                                                    class="fas fa-upload"></i></label>
                                            <input type="file" name="banner" placeholder="<?php echo app('translator')->get('Choose image'); ?>"
                                                   id="image">
                                            <img id="image_preview_container" class="preview-image"
                                                 src="<?php echo e(getFile(config('location.logo.path').'banner.jpg') ? : 0); ?>"
                                                 alt="preview image">
                                        </div>
                                        <?php $__errorArgs = ['banner'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-danger"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <h5 class="text-dark"><?php echo app('translator')->get('Footer Background'); ?></h5>

                                        <div class="image-input">
                                            <label for="image-upload" id="image-label"><i
                                                    class="fas fa-upload"></i></label>
                                            <input type="file" name="footer" placeholder="<?php echo app('translator')->get('Choose image'); ?>"
                                                   id="footerImage">
                                            <img id="footerImage_preview_container" class="preview-image"
                                                 src="<?php echo e(getFile(config('location.logo.path').'footer.jpg') ? : 0); ?>"
                                                 alt="preview image">
                                        </div>
                                        <?php $__errorArgs = ['footer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-danger"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-center">


                                <div class="col-md-12">
                                    <div class="submit-btn-wrapper text-center mt-4">
                                        <button type="submit"
                                                class="btn waves-effect waves-light btn-primary btn-block btn-rounded">
                                            <span><?php echo app('translator')->get('Save Changes'); ?></span></button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>







<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        $(document).ready(function (e) {
            "use strict";

            $('#image').change(function () {
                let reader = new FileReader();
                reader.onload = (e) => {
                    $('#image_preview_container').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });


            $('#footerImage').change(function () {
                let reader = new FileReader();
                reader.onload = (e) => {
                    $('#footerImage_preview_container').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });


            $('#loginImage').change(function () {
                let reader = new FileReader();
                reader.onload = (e) => {
                    $('#loginImage_preview_container').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });

        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH F:\GitHub\tarbetz\resources\views/admin/banner.blade.php ENDPATH**/ ?>