<?php
define('MODULE_PAYMENT_UNIPAYMENT_TEXT_ADMIN_TITLE', 'UniPayment');
define('MODULE_PAYMENT_UNIPAYMENT_TEXT_CATALOG_TITLE', 'UniPayment');

if (IS_ADMIN_FLAG === true) {
	if (defined('MODULE_PAYMENT_UNIPAYMENT_STATUS') && MODULE_PAYMENT_UNIPAYMENT_STATUS == 'True') {
		define('MODULE_PAYMENT_UNIPAYMENT_TEXT_DESCRIPTION', 'UniPayment');
	} else {
		define('MODULE_PAYMENT_UNIPAYMENT_TEXT_DESCRIPTION', 'UniPayment');
	}
	}
define('MODULE_PAYMENT_UNIPAYMENT_TEXT_DECLINED_MESSAGE', 'The transaction could not be completed.');
define('MODULE_PAYMENT_UNIPAYMENT_TEXT_ERROR_MESSAGE', 'There has been an error processing the transaction. Please try again.');


define('MODULE_PAYMENT_UNIPAYMENT_TEXT_CLIENT_ID', 'Client ID');
define('MODULE_PAYMENT_UNIPAYMENT_HELP_CLIENT_ID', 'Enter Client ID Given by UniPayment');

define('MODULE_PAYMENT_UNIPAYMENT_TEXT_CLIENT_SECRET', 'Client Secret');
define('MODULE_PAYMENT_UNIPAYMENT_HELP_CLIENT_SECRET', 'Enter Client Secret Given by UniPayment');

define('MODULE_PAYMENT_UNIPAYMENT_TEXT_APP_ID', 'Payment App ID');
define('MODULE_PAYMENT_UNIPAYMENT_HELP_APP_ID', 'Enter Payment App ID Given by UniPayment');

define('MODULE_PAYMENT_UNIPAYMENT_TEXT_C_SPEED', 'Confirm Speed');
define('MODULE_PAYMENT_UNIPAYMENT_HELP_C_SPEED', 'This is a risk parameter for the merchant to configure how they want to fulfill orders depending on the number of block confirmations.');

define('MODULE_PAYMENT_UNIPAYMENT_TEXT_PAY_CCY', 'Pay Currency');
define('MODULE_PAYMENT_UNIPAYMENT_HELP_PAY_CCY', 'Select the default pay currency used by the invoice, If not set customer will select on invoice page.');

define('MODULE_PAYMENT_UNIPAYMENT_TEXT_PR_STATUS', 'Processing Status');
define('MODULE_PAYMENT_UNIPAYMENT_HELP_PR_STATUS', 'Which status will be considered the order is paid.');


define('MODULE_PAYMENT_UNIPAYMENT_TEXT_HEXP_STATUS', 'Handel Expired Status');
define('MODULE_PAYMENT_UNIPAYMENT_HELP_HEXP_STATUS', 'If set to <b>Yes</b>, the order will set to failed when the invoice has expired and has been notified by the UniPayment IPN.');

define('MODULE_PAYMENT_UNIPAYMENT_TEXT_ENV', 'Environment');
define('MODULE_PAYMENT_UNIPAYMENT_HELP_ENV', 'Select which enviroment the plugin is connected with.');
