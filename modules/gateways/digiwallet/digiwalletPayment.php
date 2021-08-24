<?php
use WHMCS\Database\Capsule;
use Digiwallet\Packages\Transaction\Client\Client;
use Digiwallet\Packages\Transaction\Client\Request\CreateTransaction;
use Digiwallet\Packages\Transaction\Client\Request\CheckTransaction;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/../digiwallet/digiwallet.class.php';
require_once 'vendor/guzzlehttp/guzzle/src/functions.php';
require_once 'vendor/guzzlehttp/promises/src/functions.php';
require_once 'vendor/guzzlehttp/psr7/src/functions.php';
require_once 'vendor/autoload.php';

class digiwalletPayment
{
    const DIGIWALLET_API = "https://api.digiwallet.nl/";
    const DIGIWALLET_CURRENCY = 'EUR';
    public $params;
    public $paymentMethod;
    private $salt = 'e381277';
    
    public function __construct($params)
    {
        $this->paymentMethod = $this->getPaymentMethodCode($params['paymentmethod']);
        $this->params = $params;
    }
    
    public function getPaymentMethodCode($paymentMethod)
    {
        switch ($paymentMethod) {
            case "digiwallet_afterpay":
                return 'AFP';
                break;
            case "digiwallet_bancontact":
                return 'MRC';
                break;
            case "digiwallet_bankwire":
                return 'BW';
                break;
            case "digiwallet_creditcard":
                return 'CC';
                break;
            case "digiwallet_paypal":
                return 'PYP';
                break;
            case "digiwallet_paysafecard":
                return 'WAL';
                break;
            case "digiwallet_sofort":
                return 'DEB';
                break;
            case "digiwallet_eps":
                return 'EPS';
                break;
            case "digiwallet_giropay":
                return 'GIP';
                break;
            case "digiwallet_ideal":
            default:
                return 'IDE';
        }
    }
    /**
     * Process payment via Digiwallet api
     *
     * @return string
     */
    public function processPayment()
    {
        $dwLang = self::dwLoadLanguage();
        //check table if not exits 
        $this->createDigiwalletTable();
        // Invoice Parameters
        $invoiceId = $this->params['invoiceid'];
        $amount = $this->params['amount'];
        
        // Client Parameters
        $email = $this->params['clientdetails']['email'];
        
        // System Parameters
        $systemUrl = $this->params['systemurl'];
        $returnUrl = $this->params['returnurl'];
        $langPayNow = $this->params['langpaynow'];
        $moduleName = $this->params['paymentmethod'];
        $this->updateOrder($invoiceId, $moduleName);
        $htmlOutput = '';
        if ($this->paymentMethod == 'BW') {
            $digiwalletData = Capsule::table('mod_digiwallet')->where([
                ['invoice_id', $invoiceId],
                ['payment_method', $this->paymentMethod],
            ])->first();
            if ($digiwalletData) {
                list($trxid, $accountNumber, $iban, $bic, $beneficiary, $bank) = explode("|", $digiwalletData->bw_data);
                $htmlOutput= '<div class="bankwire-info">
                        <h4>' . $dwLang['digiwallet']['bw_instruction_1'] . '</h4>
                        <p>' . $dwLang['digiwallet']['bw_instruction_2'] . 
                        '<br>' . sprintf($dwLang['digiwallet']['bw_instruction_3'], htmlspecialchars($amount), htmlspecialchars($iban), htmlspecialchars($beneficiary)) . '</p>
                        <p>' . sprintf($dwLang['digiwallet']['bw_instruction_4'], htmlspecialchars($trxid), htmlspecialchars($email)) . '</p>
                        <p>' . sprintf($dwLang['digiwallet']['bw_instruction_5'], htmlspecialchars($bic), htmlspecialchars($bank)) . '<p>
                            ' . $dwLang['digiwallet']['bw_instruction_6'] . '</p>
                   </div>';
                return $htmlOutput;
            }
        }
        $postfields = array();
        $postfields['invoiceid'] = $invoiceId;
        $postfields['moduleName'] = $moduleName;
        $postfields['returnurl'] = $returnUrl;
        $postfields['clientdetails[firstname]'] = $this->params['clientdetails']['firstname'];
        $postfields['clientdetails[lastname]'] = $this->params['clientdetails']['lastname'];
        $postfields['clientdetails[email]'] = $this->params['clientdetails']['email'];
        $postfields['clientdetails[address1]'] = $this->params['clientdetails']['address1'];
        $postfields['clientdetails[address2]'] = $this->params['clientdetails']['address2'];
        $postfields['clientdetails[city]'] = $this->params['clientdetails']['city'];
        $postfields['clientdetails[state]'] = $this->params['clientdetails']['state'];
        $postfields['clientdetails[postcode]'] = $this->params['clientdetails']['postcode'];
        $postfields['clientdetails[country]'] = $this->params['clientdetails']['country'];
        $postfields['clientdetails[phonenumber]'] = $this->params['clientdetails']['phonenumber'];
        $htmlOutput .= '<form method="post" action="' . $systemUrl . '/modules/gateways/digiwallet/process_payment.php?invoice_id=' . $invoiceId . '">';
        foreach ($postfields as $k => $v) {
            $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . urlencode($v) . '" />';
        }
        $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
        $htmlOutput .= '</form>';
        
        return $htmlOutput;
    }

