<?php
namespace Paypal;

class CheckoutManager
{

    private $paypalUrl = '';
    private $paypalApiUrl = '';
    private $paypalParams = array();
    private $transferData = array();

    public function __construct($mode, $user, $password, $signature, $localcode = 'EN')
    {

        if ($mode == 'sandbox')
        {
            $this->paypalUrl = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
            $this->paypalApiUrl = 'https://api-3t.sandbox.paypal.com/nvp?';
            $this->paypalParams['VERSION'] = 56.0;
        }
        else
        {
            $this->paypalUrl = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
            $this->paypalApiUrl = 'https://api-3t.paypal.com/nvp?';
            $this->paypalParams['VERSION'] = 56.0;
        }

        $this->paypalParams['USER'] = $user;
        $this->paypalParams['PWD'] = $password;
        $this->paypalParams['SIGNATURE'] = $signature;

        $this->paypalParams["LOCALECODE"] = $localcode;
    }

    public function requestExpressCheckout($price, $currency, $desc, $image, $cancelURL, $returnURL)
    {
        $this->paypalParams['METHOD'] = 'SetExpressCheckout';

        $this->paypalParams['AMT'] = $price;

        $this->paypalParams['CURRENCYCODE'] = $currency;

        $this->paypalParams['DESC'] = $desc;

        $this->paypalParams['HDRIMG'] = $image;

        $this->paypalParams['CANCELURL'] = $cancelURL;

        $this->paypalParams['RETURNURL'] = $returnURL;

        return $this;
    }

    public function requestExpressCheckoutPayment($token, $payerID)
    {
        $this->paypalParams['TOKEN'] = $token;

        $this->requestExpressCheckoutDetails($token)->execute(function($result) use ($token) {
            $this->paypalParams['AMT'] = $result['AMT'];
            $this->paypalUser = $result;
        });

        $this->paypalParams['METHOD'] = 'DoExpressCheckoutPayment';

        $this->paypalParams['PAYMENTACTION'] = 'sale';

        $this->paypalParams['PayerID'] = $payerID;

        return $this;
    }

    public function requestExpressCheckoutDetails($token)
    {
        $this->paypalParams['METHOD'] = 'GetExpressCheckoutDetails';

        $this->paypalParams['TOKEN'] = $token;

        return $this;
    }

    public function setTransferData($transferData = array())
    {
        $this->transferData = $transferData;

        return $this;
    }

    public function execute($successCallback = null, $errorCallback = null)
    {

        $ch = curl_init($this->paypalApiUrl . http_build_query($this->paypalParams));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $paypalResult = array();

        parse_str(curl_exec($ch), $paypalResult);

        if ($paypalResult['ACK'] == 'Success')
        {
            if ($this->paypalParams['METHOD'] == 'SetExpressCheckout')
            {
                if (session_status() != PHP_SESSION_NONE) 
                {
                    $_SESSION['paypal_' . $paypalResult['TOKEN']] = $this->transferData;
                }

                $successCallback($this->paypalUrl . $paypalResult['TOKEN'], $paypalResult);
            }
            else if ($this->paypalParams['METHOD'] == 'DoExpressCheckoutPayment')
            {
                if (session_status() == PHP_SESSION_NONE) 
                {
                    $transferData = array();
                }
                else
                {
                    $transferData = $_SESSION['paypal_' . $paypalResult['TOKEN']];
                }

                $successCallback($transferData, $this->paypalUser, $paypalResult);
            }
            else
            {
                $successCallback($paypalResult);
            }
        }
        else
        {
            $errorCallback($paypalResult);
        }

        curl_close($ch);

        return $this;
    }

}