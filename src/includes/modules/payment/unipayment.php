<?php
require_once(dirname(__FILE__) . '/unipayment/vendor/autoload.php');	


class unipayment extends base {
  
  var $code;
  var $moduleVersion = '1.0.1';
  var $title;
  var $description;
  var $enabled;  
  var $transaction_id;
  var $order_nstatus; 

protected $reportable_submit_data;	

  /**
   * Constructor
   */
  function __construct() {
    global $order;

    $this->code = 'unipayment';

    $this->title = MODULE_PAYMENT_UNIPAYMENT_TEXT_CATALOG_TITLE; 
    if (IS_ADMIN_FLAG === true) {
      $this->description = MODULE_PAYMENT_UNIPAYMENT_TEXT_DESCRIPTION;
      $this->title = MODULE_PAYMENT_UNIPAYMENT_TEXT_ADMIN_TITLE; 
    }

    $this->enabled = (defined('MODULE_PAYMENT_UNIPAYMENT_STATUS') && MODULE_PAYMENT_UNIPAYMENT_STATUS == 'True');
    $this->sort_order = defined('MODULE_PAYMENT_UNIPAYMENT_SORT_ORDER') ? MODULE_PAYMENT_UNIPAYMENT_SORT_ORDER : null;
	
    if (null === $this->sort_order) return false;  
    
	  
	$this->client_id =  defined('MODULE_PAYMENT_UNIPAYMENT_CLIENT_ID') ? MODULE_PAYMENT_UNIPAYMENT_CLIENT_ID : '';
	$this->client_secret = defined('MODULE_PAYMENT_UNIPAYMENT_CLIENT_SECRET') ? MODULE_PAYMENT_UNIPAYMENT_CLIENT_SECRET : '';
    $this->app_id = defined('MODULE_PAYMENT_UNIPAYMENT_APP_ID') ? MODULE_PAYMENT_UNIPAYMENT_APP_ID : '';	  	  
	$this->confirm_speed = defined('MODULE_PAYMENT_UNIPAYMENT_C_SPEED') ? MODULE_PAYMENT_UNIPAYMENT_C_SPEED : ''; 
    $this->pay_currency = defined('MODULE_PAYMENT_UNIPAYMENT_PAY_CCY') ? MODULE_PAYMENT_UNIPAYMENT_PAY_CCY : '';
	
	$this->environment = defined('MODULE_PAYMENT_UNIPAYMENT_ENV') ? MODULE_PAYMENT_UNIPAYMENT_ENV : '';  	
	$this->lang =  $this->get_lang();
	
	if ($this->enabled == 'True' && ($this->client_id  == '')) {
      $this->title .=  '<span class="alert"> (Not Configured)</span>';
    } elseif ($this->environment == 'Test') {
      $this->title .= '<span class="alert"> (in Testing mode)</span>';
    }   
    
	$this->uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
    $this->uniPaymentClient->getConfig()->setDebug(false);
    $this->uniPaymentClient->getConfig()->setIsSandbox($this->environment == 'SandBox');
  

    if (is_object($order)) $this->update_status();

    
  }

 
  function update_status() {
    global $order, $db;

    if ($this->enabled && (int)MODULE_PAYMENT_UNIPAYMENT_ZONE > 0 && isset($order->billing['country']['id'])) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_UNIPAYMENT_ZONE . "' and zone_country_id = '" . (int)$order->billing['country']['id'] . "' order by zone_id");
      while (!$check->EOF) {
        if ($check->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
        $check->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }

    
  }
	

  function javascript_validation() {
    return '';
  }
  
