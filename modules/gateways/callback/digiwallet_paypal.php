<?php
/**
 * WHMCS Digiwallet Paypal Callback File
 * 
 * @copyright Copyright (c) WHMCS Limited 2020
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */
use WHMCS\Database\Capsule;

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/../digiwallet/digiwalletPayment.php';

$dwLang = digiwalletPayment::dwLoadLanguage();

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    throw new Exception($dwLang['digiwallet']['error_module_not_activated']);
}
$isReport = $_SERVER['REQUEST_METHOD'] === 'POST' ? true :false;
$invoiceId = $_REQUEST["invoice_id"];
$transactionId = $isReport ? $_REQUEST['acquirerID'] : $_REQUEST['paypalid'];
if (!$invoiceId || !$transactionId) {
    throw new Exception($dwLang['digiwallet']['bad_request']);
}
$digiwalletData = Capsule::table('mod_digiwallet')->where([
    ['invoice_id', $invoiceId],
    ['transaction_id', $transactionId],
])->first();
if (!$digiwalletData) {
    throw new Exception($dwLang['digiwallet']['bad_request']);
}
$redirect = $_GET['return_redirect'];
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
$cbTrans= Capsule::table('tblaccounts')->where([
    ['invoiceid', $invoiceId],
    ['transid', $transactionId],
])->first();
$invoice = digiwalletPayment::getInvoiceById($invoiceId);
if (empty($cbTrans)) {
    $digiwallet = new digiwalletPayment($gatewayParams);
    $result = $digiwallet->processCallback($transactionId);
    logTransaction($gatewayParams['name'], array_merge($_POST, $_GET), $result['message']);
    $paymentFee = null;
    if ($result['error'] == 0) {
        addInvoicePayment(
            $invoiceId,
            $transactionId,
            $result['paymentAmount'],
            $paymentFee,
            $gatewayModuleName
            );
    }
    if ($isReport) {
        echo $result['message'] . PHP_EOL;
        $invoiceNew = digiwalletPayment::getInvoiceById($invoiceId);
        echo "Old:{$invoice->status}, New:{$invoiceNew->status}";
    }
} else {
    echo sprintf($dwLang['digiwallet']['invoice_paid'], $invoiceId);
}
if ($redirect) {
    if ($result['error']) {
        $redirect .= '&dwError=true';
    }
    header("Location: {$redirect}");exit();
}