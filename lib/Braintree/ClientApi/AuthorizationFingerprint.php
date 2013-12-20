<?php

class Braintree_AuthorizationFingerprint
{

    public static function generate($params=array())
    {

        self::conditionallyVerifyKeys($params);
        $datetime = new DateTime();
        $defaults = array(
            "merchant_id" => Braintree_Configuration::MerchantId(),
            "public_key" => Braintree_Configuration::PublicKey(),
            "created_at" => $datetime->format('c'),
            "client_api_url" => Braintree_Configuration::merchantUrl() . "/client_api",
            "auth_url" => Braintree_Configuration::authUrl()
        );

        if (array_key_exists("customerId", $params)) {
            $params["customer_id"] = $params["customerId"];
            unset($params["customerId"]);
        }

        if (array_key_exists("makeDefault", $params)) {
            $params["credit_card[options][make_default]"] = $params["makeDefault"];
            unset($params["makeDefault"]);
        }

        if (array_key_exists("verifyCard", $params)) {
            $params["credit_card[options][verify_card]"] = $params["verifyCard"];
            unset($params["verifyCard"]);
        }

        if (array_key_exists("failOnDuplicatePaymentMethod", $params)) {
            $params["credit_card[options][fail_on_duplicate_payment_method]"] = $params["failOnDuplicatePaymentMethod"];
            unset($params["failOnDuplicatePaymentMethod"]);
        }

        $payload = array_merge($params, $defaults);
        $payloadArray = array();
        foreach($payload as $key => $value) {
            $payloadArray[] = "$key=$value";
        }
        $signatureService = new Braintree_SignatureService(
            Braintree_Configuration::privateKey(),
            "Braintree_Digest::hexDigestSha256"
        );
        return $signatureService->sign(join("&", $payloadArray));
    }

    public static function conditionallyVerifyKeys($params)
    {
        if (array_key_exists("customerId", $params)) {
            Braintree_Util::verifyKeys(self::generateWithCustomerIdSignature(), $params);
        } else {
            Braintree_Util::verifyKeys(self::generateWithoutCustomerIdSignature(), $params);
        }
    }

    public static function generateWithCustomerIdSignature()
    {
        return array( "customerId", "makeDefault", "verifyCard", "failOnDuplicatePaymentMethod" );
    }

    public static function generateWithoutCustomerIdSignature()
    {
        return array();

    }
}
