<?php

namespace App\Libraries\Payments;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers\Utility;
use App\Models\PaymentMethod;
use App\Models\OrderTransaction;
use App\Models\Order;

class OnePayAtmPayment extends Payment
{
    const CODE = 'one_pay_atm';

    public function getCode()
    {
        return self::CODE;
    }

    public function getName($lang = null)
    {
        $names = [
            'en' => 'Payment by ATM card with Internet Banking (via Onepay)',
        ];

        if($lang !== null && isset($names[$lang]))
            return $names[$lang];

        return 'Thanh toán bằng thẻ ATM có đăng ký Internet Banking (qua cổng Onepay)';
    }

    public function getType()
    {
        return PaymentMethod::PAYMENT_TYPE_ATM_ONLINE_DB;
    }

    public function renderView($paymentMethod)
    {
        echo view('libraries.payments.one_pay_atm_form', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    public function validateAndSetData($paymentMethod, $inputs, $validator)
    {
        if(isset($inputs['detail']))
            $paymentMethod->detail = json_encode($inputs['detail']);
        else
            $paymentMethod->detail = null;
    }

    public function handlePlacedOrderPayment($paymentMethod, $order)
    {
        list($merchantId, $accessCode, $hashCode, $paymentUrl) = self::getPaymentIntegrateInformation($paymentMethod);

        $params = [
            'vpc_Version' => 2,
            'vpc_Currency' => 'VND',
            'vpc_Command' => 'pay',
            'vpc_AccessCode' => $accessCode,
            'vpc_Merchant' => $merchantId,
            'vpc_Locale' => (App::getLocale() == 'en' ? 'en' : 'vn'),
            'vpc_ReturnURL' => action('Frontend\OrderController@paymentOrder', ['id' => $order->id]),
            'vpc_MerchTxnRef' => self::generateVpcMerchTxnRef($order),
            'vpc_OrderInfo' => $order->number,
            'vpc_Amount' => ($order->total_price * 100),
            'vpc_TicketNo' => request()->ip(),
        ];

        ksort($params);

        $stringHashData = '';
        $paymentUrl .= '?';

        $i = 1;
        foreach($params as $key => $value)
        {
            if($i > 1)
            {
                $paymentUrl .= '&';
                $stringHashData .= '&';
            }

            $paymentUrl .= urlencode($key) . '=' . urlencode($value);
            $stringHashData .= $key . '=' . $value;

            $i ++;
        }

        $vpcSecureHash = strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*', $hashCode)));

        $paymentUrl .= '&vpc_SecureHash=' . $vpcSecureHash;

        $transaction = new OrderTransaction();
        $transaction->order_id = $order->id;
        $transaction->amount = $order->total_price;
        $transaction->point_amount = 0;
        $transaction->type = Order::PAYMENT_STATUS_PENDING_DB;
        $transaction->created_at = date('Y-m-d H:i:s');
        $transaction->detail = json_encode(array_merge($params, [
            'vpc_SecureHash' => $vpcSecureHash,
            'payment_url_redirect' => $paymentUrl,
        ]));
        $transaction->save();

        return $paymentUrl;
    }

    protected static function generateVpcMerchTxnRef($order)
    {
        return $order->id . time();
    }

    protected static function getPaymentIntegrateInformation($paymentMethod)
    {
        $paymentDetails = json_decode($paymentMethod->detail, true);

        if(isset($paymentDetails['live']) && $paymentDetails['live'] == Utility::ACTIVE_DB)
        {
            return [
                $paymentDetails['merchant_id_live'],
                $paymentDetails['access_code_live'],
                $paymentDetails['hash_code_live'],
                $paymentDetails['payment_url_live'],
            ];
        }
        else
        {
            return [
                $paymentDetails['merchant_id_test'],
                $paymentDetails['access_code_test'],
                $paymentDetails['hash_code_test'],
                $paymentDetails['payment_url_test'],
            ];
        }
    }

    public function handleOrderPaymentResponse($paymentMethod, $order, $params)
    {
        $paid = false;

        list($merchantId, $accessCode, $hashCode, $paymentUrl) = self::getPaymentIntegrateInformation($paymentMethod);

        $vpcSecureHash = strtoupper($params['vpc_SecureHash']);
        unset($params['vpc_SecureHash']);

        $validateSecureHash = false;

        if($params['vpc_TxnResponseCode'] != '7' && $params['vpc_TxnResponseCode'] != 'No Value Returned')
        {
            ksort($params);

            $stringHashData = '';

            $i = 1;
            foreach($params as $key => $value )
            {
                if($i > 1)
                    $stringHashData .= '&';

                $stringHashData .= $key . '=' . $value;

                $i ++;
            }

            $vpcSecureHashCalculated = strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*', $hashCode)));

            if($vpcSecureHash == $vpcSecureHashCalculated)
                $validateSecureHash = true;
        }

        $vpcAmount = (isset($params['vpc_Amount']) ? $params['vpc_Amount'] : '');
        $vpcOrderInfo = (isset($params['vpc_OrderInfo']) ? $params['vpc_OrderInfo'] : '');
        $vpcMerchantID = (isset($params['vpc_Merchant']) ? $params['vpc_Merchant'] : '');
        $vpcMerchTxnRef = (isset($params['vpc_MerchTxnRef']) ? $params['vpc_MerchTxnRef'] : '');
        $vpcTransactionNo = (isset($params['vpc_TransactionNo']) ? $params['vpc_TransactionNo'] : '');
        $vpcTxnResponseCode = (isset($params['vpc_TxnResponseCode']) ? $params['vpc_TxnResponseCode'] : '');

        $details = array();
        foreach($order->orderTransactions as $orderTransaction)
        {
            if($orderTransaction->type == Order::PAYMENT_STATUS_PENDING_DB)
                $details = json_decode($orderTransaction->detail, true);
        }

        if($vpcAmount * 100 != $order->total_price || $vpcOrderInfo != $order->number || $vpcMerchantID != $merchantId || !isset($details['vpc_MerchTxnRef']) || $vpcMerchTxnRef != $details['vpc_MerchTxnRef'] || empty($vpcTransactionNo))
            $validateSecureHash = false;

        if($validateSecureHash == true && $vpcTxnResponseCode == '0')
        {
            $paid = true;

            $order->completePayment(null, false, json_encode(array_merge($params, [
                'vpc_SecureHash' => $vpcSecureHash,
            ])));

            return $paid;
        }
        else if($vpcTxnResponseCode != '0')
        {

            return $paid;
        }
        else if($validateSecureHash == false)
        {


        }
    }
}