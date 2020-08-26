# Client

## Usage

### Create Transaction
```php
$digiwalletApi = new Client('https://api.digiwallet.nl/');

$this->transaction->parsePaymentOptions();

$formParams = [
  'outletId' => $this->transaction->outlet_id,
  'currencyCode' => $this->transaction->currency,
  'consumerEmail' => $this->transaction->email,
  'description' => $this->transaction->description,
  'returnUrl' => Yii::$app->params['selfUrl'][YII_ENV] . static::RETURN_URL . '/' . $this->transaction->id,
  'reportUrl' => Yii::$app->params['selfUrl'][YII_ENV] . static::REPORT_URL . '/' . $this->transaction->id,
  'consumerIp' => Yii::$app->request->userIP,
  'environment' => 0,
  'acquirerPreprodMode' => 0,
  'amountChangeable' => $this->transaction->amountChangeable,
  'inputAmount' => $this->transaction->inputAmount * 100,
  'inputAmountMin' => $this->transaction->inputAmountMin ? $this->transaction->inputAmountMin * 100 : null,
  'inputAmountMax' => $this->transaction->inputAmountMax ? $this->transaction->inputAmountMax * 100 : null,
  'paymentMethods' => $this->transaction->payment_method_code,
  'app_id' => Yii::$app->params['dwApiId'],
];

$request = new CreateTransaction($digiwalletApi, $formParams);
$request->withBearer($this->transaction->organization->api_key);
/** @var CreateTransactionResponse $apiResult */
$apiResult = $request->send();
```

### Check Transaction
```php

$digiwalletApi = new Client('https://api.digiwallet.nl/');
$request = new CheckTransaction($digiwalletApi);
$request->withBearer($this->transaction->organization->api_key);
$request->withOutlet($this->transaction->outlet_id);
$transactionResponse = Json::decode($this->transaction->response);
$request->withTransactionId($transactionResponse['transaction_id']);
/** @var \Digiwallet\Packages\Transaction\Client\Response\CheckTransaction $apiResult */
$apiResult = $request->send();
```
