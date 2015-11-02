# Paypal NVP Api 

This php class will simply consume the paypal NVP Api for make a simple payment.

### Installation

Add this line into require in your composer.json:

```sh
composer require emirazaiez/paypal
```

then add this line to your php file (Or not if you use a framework)
```sh
require 'vendor/autoload.php'
```

### A few examples:
Simple make a call request to the API for get a paypal payment URL : (route can be something like : /paypal/checkout)
```sh
<?php
require 'vendor/autoload.php'

use Paypal\CheckoutManager;

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
    'transferData' => array('productID' => '1', 'quantity' => '1month') //Those data arn't used by paypal, you can put whatever you want. Those data will be avaible on success of the "return url" called by paypal
);

$paypal = new CheckoutManager($paypalConfig['mode'], $paypalConfig['user'], $paypalConfig['password'], $paypalConfig['signature'], $paypalConfig['lang']);

$paypal->requestExpressCheckout($productConfig['price'], $productConfig['currency'], $productConfig['description'], $productConfig['logo'], $productConfig['returnURL'], $productConfig['cancelURL'])
       ->setTransferData($paypalConfig['transferData'])
       ->execute(
        function($paypalCheckoutURL,$success) {
            //Put your own logic here
            //You can redirect the user directly to paypal checkout shop :
            header("Location: " . $paypalCheckoutURL);
        },
        function($errors) {
            //Put your own logic here
        });
```


Simple return of paypal API : (route can be something like : /paypal/return)
```sh
<?php

$paypal = new CheckoutManager($paypalConfig['mode'], $paypalConfig['user'], $paypalConfig['password'], $paypalConfig['signature'], $paypalConfig['lang']);

$paypal->requestExpressCheckoutPayment($_GET['token'], $_GET['PayerID'])
       ->execute(
        function($transferedData, $success) {
            //Put your own logic here (Will be some db call for save everything)
        }, 
        function($errors) {
            //Put your own logic here
        });
```


Cancel payment : (route can be something like : /paypal/cancel)
```sh
<?php

$paypal = new CheckoutManager($paypalConfig['mode'], $paypalConfig['user'], $paypalConfig['password'], $paypalConfig['signature'], $paypalConfig['lang']);

$paypal->requestExpressCheckoutPayment($_GET['token'])
       ->execute(
        function($transferedData, $paypalUserInformation) {
            //Put your own logic here
        }, 
        function($errors) {
            //Put your own logic here
        });
```

### Authors
[Emir Azaiez](https://github.com/EmirAzaiez/)