    /**
     * Process callback via Digiwallet api
     *
     * @return array
     */
    public function processCallback($trxid)
    {
        $response = array();
        $parametersReport = array();
        $isSuccess = false;
        try {
            if (in_array($this->paymentMethod, ['EPS', 'GIP'])) {
                $digiwalletApi = new Client(self::DIGIWALLET_API);
                $request = new CheckTransaction($digiwalletApi);
                $request->withBearer($this->params['token']);
                $request->withOutlet($this->params['rtlo']);
                $request->withTransactionId($trxid);
                /** @var \Digiwallet\Packages\Transaction\Client\Response\CheckTransaction $apiResult */
                $apiResult = $request->send();
                $apiStatus = $apiResult->getStatus();
                $isSuccess = (0 == $apiStatus && 'Completed' == $apiResult->getTransactionStatus()) ? true : false;
                $errorMessage = $apiResult->getMessage();
            } else {
                $digiwallet = new DigiwalletCore($this->paymentMethod, $this->params['rtlo']);
                if ($this->paymentMethod == 'BW') {
                    $checksum = md5($trxid . $this->params['rtlo'] . $this->salt);
                    $parametersReport['checksum'] = $checksum;
                }
                $digiwallet->checkPayment($trxid, $parametersReport);
                $isSuccess = $digiwallet->getPaidStatus();
                $errorMessage = $digiwallet->getErrorMessage();
            }
        } catch(Exception $e) {
            $response['message'] = $e->getMessage();
        }
        $amountPaid = null;
        if ($isSuccess) {
            if ($this->paymentMethod == 'BW') {
                $consumber_info = $digiwallet->getConsumerInfo();
                if (!empty($consumber_info) && $consumber_info['bw_paid_amount'] > 0) {
                    $amountPaid = number_format($consumber_info['bw_paid_amount'] / 100, 5);
                }
            }
            $response['error'] = 0;
            $response['message'] = 'Success';
        } else {
            $response['error'] = 1;
            $response['message'] = $errorMessage;
        }
        $response['paymentAmount'] = $amountPaid;
        Capsule::table('mod_digiwallet')
        ->where('transaction_id', $trxid)
        ->update(['message' => $response['message']]);
        
        return $response;
    }
    
    /**
     * Process refund via Digiwallet api
     * 
     * @param $dataRefund array
     * @return boolean
     */
    public function processRefund($dataRefund)
    {
        $digiwallet = new DigiwalletCore($this->paymentMethod, $this->params['rtlo']);
        
        $refund = $digiwallet->refund($this->params['token'], $dataRefund);
        
        if ($refund == false) {
            $dataRefund['message'] = $digiwallet->getErrorMessage();
            return $dataRefund;
        }
        return true;
    }
    