  function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
  }
  
	
  function pre_confirmation_check() {    
    return true;
  }
  
	
  function confirmation() {	
    return array();
  }
	
  public function after_order_create($insert_id)
  {
	  global $db, $order;
	  
	  $next_order_id = $insert_id;
	  $order_id = $insert_id;
	  $amount = round($order->info['total'], 2);
	  $desc = 'Order No : ' . $order_id;
	  $currency_code = $order->info['currency'];
	  
	  $returnURL = zen_href_link(FILENAME_CHECKOUT_SUCCESS, 'checkout_id=' . $insert_id, 'SSL');  	  
	  $notifyURL = zen_href_link( 'unipayment_notify_handler.php', '', 'SSL', false, false, true ); 
	  
	  $this->uniPaymentClient->getConfig()->setClientId($this->client_id);
	  $this->uniPaymentClient->getConfig()->setClientSecret($this->client_secret);

	  $createInvoiceRequest = new \UniPayment\Client\Model\CreateInvoiceRequest();
	  $createInvoiceRequest->setAppId($this->app_id);
	  $createInvoiceRequest->setPriceAmount($amount);
	  $createInvoiceRequest->setPriceCurrency($currency_code);
      
	  if ($this->pay_currency != '-') {	
		  $createInvoiceRequest->setPayCurrency($this->pay_currency);
	  }

	  $createInvoiceRequest->setOrderId($order_id);
	  $createInvoiceRequest->setConfirmSpeed($this->confirm_speed);
	  $createInvoiceRequest->setRedirectUrl($returnURL);
	  $createInvoiceRequest->setNotifyUrl($notifyURL);
	  $createInvoiceRequest->setTitle($desc);
	  $createInvoiceRequest->setDescription($desc);
	  $createInvoiceRequest->setLang($this->lang);
	  $response = $this->uniPaymentClient->createInvoice($createInvoiceRequest);
	  
	  if ($response['code'] == 'OK') {
		  $payurl = $response->getData()->getInvoiceUrl();		  
		  header("Location: $payurl");
		  exit;		  
	  } else {
		  $errmsg = $response['msg']; 
		  echo $errmsg;
		  exit;
	  }
	  exit;
  }

   
  function process_button() {        
    return '';
  }
  /**
   * Store the CC info to the order and process any results that come back from the payment gateway
   *
   */
  function before_process() {
	  return true;
  }

  
  function after_process() {	
    return false;
  }
  /**
   * Check to see whether module is installed
   *
   * @return boolean
   */
  function check() {
    global $db;
    // install newer switches, if relevant
    
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_UNIPAYMENT_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }
  /**
   * Install the payment module and its configuration settings
   *
   */
  function install() {
    global $db, $messageStack;
    if (defined('MODULE_PAYMENT_UNIPAYMENT_STATUS')) {
      $messageStack->add_session('Unipayment Payment Pages module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=firstdata'));
      return 'failed';
    }
	  

	
	$payccy_list = 'array('.$this->get_currencies().')';  
	  
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Unipayment Payment Module', 'MODULE_PAYMENT_UNIPAYMENT_STATUS', 'True', 'Do you want to accept  payments via Unipayment?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
	  
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_UNIPAYMENT_SORT_ORDER', '0', 'Sort order of displaying payment options to the customer. Lowest is displayed first.', '6', '0', now())");
	  
      	  
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_UNIPAYMENT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
	  
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".MODULE_PAYMENT_UNIPAYMENT_TEXT_CLIENT_ID."', 'MODULE_PAYMENT_UNIPAYMENT_CLIENT_ID', '', '".MODULE_PAYMENT_UNIPAYMENT_HELP_CLIENT_ID."', '6', '0', now() )");
	
	$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".MODULE_PAYMENT_UNIPAYMENT_TEXT_CLIENT_SECRET."', 'MODULE_PAYMENT_UNIPAYMENT_CLIENT_SECRET', '', '".MODULE_PAYMENT_UNIPAYMENT_HELP_CLIENT_SECRET."', '6', '0', now() )");  
	
	$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('".MODULE_PAYMENT_UNIPAYMENT_TEXT_APP_ID."', 'MODULE_PAYMENT_UNIPAYMENT_APP_ID', '', '".MODULE_PAYMENT_UNIPAYMENT_HELP_APP_ID."', '6', '0', now() )");   
	  
	$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('".MODULE_PAYMENT_UNIPAYMENT_TEXT_C_SPEED."', 'MODULE_PAYMENT_UNIPAYMENT_C_SPEED', 'medium', '".MODULE_PAYMENT_UNIPAYMENT_HELP_C_SPEED."', '6', '0', 'zen_cfg_select_option(array(\'low\', \'medium\', \'high\'), ', now() )");     
	  
	$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('".MODULE_PAYMENT_UNIPAYMENT_TEXT_PAY_CCY."', 'MODULE_PAYMENT_UNIPAYMENT_PAY_CCY', '-', '".MODULE_PAYMENT_UNIPAYMENT_HELP_PAY_CCY."', '6', '0', 'zen_cfg_select_option(".$payccy_list.", ', now() )");       
	  
	$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('".MODULE_PAYMENT_UNIPAYMENT_TEXT_PR_STATUS."', 'MODULE_PAYMENT_UNIPAYMENT_PR_STATUS', 'Confirmed', '".MODULE_PAYMENT_UNIPAYMENT_HELP_PR_STATUS."', '6', '0',  'zen_cfg_select_option(array(\'Confirmed\', \'Complete\', \'Paid\'), ',now())");  	  
	  

	$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('".MODULE_PAYMENT_UNIPAYMENT_TEXT_HEXP_STATUS."', 'MODULE_PAYMENT_UNIPAYMENT_HEXP_STATUS', 'No', '".MODULE_PAYMENT_UNIPAYMENT_HELP_HEXP_STATUS."', '6', '0', 'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
	  
	
   	$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('".MODULE_PAYMENT_UNIPAYMENT_TEXT_ENV."', 'MODULE_PAYMENT_UNIPAYMENT_ENV', 'SandBox', '".MODULE_PAYMENT_UNIPAYMENT_HELP_ENV."', '6', '0', 'zen_cfg_select_option(array(\'SandBox\', \'Live\'), ', now())");
	  	  
  
  }
	
  function get_currencies($fiat = false)
  {
	  $currencies = "\'-\'";
	  $cm = ',';
	  $this->uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
    $this->uniPaymentClient->getConfig()->setDebug(false);
    $this->uniPaymentClient->getConfig()->setIsSandbox($this->environment == 'SandBox');	  
	  $apires = $this->uniPaymentClient->getCurrencies();
	  if ($apires['code'] == 'OK') {
		  foreach ($apires['data'] as $crow) {
			  if ($crow['is_fiat'] == $fiat) {
				$currencies .=  $cm."\'". $crow['code']."\'";
			  }
		  }
	  }
	  return $currencies;
  }	
	
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }
	
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
    return array('MODULE_PAYMENT_UNIPAYMENT_STATUS',
            'MODULE_PAYMENT_UNIPAYMENT_SORT_ORDER',
			'MODULE_PAYMENT_UNIPAYMENT_ZONE',				 
            'MODULE_PAYMENT_UNIPAYMENT_CLIENT_ID',
		    'MODULE_PAYMENT_UNIPAYMENT_CLIENT_SECRET',				 
		    'MODULE_PAYMENT_UNIPAYMENT_APP_ID', 
            'MODULE_PAYMENT_UNIPAYMENT_C_SPEED',
            'MODULE_PAYMENT_UNIPAYMENT_PAY_CCY',            
			'MODULE_PAYMENT_UNIPAYMENT_PR_STATUS',            				 
			'MODULE_PAYMENT_UNIPAYMENT_HEXP_STATUS',	 			
            'MODULE_PAYMENT_UNIPAYMENT_ENV');
  }
 function get_lang() {	
	 $langlist = array(
		 	'english' => 'en-US', 
		 	'chinese' => 'zh-CN', 	
			'arabic' => 'ar-AE', 			 
		 	'dutch' => 'nl-BE',		 
		 	'spanish' => 'es-ES',		 		 
		 	'german' => 'de-DE',		 
		 	'french' => 'fr-FR',		 
		 	'hebrew' => 'he-IL',
		 	'hindi' => 'hi-IN',
		 	'tamil' => 'ta-IN',		 
		 	'japanese' => 'ja-JP',
		 	'russian' => 'ru-RU',
		 	'greek' => 'el-GR',	
		 	'danish' => 'da-DK',
		 	'polish' => 'pl-PL',
		 	'swedish' => 'sv-SE',		 
			'indonesian' => 'id-ID',
		 	'italian' => 'it-IT',
		 	'portuguese' => 'pt-PT',		 
		 	'finnish' => 'fi-FI',
		 	'portuguese' => 'bg-BG',		 					   
	 	) ;	  
	 return $langlist[$_SESSION['language']];
 }
	

}



