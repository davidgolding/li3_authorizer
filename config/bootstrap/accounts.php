<?php

use li3_authorizer\Authorizer;

Authorizer::config(array(
	'test' => array(
		'loginId' => '',
		'transactionKey' => '',
		'mode' => 'test',
		'version' => '3.1',
		'delimitData' => 'TRUE',
		'delimitChar' => '|',
		'relayResponse' => 'FALSE',
		'type' => 'AUTH_CAPTURE',
		'method' => 'CC',
		'description' => 'TEST - TRANSACTION DESCRIPTION',
		'url' => 'https://test.authorize.net/gateway/transact.dll'
	),
	'default' => array(
		'loginId' => '',
		'transactionKey' => '',
		'mode' => 'live',
		'version' => '3.1',
		'delimitData' => 'TRUE',
		'delimitChar' => '|',
		'relayResponse' => 'FALSE',
		'type' => 'AUTH_CAPTURE',
		'method' => 'CC',
		'description' => 'TRANSACTION DESCRIPTION',
		'url' => 'https://secure.authorize.net/gateway/transact.dll'
	)
));

?>