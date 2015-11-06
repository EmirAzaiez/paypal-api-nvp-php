<?php

//This is just an exemple, it's better if for each "function" you do a route

require 'vendor/autoload.php';

use Paypal\CheckoutManager;

session_start();

$paypalConfig = array(
    'mode' => 'sandbox', // You can provide 'prod' for use the real paypal service
    'user' => '', // You can get it from paypal service
    'password' => '', // You can get it from paypal service
    'signature' => '', // You can get it from paypal service
    'lang' => 'EN'
);

$productConfig = array(
    'price' => 5,
    'currency' => 'USD',
    'description' => 'My app subscription for 1 month', //Which will be show on the paypal payment page
    'logo' => 'http://creativebits.org/files/500px-Apple_Computer_Logo.svg_.png', //Logo of your company
    'returnURL' => 'http://myweb.com/paypal/return', //The return url will be passed to paypal, this url will be call by paypal when the transfere will be done
    'cancelURL' => 'http://myweb.com/paypal/cancel', //The return url will be passed to paypal, this url will be call when the user cancel the payment
    'transferData' => array('productID' => '1', 'quantity' => '1month') //Those data arn't used by paypal, you can put whatever you want. Those data will be avaible on success of the "return url" called by paypal (You can only use this if you start the session)
);

if (isset($_GET['token']) && isset($_GET['PayerID']))
{
    $paypal = new CheckoutManager($paypalConfig['mode'], $paypalConfig['user'], $paypalConfig['password'], $paypalConfig['signature'], $paypalConfig['lang']);

    $paypal->requestExpressCheckoutPayment($_GET['token'], $_GET['PayerID'])
           ->execute(
            function($transferedData, $paypalUser, $success) {
                var_dump($transferedData);
                var_dump($paypalUser);
                var_dump($success);
            }, 
            function($errors) {
                var_dump($errors);
            });
}
else if (isset($_GET['token']))
{
    $paypal = new CheckoutManager($paypalConfig['mode'], $paypalConfig['user'], $paypalConfig['password'], $paypalConfig['signature'], $paypalConfig['lang']);

    $paypal->requestExpressCheckoutDetails($_GET['token'])
           ->execute(
            function($transferedData, $paypalUser) {
                var_dump($transferedData);
                var_dump($paypalUser);
            }, 
            function($errors) {
                var_dump($errors);
            });
}
else
{
    $paypal = new CheckoutManager($paypalConfig['mode'], $paypalConfig['user'], $paypalConfig['password'], $paypalConfig['signature'], $paypalConfig['lang']);

    $paypal->requestExpressCheckout($productConfig['price'], $productConfig['currency'], $productConfig['description'], $productConfig['logo'], $productConfig['returnURL'], $productConfig['cancelURL'])
           ->setTransferData($productConfig['transferData'])
           ->execute(
            function($paypalCheckoutURL,$success) {
                header("Location: " . $paypalCheckoutURL);
            },
            function($errors) {
                var_dump($errors);
            });
}