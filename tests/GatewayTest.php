<?php

namespace Omnipay\GoCardless;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId('abc');
        $this->gateway->setAppSecret('123');

        $this->options = array(
            'amount' => '10.00',
            'returnUrl' => 'https://www.example.com/return',
        );
    }

    public function testPurchase()
    {
        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\GoCardless\Message\PurchaseResponse', $response);
        $this->assertTrue($response->isRedirect());
        $this->assertStringStartsWith('https://gocardless.com/connect/bills/new?', $response->getRedirectUrl());
    }

    public function testCompletePurchaseSuccess()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'resource_uri' => 'a',
                'resource_id' => 'b',
                'resource_type' => 'c',
                'signature' => '416f52e7d287dab49fa8445c1cd0957ca8ddf1c04a6300e00117dc0bedabc7d7',
            )
        );

        $this->setMockHttpResponse('CompletePurchaseSuccess.txt');

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('b', $response->getTransactionReference());
    }

    public function testCompletePurchaseError()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'resource_uri' => 'a',
                'resource_id' => 'b',
                'resource_type' => 'c',
                'signature' => '416f52e7d287dab49fa8445c1cd0957ca8ddf1c04a6300e00117dc0bedabc7d7',
            )
        );

        $this->setMockHttpResponse('CompletePurchaseFailure.txt');

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('The resource cannot be confirmed', $response->getMessage());
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidResponseException
     */
    public function testCompletePurchaseInvalid()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'resource_uri' => 'a',
                'resource_id' => 'b',
                'resource_type' => 'c',
                'signature' => 'd',
            )
        );

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    public function testAuthorization()
    {
        $response = $this->gateway->authorize($this->options);

        $this->assertInstanceOf('Omnipay\GoCardless\Message\AuthorizeRequest', $response);

    }

    public function testCapture()
    {
        $response = $this->gateway->capture(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\GoCardless\Message\CaptureRequest', $response);
    }

    public function testCaptureSuccess()
    {
        $params = array(
            'amount' => '10.00',
            'transactionReference' => 'abc',
            'description' => 'fyi'
        );
        $transaction = $this->gateway->capture($params);
        $transaction->setChargeCustomerAt('2015-12-12');

        $this->setMockHttpResponse('CapturePaymentSuccess.txt');

        $response = $transaction->send();

        $this->assertInstanceOf('Omnipay\GoCardless\Message\CaptureResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());
    }

    public function testCaptureFailure()
    {
        $params = array(
            'amount' => '10.00',
            'transactionReference' => '12v'
        );
        $this->setMockHttpResponse('CapturePaymentFailure.txt');
        $response = $this->gateway->capture($params)->send();

        $this->assertInstanceOf('Omnipay\GoCardless\Message\CaptureResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('The authorization cannot be found', $response->getMessage());
    }

}