    public function startPayment()
    {
        $transactionId = $bankUrl = $message = $result = $moreInformation = null;
        $moduleName = $this->params['paymentmethod'];
        $rtlo = $this->params['rtlo'];
        $token = $this->params['token'];
        $amount = $this->params['amount'];
        $description = 'Order ' . $this->params['invoiceid'];
        $returnUrl = $this->params['systemurl'] . "/modules/gateways/callback/{$moduleName}.php?invoice_id={$this->params['invoiceid']}&return_redirect={$this->params['returnurl']}";
        $reportUrl = $this->params['systemurl'] . "/modules/gateways/callback/{$moduleName}.php?invoice_id={$this->params['invoiceid']}";
        if (in_array($this->paymentMethod, ['EPS', 'GIP'])) {
            $digiwalletApi = new Client(self::DIGIWALLET_API);
            $formParams = [
                'outletId' => $rtlo,
                'currencyCode' => $this->getCurrency(),
                'consumerEmail' => $this->params['clientdetails']['email'],
                'description' => $description,
                'returnUrl' => $returnUrl,
                'reportUrl' => $reportUrl,
                'consumerIp' => $this->getCustomerIP(),
                'suggestedLanguage' => 'NLD',
                'amountChangeable' => false,
                'inputAmount' => $amount * 100,
                'paymentMethods' => [
                    $this->paymentMethod
                ],
                'app_id' => DigiwalletCore::APP_ID,
            ];
            
            $request = new CreateTransaction($digiwalletApi, $formParams);
            $request->withBearer($token);
            /** @var \Digiwallet\Packages\Transaction\Client\Response\CreateTransaction $apiResult */
            try {
                $apiResult = $request->send();
                $result = 0 == $apiResult->status() ? true : false;
                $message = $apiResult->message();
                $transactionId = $apiResult->transactionId();
                $bankUrl = $apiResult->launchUrl();
            } catch(Exception $e) {
                $result = false;
                $message = $e->getMessage();
            }
            
        } else {
            $digiWallet = new DigiWalletCore($this->paymentMethod, $rtlo);
            $digiWallet->setAmount(round($amount * 100));
            $digiWallet->setDescription($description);
            // set return & report
            $digiWallet->setReturnUrl($returnUrl);
            $digiWallet->setReportUrl($reportUrl);
            $digiWallet->bindParam('email', $this->params['clientdetails']['email']);
            $this->additionalParameters($digiWallet);
            
            $result = $digiWallet->startPayment();
            $transactionId = $digiWallet->getTransactionId();
            $bankUrl = $digiWallet->getBankUrl();
            $message = $digiWallet->getErrorMessage();
            $moreInformation = $digiWallet->getMoreInformation();
        }
        
        return [
            'result' => $result,
            'transactionId' => $transactionId,
            'bankUrl' => $bankUrl,
            'message' => $message,
            'moreInformation' => $moreInformation
        ];
    }
    
    public static function formatPhone($country, $phone) {
        $function = 'format_phone_' . strtolower($country);
        if(method_exists(self::class, $function)) {
            return self::$function($phone);
        } else {
            echo "unknown phone formatter for country: ". ($function);
            exit;
        }
        return $phone;
    }
    
    public static function format_phone_nld($phone) {
        // note: making sure we have something
        if(!isset($phone{3})) { return ''; }
        // note: strip out everything but numbers
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $length = strlen($phone);
        switch($length) {
            case 9:
                return "+31".$phone;
                break;
            case 10:
                return "+31".substr($phone, 1);
                break;
            case 11:
            case 12:
                return "+".$phone;
                break;
            default:
                return $phone;
                break;
        }
    }
    
    public static function format_phone_bel($phone) {
        // note: making sure we have something
        if(!isset($phone{3})) { return ''; }
        // note: strip out everything but numbers
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $length = strlen($phone);
        switch($length) {
            case 9:
                return "+32".$phone;
                break;
            case 10:
                return "+32".substr($phone, 1);
                break;
            case 11:
            case 12:
                return "+".$phone;
                break;
            default:
                return $phone;
                break;
        }
    }
    
    private static function breakDownStreet($street) {
        $out = [
            'street' => null,
            'houseNumber' => null,
            'houseNumberAdd' => null,
        ];
        $addressResult = null;
        preg_match("/(?P<address>\D+) (?P<number>\d+) (?P<numberAdd>.*)/", $street, $addressResult);
        if (! $addressResult) {
            preg_match("/(?P<address>\D+) (?P<number>\d+)/", $street, $addressResult);
        }
        if (empty($addressResult)) {
            $out['street'] = $street;
            
            return $out;
        }
        
        $out['street'] = array_key_exists('address', $addressResult) ? $addressResult['address'] : null;
        $out['houseNumber'] = array_key_exists('number', $addressResult) ? $addressResult['number'] : null;
        $out['houseNumberAdd'] = array_key_exists('numberAdd', $addressResult) ? trim(strtoupper($addressResult['numberAdd'])) : null;
        
        return $out;
    }
    
