# Eupago - Multibanco for omnipay

**This is the Eupago solution for omnipay payment processing library**

Eupago is one Portuguese payment method that allows the customer to pay by bank reference.
For use it you need create one account in [Eupago](http://www.eupago.pt/) website.
Once installed and configured you are able to use all the features of our [API](https://seguro.eupago.pt/api/).

## Instalation

For instalation details please check the [omnipay](https://github.com/thephpleague/omnipay#installation) git page.


## Required fields

apiKey -> to generate your api key please [create_an_account](https://eupago.pt/registo) on our website</br>
currency -> currency is required and must be 'EUR' or '€'</br>
ammount -></br>
transactionId -> transactionId (normaly this is the orderId number)</br>


## Example 


```php

$gateway = Omnipay::create('Eupago_Multibanco');

//required fields
$gateway->setApiKey('xxx-xxx-xxx-xxx');
$gateway->setCurrency('EUR');
$gateway->setTransactionId('xxxxx');

$response = $gateway->purchase(['amount' => '10.00'])->send();

// return the Eupago api response with payment credentials
// see src/message/response.php methods for more information
$payment_data = $response->getData();

// return the Transaction Reference
// the transaction Reference is required for call the status of payment, you should store them in your "orders" table related database
$ref = $response->getTransactionReference();

//return the status of transaction (pendent, paid , not_exist, etc)
$PaymentStatus = $gateway->checkStatus(['TransactionReference' => 'xxxxxx'])->send();

if ($response->isSuccessful()) {
    // payment was successful: update database
	// you will nedd to store some adicional data like transaction reference,
} else {
    // payment failed: display message to customer
    echo $response->getMessage();
}

```