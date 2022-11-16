<?php	
	require_once( 'includes/configure.php' );
	require_once( 'includes/application_top.php' );
	require_once(dirname(__FILE__).'/includes/modules/payment/unipayment/vendor/autoload.php');

	$notify_json = file_get_contents('php://input');
	$notify_ar = json_decode($notify_json, true);
	$order_id = $notify_ar['order_id'];
	$invoice_id = $notify_ar['invoice_id'];
	

	$upconf = getUnipaymentConf();
	$processing_status = $upconf['MODULE_PAYMENT_UNIPAYMENT_PR_STATUS'];
	$handle_expired_status = $upconf['MODULE_PAYMENT_UNIPAYMENT_HEXP_STATUS'];


	$uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
    $uniPaymentClient->getConfig()->setDebug(false);
    $uniPaymentClient->getConfig()->setIsSandbox($upconf['MODULE_PAYMENT_UNIPAYMENT_ENV'] == 'SandBox');
	$uniPaymentClient->getConfig()->setClientId($upconf['MODULE_PAYMENT_UNIPAYMENT_CLIENT_ID']);
	$uniPaymentClient->getConfig()->setClientSecret($upconf['MODULE_PAYMENT_UNIPAYMENT_CLIENT_SECRET']);
	$response = $uniPaymentClient->checkIpn($notify_json);

	if ($response['code'] == 'OK') {
		$error_status = $notify_ar['error_status'];		
		$status = $notify_ar['status'];
		switch ($status) {
			case 'New':
				{					
					break;
				}
			case 'Paid': 				
				{
					if($processing_status == $status) UpdateOrder($order_id, $invoice_id);
					$info_string  = 'Invoice : '.$invoice_id.' transaction detected on blockchain';
					error_log("    [Info] $info_string");																			
					break;
				}
                    
			case 'Confirmed':
				{
					if($processing_status == $status) UpdateOrder($order_id, $invoice_id);
					$info_string  = 'Invoice : '.$invoice_id.' has changed to confirmed';
					error_log("    [Info] $info_string");															
					break;
				}
			case 'Complete':
				{
					
					if($processing_status == $status) UpdateOrder($order_id, $invoice_id);
					$info_string  = 'Invoice : '.$invoice_id.' has changed to complete';
					error_log("    [Info] $info_string");										
					break;	
				}
				
                    
			case 'Invalid':
				{
					$error_string  = 'Invoice : '.$invoice_id.' has changed to invalid because of network congestion, please check the dashboard';
					error_log("    [Warning] $error_string");					
					break;				
				}
			case 'Expired':
				{
					$error_string  = 'Invoice : '.$invoice_id.' has chnaged to expired';
					if ($handle_expired_status == 'Yes') {	
						UpdateOrder($order_id, $invoice_id, 4);							
					}
					
					error_log("    [Warning] $error_string");					
					break;                    
				}
			default:
				{
					error_log('    [Info] IPN response is an unknown message type. See error message below:');
					$error_string = 'Unhandled invoice status: ' . $payment_status;
					error_log("    [Warning] $error_string");
                }
		}
		echo "SUCCESS";
		
	}
	else {
		echo "Fail";
	}
	exit;

		
function UpdateOrder($order_id,  $invoice_id, $newstatus = 2)
{
	global $db;	
	$comments = 'Invoice Id: '.$invoice_id;
	$ordupdatar = array(array('fieldName' => 'orders_id', 'value' => $order_id, 'type' => 'integer'),
			array('fieldName' => 'orders_status_id', 'value' => $newstatus, 'type' => 'integer'),
            array('fieldName' => 'date_added', 'value' => 'now()', 'type' => 'noquotestring'),
            array('fieldName' => 'comments', 'value' => $comments, 'type' => 'string'),
            array('fieldName' => 'customer_notified', 'value' => 0, 'type' => 'integer'));
		
	$db->perform(TABLE_ORDERS_STATUS_HISTORY, $ordupdatar);
		
	$db->Execute("UPDATE " . TABLE_ORDERS . " SET `orders_status` = '" . (int)$newstatus
            . "' WHERE `orders_id` = '" . (int)$order_id . "'");
			
}

 function getUnipaymentConf()
    {
        global $db;
        $query = $db->Execute("SELECT configuration_key,configuration_value FROM " . TABLE_CONFIGURATION
            . " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_UNIPAYMENT\_%'");
        if ($query->RecordCount() === 0) {            
			return false;
        }
        while (!$query->EOF) {
            $fbconf[$query->fields['configuration_key']] = $query->fields['configuration_value'];
            $query->MoveNext();
        }
	 return $fbconf;
    }

?>