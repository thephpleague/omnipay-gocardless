<?php

namespace Omnipay\GoCardless\Message;

/**
 * GoCardless Authorize Request
 */
class AuthorizeRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('amount', 'intervalLength', 'intervalUnit', 'returnUrl');

        $data = array();
        //Required Items from the API
        $data['client_id'] = $this->getAppId();
        $data['nonce'] = $this->generateNonce();
        $data['timestamp'] = gmdate('Y-m-d\TH:i:s\Z');

        $data['pre_authorization']['max_amount'] = $this->getAmount();
        $data['pre_authorization']['interval_length'] = $this->getIntervalLength();
        $data['pre_authorization']['interval_unit'] = $this->getIntervalUnit();

        //Nice to haves.
        $data['cancel_uri'] = $this->getCancelUrl();
        $data['redirect_uri'] = $this->getReturnUrl();
        $data['state'] = $this->getState();
        $data['name'] = $this->getDescription();
        $data['pre_authorization']['calendar_intervals'] = $this->getCalendarInterval();
        $data['pre_authorization']['setup_fee'] = $this->getSetupFee();
        $data['pre_authorization']['interval_count'] = $this->getIntervalCount();
        $data['pre_authorization']['expires_at'] = $this->getPreAuthExpire();

        if ($this->getCard()) {
            $data['bill']['user'] = array();
            $data['bill']['user']['first_name'] = $this->getCard()->getFirstName();
            $data['bill']['user']['last_name'] = $this->getCard()->getLastName();
            $data['bill']['user']['email'] = $this->getCard()->getEmail();
            $data['bill']['user']['billing_address1'] = $this->getCard()->getAddress1();
            $data['bill']['user']['billing_address2'] = $this->getCard()->getAddress2();
            $data['bill']['user']['billing_town'] = $this->getCard()->getCity();
            $data['bill']['user']['billing_county'] = $this->getCard()->getCountry();
            $data['bill']['user']['billing_postcode'] = $this->getCard()->getPostcode();
        }

        $data['signature'] = $this->generateSignature($data);

        return $data;

    }

    public function sendData($data)
    {
        return $this->response = new AuthorizeResponse($this, $data);
    }

}