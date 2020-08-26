<?php
/**
 * WHMCS Digiwallet Process Payment File
 * 
 * @copyright Copyright (c) WHMCS Limited 2020
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */
use WHMCS\Database\Capsule;

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/../digiwallet/digiwallet.class.php';
require_once __DIR__ . '/../digiwallet/digiwalletPayment.php';

$dwLang = digiwalletPayment::dwLoadLanguage();

$invoiceId = $_POST["invoiceid"];
$gatewayModuleName = $_POST["moduleName"];
$returnUrl = urldecode($_POST["returnurl"]);

if (!$invoiceId || !$gatewayModuleName || !$returnUrl) {
    die($dwLang['digiwallet']['bad_request']);
}
// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die($dwLang['digiwallet']['error_module_not_activated']);
}

$invoice = Capsule::table('tblinvoices')->where([
    ['id', $invoiceId],
    ['paymentmethod', $gatewayModuleName],
])->first();
if (empty($invoice)) {
    die($dwLang['digiwallet']['bad_request']);
}
if ($invoice->status == 'paid') {
    header("Location: {$returnUrl}");
    exit();
}

$params = $_POST;
$params['invoiceid'] = $invoiceId;
$params['systemurl'] = $CONFIG['SystemURL'];
$params['returnurl'] = $returnUrl;
$params['rtlo'] = $gatewayParams['rtlo'];
$params['token'] = $gatewayParams['token'];
$params['amount'] = $invoice->total;
$params['paymentmethod'] = $gatewayModuleName;
$params['clientdetails']['email'] = urldecode($params['clientdetails']['email']);
$digiwalletPayment = new digiwalletPayment($params);
$result = $digiwalletPayment->startPayment();

if ($result['result']) {
    Capsule::table('mod_digiwallet')->insert(
        [
            'invoice_id' => $invoiceId,
            'rtlo' => $params['rtlo'],
            'transaction_id' => $result['transactionId'],
            'amount' => $params['amount'],
            'payment_method' => $digiwalletPayment->paymentMethod,
            'bw_data' => $result['moreInformation'],
        ]
    );
    if ($digiwalletPayment->paymentMethod == 'BW') {
        header("Location: {$returnUrl}");
        exit();
    }
    header("Location: {$result['bankUrl']}");
    exit();
}
logActivity($result['message']);
$redirect = $returnUrl . '&error=' . $result['message'];
header("Location: {$redirect}");exit();
