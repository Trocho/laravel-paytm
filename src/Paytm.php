<?php namespace Trocho\LaravelPaytm;

use Princealikhan\PaytmPayment\Factories\PaytmFactory;
use Illuminate\Support\Facades\Config;

class Paytm extends PaytmFactory
{

    function __construct()
    {
        $config = Config::get('paytm');

        $env = $config['default'];

        if ($env == 'sandbox') {
            $this->refund = 'https://pguat.paytm.com/oltp/HANDLER_INTERNAL/REFUND';
            $this->txnStatus = 'https://pguat.paytm.com/oltp/HANDLER_INTERNAL/TXNSTATUS';
            $this->txnUrl = 'https://pguat.paytm.com/oltp-web/processTransaction';

        } else {

            $this->refund = 'https://secure.paytm.in/oltp/HANDLER_INTERNAL/REFUND';
            $this->txnStatus = 'https://secure.paytm.in/oltp/HANDLER_INTERNAL/TXNSTATUS';
            $this->txnUrl = 'https://secure.paytm.in/oltp-web/processTransaction';
        }

        $this->orderPrefix = Config::get('paytm.order_prefix');
        $this->callback = Config::get('paytm.callback_url');
        $this->channel = Config::get('paytm.channel');
        $this->industry = Config::get('paytm.industry_type');
        $this->website = Config::get('paytm.website');
        $this->merchantKey = Config::get('paytm.connections.' . $env . '.merchant_key');
        $this->merchantMid = Config::get('paytm.connections.' . $env . '.merchant_mid');
    }

    /**
     * Generate the list of attributes necessary to the integration via form
     *
     * @param $requestParamList
     * @return array
     */
    public function pay($requestParamList)
    {
        $parameters = [
            'MID' => $this->merchantMid,
            'ORDER_ID' => parent::orderID($this->orderPrefix),
            'INDUSTRY_TYPE_ID' => $this->industry,
            'CHANNEL_ID' => $this->channel,
            'WEBSITE' => $this->website,
            'CALLBACK_URL' => $this->callback,
        ];

        return [
            'url' => $this->txnUrl,
            'parameters' => $parameters,
            'token' => parent::getChecksumFromArray($requestParamList, $this->merchantKey),
        ];
    }

    /**
     * Verify the payment after callback
     *
     * @param $requestParamList
     * @return array
     */
    public function verifyPayment($requestParamList)
    {
        $verfication = parent::verifychecksum_e($requestParamList, $this->merchantKey,
            $requestParamList['CHECKSUMHASH']);

        if ($verfication === true) {
            if ($requestParamList["STATUS"] == "TXN_SUCCESS") {
                return [
                    'status' => 'success',
                    'data' => $requestParamList,
                ];
            } else {
                return [
                    'status' => 'error',
                    'data' => null,
                ];
            }
        } else {
            return [
                'status' => 'error',
                'data' => null,
                'message' => 'Checksum mismatched.',
            ];
        }
    }

    /**
     * Get transacion status via order id
     *
     * @param $orderID
     * @return array|mixed
     */
    public function transactionStatus($orderID)
    {
        $requestParamList = array("MID" => $this->merchantMid, "ORDERID" => $orderID);
        return parent::callAPI($this->txnStatus, $requestParamList);
    }

    /**
     * Refund or cancel a transaction
     *
     * @param $orderID
     * @param $amount
     * @param string $txnType
     * @return array|mixed
     */
    public function initiateTransactionRefund($orderID, $amount, $txnType = 'REFUND')
    {
        $requestParamList = array();

        $tranStatus = self::transactionStatus($orderID);
        $requestParamList['MID'] = $this->merchantMid;
        $requestParamList["TXNID"] = $tranStatus['TXNID'];
        $requestParamList["ORDERID"] = $orderID;
        $requestParamList["REFUNDAMOUNT"] = $amount;
        $requestParamList["TXNTYPE"] = $txnType; //REFUND || CANCEL

        $CHECKSUM = parent::getChecksumFromArray($requestParamList, $this->merchantKey, 0);
        $requestParamList["CHECKSUM"] = $CHECKSUM;

        return self::callAPI($this->refund, $requestParamList);
    }
}