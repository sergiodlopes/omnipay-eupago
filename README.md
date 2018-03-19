# Eupago - euPago integration in omnipay 

**This is the euPago solution for omnipay payment processing library**

Eupago is one Portuguese payment gateway that has multiple methods available.
For use it you need create one account in [euPago](https://www.eupago.pt/) website.
Once installed and configured you are able to use all the features of our [API](https://seguro.eupago.pt/api/).

## Instalation

For instalation details please check the [omnipay](https://github.com/thephpleague/omnipay#installation) git page.


## Implemented Payment Methods

- **Multibanco** - Create MB references with and without start/end date, minimum/maximum amount support (just set the start/end date when creating a payment)
- **MBWay** - MBWay payment system
- **PayShop**
- **Pagaqui**

###### Future implementations:
- **Credit Card**

## Required fields

- 'apiKey' - to generate your api key please [create an account](https://eupago.pt/registo) on their website
- 'currency' - currency is required and must be 'EUR' or '€'
- 'amount' - Purchase amount
- 'transactionId' - usually this is the order ID

## Example 


```php

$gateway = Omnipay::create('Eupago_Multibanco');

//required fields
$gateway->setApiKey('xxx-xxx-xxx-xxx');
$gateway->setCurrency('EUR');
$gateway->setTransactionId('xxxxx');

$response = $gateway->purchase(['amount' => '10.00'])->send();

// return the euPago api response with payment credentials
// see src/message/response.php methods for more information
$paymentData = $response->getData();

// return the Transaction Reference
// the transaction Reference is required for call the status of payment, you should store them in your "orders" table related database
$referenceId = $response->getTransactionReference();

//return the status of transaction (pendent, paid , not_exist, etc)
$paymentStatus = $gateway->checkStatus(['TransactionReference' => 'xxxxxx'])->send();

if ($response->isSuccessful()) {
    // payment was successful: update database
	// you will nedd to store some adicional data like transaction reference,
} else {
    // payment failed: display message to customer
    echo $response->getMessage();
}

```