    private function additionalParameters($digiwallet) {
        if ($this->paymentMethod == 'AFP') {
            $invoiceId = $this->params['invoiceid'];
            // Client Parameters
            $firstname = $this->params['clientdetails']['firstname'];
            $lastname = $this->params['clientdetails']['lastname'];
            $email = $this->params['clientdetails']['email'];
            $address1 = $this->params['clientdetails']['address1'];
            $address2 = $this->params['clientdetails']['address2'];
            $city = $this->params['clientdetails']['city'];
            $state = $this->params['clientdetails']['state'];
            $postcode = $this->params['clientdetails']['postcode'];
            $country = $this->params['clientdetails']['country'];
            $phone = $this->params['clientdetails']['phonenumber'];
            $country = (strtoupper($country) == 'BE' ? 'BEL' : 'NLD');
            $streetParts = self::breakDownStreet($address1);
            
            $digiwallet->bindParam('billingstreet', $streetParts['street']);
            $digiwallet->bindParam('billinghousenumber', empty($streetParts['houseNumber'].$streetParts['houseNumberAdd']) ?
                $address1 : $streetParts['houseNumber'] . ' ' . $streetParts['houseNumberAdd']);
            $digiwallet->bindParam('billingpostalcode', $postcode);
            $digiwallet->bindParam('billingcity', $city);
            $digiwallet->bindParam('billingpersonemail', $email);
            $digiwallet->bindParam('billingpersoninitials', "");
            $digiwallet->bindParam('billingpersongender', "");
            $digiwallet->bindParam('billingpersonfirstname', $firstname);
            $digiwallet->bindParam('billingpersonsurname', $lastname);
            $digiwallet->bindParam('billingcountrycode', $country);
            $digiwallet->bindParam('billingpersonlanguagecode', $country);
            $digiwallet->bindParam('billingpersonbirthdate', "");
            $digiwallet->bindParam('billingpersonphonenumber', self::formatPhone($country, $phone));
            
            $streetParts = self::breakDownStreet($address1);
            
            $digiwallet->bindParam('shippingstreet', $streetParts['street']);
            $digiwallet->bindParam(
                'shippinghousenumber',
                empty($streetParts['houseNumber'].$streetParts['houseNumberAdd']) ?
                $address1 : $streetParts['houseNumber'] . ' ' . $streetParts['houseNumberAdd']
                );
            $digiwallet->bindParam('shippingpostalcode', $postcode);
            $digiwallet->bindParam('shippingcity', $city);
            $digiwallet->bindParam('shippingpersonemail', $email);
            $digiwallet->bindParam('shippingpersoninitials', "");
            $digiwallet->bindParam('shippingpersongender', "");
            $digiwallet->bindParam('shippingpersonfirstname', $firstname);
            $digiwallet->bindParam('shippingpersonsurname', $lastname);
            $digiwallet->bindParam('shippingcountrycode', $country);
            $digiwallet->bindParam('shippingpersonlanguagecode', $country);
            $digiwallet->bindParam('shippingpersonbirthdate', "");
            $digiwallet->bindParam(
                'shippingpersonphonenumber',
                self::formatPhone(
                    $country,
                    $phone
                    )
                );
            // Getting the items in the order
            $invoicelines = array();
            $total_amount_by_products = 0;
            // Iterating through each item in the order
            $productDetails = Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceId)->get();
            foreach ($productDetails as $product) {
                $total_amount_by_products += $product->amount;
                $invoicelines[] = array(
                    'productCode' => $product->type,
                    'productDescription' => $product->description,
                    'quantity' => 1,
                    'price' => $product->amount, // Price without tax
                    'taxCategory' => $digiwallet->getTax($product->taxed)
                );
            }
            //Remain invoice line to balance against grand total
            $invoicelines[] = array(
                'productCode' => '000000',
                'productDescription' => "Other fees (shipping, additional fees)",
                'quantity' => 1,
                'price' =>  $this->params["amount"] - $total_amount_by_products,
                'taxCategory' => 3
            );
            
            $digiwallet->bindParam('invoicelines', json_encode($invoicelines));
            $digiwallet->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
        } else if ($this->paymentMethod == 'BW') {
            $digiwallet->bindParam('salt', $this->salt);
            $digiwallet->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
        } else if ($this->paymentMethod == 'DEB') {
            $digiwallet->bindParam('country', 'NL');
        }
    }
    
    private function createDigiwalletTable()
    {
        if (!Capsule::schema()->hasTable('mod_digiwallet')) {
            Capsule::schema()->create('mod_digiwallet', function($table) {
                $table->increments('id');
                $table->integer('invoice_id');
                $table->string('rtlo', 10);
                $table->string('transaction_id', 50);
                $table->float('amount', 8, 2);
                $table->string('payment_method', 10);
                $table->string('bw_data', 1000)->nullable();
                $table->text('message')->nullable();
            });
        }
    }
    
    public static function getInvoiceById($id)
    {
        $invoice = Capsule::table('tblinvoices')->where([
            ['id', $id],
        ])->first();
        return $invoice;
    }
    
    public static function dwLoadLanguage()
    {
        global $CONFIG;
        $userLanguage = $_SESSION['Language'] ? $_SESSION['Language'] : $CONFIG['Language'];
        $langFile =  __DIR__ . "/lang/$userLanguage.php";
        if (!file_exists($langFile)) {
            $langFile = __DIR__ . "/lang/english.php";
        }
        $dwLang = require($langFile);
        return $dwLang;
    }
    
    /***
     * Get user's ip address
     * @return mixed
     */
    public function getCustomerIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    public function getCurrency()
    {
        return self::DIGIWALLET_CURRENCY;
    }
    
    public function updateOrder($invoiceId, $payment)
    {
        return Capsule::table('tblorders')
        ->where('invoiceid', $invoiceId)
        ->update(['paymentmethod' => $payment]);
    }
}
