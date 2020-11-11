<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/28/16
 * Time: 6:44 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Braintree\Gateway\Request;


use Magento\Payment\Gateway\Helper\SubjectReader;

class PaymentDataBuilder
{

    public function aroundBuild(\Magento\Braintree\Gateway\Request\PaymentDataBuilder $paymentDataBuilder, callable $proceed, array $buildSubject) {
        $result = $proceed($buildSubject);

        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();
        if($paymentToken) {
            $gatewayToken = $paymentToken->getGatewayToken();
            if($gatewayToken) {
                unset($result[\Magento\Braintree\Gateway\Request\PaymentDataBuilder::PAYMENT_METHOD_NONCE]);
                $result['paymentMethodToken'] = $gatewayToken;
            }
        }

        return $result;
    }

}