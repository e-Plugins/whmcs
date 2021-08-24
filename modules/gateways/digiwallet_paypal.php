<?php
/**
 * WHMCS Digiwallet Payment Gateway Module
 *
 * @copyright Copyright (c) WHMCS Limited 2020
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

require_once __DIR__ . '/digiwallet/digiwalletPayment.php';

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function digiwallet_paypal_MetaData()
{
    return array(
        'DisplayName' => 'Digiwallet - Paypal',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * @return array
 */
function digiwallet_paypal_config()
{
    $dwLang = digiwalletPayment::dwLoadLanguage();
    
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => sprintf($dwLang['digiwallet']['default_method_name'], 'Paypal'),
        ),
        'rtlo' => array(
            'FriendlyName' => $dwLang['digiwallet']['rtlo'],
            'Type' => 'text',
            'Size' => '25',
            'Default' => '156187',
            'Description' => $dwLang['digiwallet']['rtlo_description'],
        ),
        'token' => array(
            'FriendlyName' => $dwLang['digiwallet']['token'],
            'Type' => 'text',
            'Size' => '25',
            'Default' => 'bf72755a648832f48f0995454',
            'Description' => $dwLang['digiwallet']['token_description'],
        )
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function digiwallet_paypal_link($params)
{
    $digiwallet = new digiwalletPayment($params);

    return $digiwallet->processPayment();
}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function digiwallet_paypal_refund($params)
{
    // perform API call to initiate refund and interpret result
    $digiwallet = new digiwalletPayment($params);
    $status = 'error';
    $dataRefund = array(
        'paymethodID' => $digiwallet->paymentMethod,
        'transactionID' => $digiwallet->params['transid'],
        'amount' => (int)((float)($digiwallet->params['amount']) * 100),
        'description' => 'OrderId: ' . $digiwallet->params['invoiceid'] . ', Amount: ' . $digiwallet->params['amount'],
        'internalNote' => 'Internal note - OrderId: ' . $digiwallet->params['invoiceid'] . ', Amount: ' . $digiwallet->params['amount'].
        ', Customer Email: ' . $digiwallet->params['clientdetails']['email'],
        'consumerName' => $digiwallet->params['clientdetails']['firstname'] . ' ' . $digiwallet->params['clientdetails']['lastname']
    );
    $result = $digiwallet->processRefund($dataRefund);
    if($result === true) {
        $status = 'success';
    } else {
        $dataRefund = $result;
    }
    
    return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => $status,
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $dataRefund,
        // Unique Transaction ID for the refund transaction
        'transid' => $params['transid'],
    );
}

add_hook('ShoppingCartValidateCheckout', 1, function($vars) {
    $dwLang = digiwalletPayment::dwLoadLanguage();
    $gatewayModuleName = basename(__FILE__, '.php');
    if($vars['paymentmethod'] == $gatewayModuleName) {
        $gatewayParams = getGatewayVariables($gatewayModuleName);
        if (empty($gatewayParams['rtlo'])) {
            return [
                sprintf($dwLang['digiwallet']['error_missing_rtlo'], 'Paypal'),
            ];
        }
    }
});