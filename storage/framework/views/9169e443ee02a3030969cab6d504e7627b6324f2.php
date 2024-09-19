<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get($title); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-7">
            <div class="card card-primary m-0 m-md-4 my-4 m-md-0 shadow">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between mb-3">
                        <div class="col-md-6">
                            <h5 class="card-title  font-weight-bold color-primary"><?php echo app('translator')->get('CurrencyLayer Api Config (Fiat Currency)'); ?></h5>
                        </div>
                    </div>
                    <form action="<?php echo e(route('admin.currency.exchange.api.config')); ?>" method="post"
                          class="needs-validation base-form">
                        <?php echo csrf_field(); ?>
                        <div class="row my-3">
                            <div class="form-group col-sm-4 col-12">
                                <label for="currency_layer_access_key"><?php echo app('translator')->get('Currency Layer Access Key'); ?></label>
                                <input type="text" name="currency_layer_access_key"
                                       value="<?php echo e(old('currency_layer_access_key',$basicControl->currency_layer_access_key)); ?>"
                                       placeholder="<?php echo app('translator')->get('Enter your currency layer access key'); ?>"
                                       class="form-control <?php $__errorArgs = ['currency_layer_access_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['currency_layer_access_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger"><?php echo e(trans($message)); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="form-group col-sm-4 col-12">
                                <label for="currency_layer_auto_update_at"><?php echo app('translator')->get('Select Update Time'); ?></label>
                                <select name="currency_layer_auto_update_at" id="update_time_currency_layer"
                                        class="form-control <?php $__errorArgs = ['how_many_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__currentLoopData = config('basic.schedule_list'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                            value="<?php echo e($key); ?>" <?php echo e($key == old('currency_layer_auto_update_at',$basicControl->currency_layer_auto_update_at) ? 'selected' : ''); ?>> <?php echo app('translator')->get($value); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['currency_layer_auto_update_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger"><?php echo e(trans($message)); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>


                            <div class="form-group col-sm-4  col-12">
                                <label for="currency_layer_auto_update"><?php echo app('translator')->get('Auto Update Currency Rate'); ?></label>
                                <div class="custom-switch-btn">
                                    <input type='hidden' value='1' name='currency_layer_auto_update'>
                                    <input type="checkbox" name="currency_layer_auto_update"
                                           class="custom-switch-checkbox"
                                           id="currency_layer_auto_update"
                                           value="0" <?php echo e(old('currency_layer_auto_update', $basicControl->currency_layer_auto_update) == 0 ? 'checked' : ''); ?> >
                                    <label class="custom-switch-checkbox-label" for="currency_layer_auto_update">
                                        <span class="custom-switch-checkbox-inner"></span>
                                        <span class="custom-switch-checkbox-switch"></span>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="row align-items-center justify-content-between mb-3">
                            <div class="col-md-6">
                                <h5 class="card-title  font-weight-bold color-primary"><?php echo app('translator')->get('CoinMarketCap Api Config (Crypto Currency)'); ?></h5>
                            </div>
                        </div>


                        <div class="row my-3">
                            <div class="form-group col-sm-4 col-12">
                                <label for="coin_market_cap_app_key"><?php echo app('translator')->get('Coin Market Cap App Key'); ?></label>
                                <input type="text" name="coin_market_cap_app_key"
                                       value="<?php echo e(old('coin_market_cap_app_key',$basicControl->coin_market_cap_app_key)); ?>"
                                       placeholder="<?php echo app('translator')->get('Enter your coin market cap app key'); ?>"
                                       class="form-control <?php $__errorArgs = ['coin_market_cap_app_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['coin_market_cap_app_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger"><?php echo e(trans($message)); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="form-group col-sm-4 col-12">
                                <label for="coin_market_cap_auto_update_at"><?php echo app('translator')->get('Select Update Time'); ?></label>
                                <select name="coin_market_cap_auto_update_at" id="update_time_coin_market_cap"
                                        class="form-control <?php $__errorArgs = ['how_many_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__currentLoopData = config('basic.schedule_list'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                            value="<?php echo e($key); ?>" <?php echo e($key == old('coin_market_cap_auto_update_at',$basicControl->coin_market_cap_auto_update_at) ? 'selected' : ''); ?>> <?php echo app('translator')->get($value); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['coin_market_cap_auto_update_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger"><?php echo e(trans($message)); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>



                            <div class="form-group col-sm-4  col-12">
                                <label for="coin_market_cap_auto_update"><?php echo app('translator')->get('Auto Update Currency Rate'); ?></label>
                                <div class="custom-switch-btn">
                                    <input type='hidden' value='1' name='coin_market_cap_auto_update'>
                                    <input type="checkbox" name="coin_market_cap_auto_update" class="custom-switch-checkbox"
                                           id="coin_market_cap_auto_update"
                                           value="0" <?php echo e(old('coin_market_cap_auto_update', $basicControl->coin_market_cap_auto_update) == 0 ? 'checked' : ''); ?>>
                                    <label class="custom-switch-checkbox-label" for="coin_market_cap_auto_update">
                                        <span class="custom-switch-checkbox-inner"></span>
                                        <span class="custom-switch-checkbox-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn waves-effect waves-light btn-rounded btn-primary btn-block mt-3"><span><i
                                    class="fas fa-save pr-2"></i> <?php echo app('translator')->get('Save Changes'); ?></span></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card card-primary m-0 m-md-4 my-4 m-md-0 shadow">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between mb-3">
                        <div class="col-md-6">
                            <h5 class="card-title  font-weight-bold color-primary"><?php echo app('translator')->get('Currency Layer Instructions'); ?></h5>
                        </div>
                    </div>

                    <p>
                        <?php echo app('translator')->get('Currencylayer provides a simple REST API with real-time and historical exchange rates for 168 world currencies, delivering currency pairs in universally usable JSON format - compatible with any of your applications.
                    <br><br>
                    Spot exchange rate data is retrieved from several major forex data providers in real-time, validated, processed and delivered hourly, Every 10 minutes, or even within the 60-second market window.'); ?>
                        <a href="https://currencylayer.com/product"
                           target="_blank"><?php echo app('translator')->get('Create an account'); ?> <i class="fas fa-external-link-alt"></i></a>
                    </p>

                    <div class="row align-items-center justify-content-between mb-3 mt-5">
                        <div class="col-md-6">
                            <h5 class="card-title  font-weight-bold color-primary"><?php echo app('translator')->get('Coin Market Cap Instructions'); ?></h5>
                        </div>
                    </div>

                    <p>
                        <?php echo app('translator')->get('CoinMarketCap is the world\'s most-referenced price-tracking website for cryptoassets in the rapidly growing cryptocurrency space.
												Its mission is to make crypto discoverable and efficient globally by empowering retail users with unbiased,
												high quality and accurate information for drawing their own informed conclusions.
												Get your free API keys'); ?>
                        <a href="https://coinmarketcap.com/"
                           target="_blank"><?php echo app('translator')->get('Create an account'); ?> <i class="fas fa-external-link-alt"></i></a>
                    </p>

                </div>
            </div>
        </div>
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        'use strict';
        $("#update_time_coin_market_cap").select2({
            selectOnClose: true,
            width: '100%'
        })
        $("#update_time_currency_layer").select2({
            selectOnClose: true,
            width: '100%'
        })
    </script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/starpvfh/public_html/resources/views/admin/plugin_panel/currencyExchangeApiConfig.blade.php ENDPATH**/ ?>