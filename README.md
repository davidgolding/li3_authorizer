li3_authorizer
==============

Authorize.net helper plugin for the Lithium framework

## How to Install

Simply place the `li3_authorizer` folder into the Lithium application's `libraries` directory and bootstrap it in using

```php
Libraries::add('li3_authorizer');
```

Once the plugin is bootstrapped, call the Authorizer class anywhere in the application where you plan on capturing payments.

```php
use li3_authorizer\Authorizer;

/**
 * your own surrounding code
 */
 //this will capture the payment and return Authorize.net's response codes
 $txn = Authorizer::capture($paymentDetails);
```

## Configuring for Capturing Payments

You'll need to provide your Authorize.net account settings for li3_authorizer to successfully capture payments. These are stored in the
`li3_authorizer/bootstrap/accounts.php` file. The default configuration captures **live** payments, so if you wish to run test payments,
you'll need to supply the proper configurations and pass the `account` key during capture:

```php
$txn = Authorizer::capture($paymentDetails, array('account' => 'test'));
```

## Capturing the Payment

As you can see from above, capturing a payment is made simple by calling the `capture` method. You will need to pass the payment details as
an array. The following array keys are required for Authorize.net to return a valid response:

* `'cc'` - The credit card number to process
* `'expmonth'` - The card's expiration month
* `'expyear'` - The card's expiration year
* `'ccv'` - The card security code (Card Code Verification or CCV)
* `'amount'` - The amount
* `'firstname'` - Cardholder's firstname
* `'lastname'` - Cardholder's lastname
* `'address'` - Address on the card
* `'city'` - City on the card
* `'state'` - Two-letter abbreviation of state on the card
* `'zip'` - ZIP code on the card

### Example Payment

```php
$txn = Authorizer::capture(array(
	'cc' => '378282246310005',
	'expmonth' => '12',
	'expyear' => '2020',
	'ccv' => '1111',
	'amount' => '10.00',
	'firstname' => 'John',
	'lastname' => 'Doe',
	'address' => '1 Main St.',
	'city' => 'Beverly Hills',
	'state' => 'CA',
	'zip' => '90210'
));
```

### Example Response

Authorize.net will return a response which the Authorizer class will format as an array. The following is a sample returned response array from running
the previous transaction out-of-the-box:

```php
$response = array(
	'status' => 3,
	'message' => 'The merchant login ID or password is invalid or the account is inactive.',
	'amount' => '10.00',
	'txn' => '74078B398622E4FBDC0FB5469D7A2054',
	'params' => array(
		'x_login' => null,
		'x_tran_key' => null,
		'x_version' => '3.1',
		'x_delim_data' => 'TRUE',
		'x_delim_char' => '|',
		'x_relay_response' => 'FALSE',
		'x_type' => 'AUTH_CAPTURE',
		'x_method' => 'CC',
		'x_card_num' => '378282246310005',
		'x_exp_date' => '12-2020',
		'x_card_code' => '1111',
		'x_amount' => '10.00',
		'x_description' => 'TRANSACTION DESCRIPTION',
		'x_first_name' => 'John',
		'x_last_name' => 'Doe',
		'x_address' => '1 Main St.',
		'x_city' => 'Beverly Hills',
		'x_state' => 'CA',
		'x_zip' => '90210'
	),
	'full' => array() //the full response as a keyed array; see the Authorize.net API for information on their response codes
);
```