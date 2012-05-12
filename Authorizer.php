<?php
/**
 * Authorizer - Authorize.net transactions utility class for Lithium
 *
 * @copyright	Copyright 2012, David Golding http://www.davidgolding.com
 * @license		http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace li3_authorizer;

use lithium\core\NetworkException;

/**
 * Sends and receives an Authorize.net request based on a given data array.
 *
 * When running a capture payment on Authorize.net, a toggle between configurations
 * in the bootstrap may be passed using the `account` key:
 *
 * {{{
 * $txn = Authorizer::capture($userPaymentSettings, array('account' => 'test')); //will capture credit card payments
 * }}}
 */
class Authorizer extends \lithium\core\Adaptable {
	
	/**
	 * Holds configuration settings
	 */
	protected static $_configurations = array();
	
	/**
	 * Mapping for Adaptable class
	 */
	protected static $_adapters = 'adapter.authorizer';
	
	/**
	 * Load configs
	 */
	protected static function _initConfig($name, $config) {
		$defaults = array('adapter' => 'Authorizer');
		$config = parent::_initConfig($name, $config) + $defaults;
		return $config;
	}
	
	/**
	 * Sends and receives an Authorize.net request based on given data array.
	 *
	 * {{{
	 * $capture = Authorizer::capture(array(
	 * 	'cc'		=> '378282246310005',
	 * 	'expmonth'	=> '12',
	 * 	'expyear'	=> '2020',
	 * 	'ccv'		=> '1111',
	 * 	'amount'	=> '1000000.00',
	 * 	'firstname'	=> 'Warren',
	 * 	'lastname'	=> 'Buffet',
	 * 	'address'	=> '1 Main St.',
	 * 	'city'		=> 'Beverly Hills',
	 * 	'state'		=> 'CA',
	 * 	'zip'		=> '90210'
	 * ));
	 * }}}
	 *
	 * Alternatively, configuration options may be passed when the function is called, though
	 * it's best to set these configurations in the li3_authorizer/bootstrap/accounts.php file.
	 * Use the `account` key to specify which configuration array should be used:
	 *
	 * {{{
	 * $txn = array(); //the user payment details
	 * $capture = Authorizer::capture($txn, array('account' => 'test'));
	 * }}}
	 *
	 * {{{
	 * $txn = array(); //the user payment details
	 * $capture = Authorizer::capture($txn, array('delimitChar' => ',', 'description' => 'One Month Subscription'));
	 * }}}
	 *
	 * Every transaction will need to provide the full transaction values otherwise Authorize.net
	 * will return a failed response:
	 *
	 * ### Transaction Fields to Send
	 *
	 * * `'cc'` - The credit card number to process
	 * * `'expmonth'` - The card's expiration month
	 * * `'expyear'` - The card's expiration year
	 * * `'ccv'` - The card security code (Card Code Verification or CCV)
	 * * `'amount'` - The amount
	 * * `'firstname'` - Cardholder's firstname
	 * * `'lastname'` - Cardholder's lastname
	 * * `'address'` - Address on the card
	 * * `'city'` - City on the card
	 * * `'state'` - Two-letter abbreviation of state on the card
	 * * `'zip'` - ZIP code on the card
	 *
	 * ### Returned Response Keys
	 *
	 * * `'status'` - Response code from transaction
	 * * `'message'` - Transaction message, particularly important for any error messages
	 * * `'amount'` - Amount captured in the transaction
	 * * `'params'` - Full parameter string as an array with matching Authorize.net value keys
	 * * `'full'` - The full transaction response as an array
	 *
	 * @param array $data An array containing the transaction fields to send
	 * @param array $options Additional options used only for this transaction
	 * @return array Response array containing returned transaction response codes
	 */
	public static function capture($data, array $options = array()) {
		if (!$data || empty($data)) {
			return false;
		}
		$account = 'default';
		if (isset($options['account'])) {
			$account = $options['account'];
			unset($options['account']);
		}
		$config = static::$_configurations;
		$params = $options + $config[$account];
		$txn = array(
		    'x_login'          =>    $params['loginId'],
		    'x_tran_key'       =>    $params['transactionKey'],
		    'x_version'        =>    $params['version'],
		    'x_delim_data'     =>    $params['delimitData'],
		    'x_delim_char'     =>    $params['delimitChar'],
		    'x_relay_response' =>    $params['relayResponse'],
		    'x_type'           =>    $params['type'],
		    'x_method'         =>    $params['method'],
		    'x_card_num'       =>    $data['cc'],
		    'x_exp_date'       =>    $data['expmonth'] . '-' . $data['expyear'],
		    'x_card_code'      =>    $data['ccv'],
		    'x_amount'         =>    $data['amount'],
		    'x_description'    =>    $params['description'],
		    'x_first_name'     =>    $data['firstname'],
		    'x_last_name'      =>    $data['lastname'],
		    'x_address'        =>    $data['address'],
		    'x_city'           =>    $data['city'],
		    'x_state'          =>    $data['state'],
		    'x_zip'            =>    $data['zip']
		);
		$request = curl_init($params['url']);
		curl_setopt($request, CURLOPT_HEADER, 0); 			// set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_TIMEOUT, 45);			// sets the timeout to 45 seconds
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); 	// Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($txn));	// use HTTP POST to send form data
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 2);
		$response = curl_exec($request);
		if ($error = curl_error($request)) {
			throw new NetworkException('There was an error capturing the transaction with Authorize.net', 503);
		}
		curl_close($request);
		$response = explode($params['delimitChar'], $response);
		return array(
		    'status' => $response[0],
		    'message' => $response[3],
		    'amount' => $response[9],
		    'txn' => $response[37],
		    'params' => $txn,
		    'full' => $response
		);
	}
}

?>