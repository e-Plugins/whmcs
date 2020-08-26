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
function digiwallet_giropay_MetaData()
{
    return array(
        'DisplayName' => 'Digiwallet - Giropay',
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
function digiwallet_giropay_config()
{
    $dwLang = digiwalletPayment::dwLoadLanguage();
    
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => sprintf($dwLang['digiwallet']['default_method_name'], 'Giropay'),
        ),
        'rtlo' => array(
            'FriendlyName' => $dwLang['digiwallet']['rtlo'],
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => $dwLang['digiwallet']['rtlo_description'],
        ),
        'token' => array(
            'FriendlyName' => $dwLang['digiwallet']['token'],
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
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
function digiwallet_giropay_link($params)
{
    $digiwallet = new digiwalletPayment($params);

    return $digiwallet->processPayment();
}

add_hook('ShoppingCartValidateCheckout', 1, function($vars) {
    $dwLang = digiwalletPayment::dwLoadLanguage();
    $gatewayModuleName = basename(__FILE__, '.php');
    if($vars['paymentmethod'] == $gatewayModuleName) {
        $gatewayParams = getGatewayVariables($gatewayModuleName);
        if (empty($gatewayParams['rtlo'])) {
            return [
                sprintf($dwLang['digiwallet']['error_missing_rtlo'], 'Giropay'),
            ];
        }
        if (empty($gatewayParams['token'])) {
            return [
                sprintf($dwLang['digiwallet']['error_missing_token'], 'Giropay'),
            ];
        }
    }
});