<?php
/**
 * GoCardless Capture Request
 */

namespace Omnipay\GoCardless\Message;

use Omnipay\GoCardless\Gateway;

/**
 *
 *
 *
 *
 * @see AuthorizeRequest
 */
class CaptureRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('amount', 'transactionReference');

        $data = array();

        $bill = array();
        $bill['amount'] = $this->getAmount();
        $bill['pre_authorization_id'] = $this->getTransactionReference();
        $bill['name'] = $this->getDescription();
        $bill['charge_customer_at'] = $this->getChargeCustomerAt();

        $data['bill'] = $bill;

        return $data;
    }

    public function sendData($data){
        $httpRequest = $this->httpClient->post(
            $this->getEndpoint().'/v1/bills',
            array('Accept' => 'application/json'),
            Gateway::generateQueryString($data)
        );

        $httpResponse = $httpRequest->setHeader($this->getAppId(), $this->getAppSecret())->send();

        return $this->response = new CaptureResponse($this, $httpResponse->json());
    }
}