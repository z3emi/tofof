<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();
App::setLocale('en');
echo __('common.currency').'|'.__('pages.payment_methods_heading').'|'.__('shop.sub').'|'.__('pages.delivery_heading').PHP_EOL;
App::setLocale('ar');
echo __('common.currency').'|'.__('pages.payment_methods_heading').'|'.__('shop.sub').'|'.__('pages.delivery_heading').PHP_EOL;
