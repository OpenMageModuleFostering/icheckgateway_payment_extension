<?php
 
class ICheckGateway_Checks_Model_PaymentMethod extends Mage_Payment_Model_Method_CheckACH
{

    protected $_code = 'Checks';
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc = false;
 
	public function authorize(Varien_Object $payment, $amount)
    {
		$order = $payment->getOrder();
		$billing = $order->getBillingAddress();
		$livemode = $this->getConfigData('livemode');
		
		if($livemode == 1){
			$url='https://icheckgateway.com/API/Post.aspx';
		}
		else{
			$url='https://icheckgateway.com/iRouteDemo/';
		}
		
		$fields = array(
						'SiteID'=>urlencode($this->getConfigData('siteid')),
						'SiteKey'=>urlencode($this->getConfigData('sitekey')),
						'APIKey'=>urlencode($this->getConfigData('apikey')),
						'cus1'=>urlencode($order->getIncrementId()),
						'cus2'=>urlencode($order->getRemoteIp()),
						'Amount'=>urlencode($amount),
						'LastName'=>urlencode($billing->getLastname()),
						'FirstName'=>urlencode($billing->getFirstname()),
						'Address1'=>urlencode($billing->getStreet(1)),
						'City'=>urlencode($billing->getCity()),
						'State'=>urlencode($billing->getRegion()),
						'Zip'=>urlencode($billing->getPostcode()),
						'EmailAddress'=>urlencode(""),
						'PaymentType'=>("ICHECK"),
						'CompanyAccount'=>urlencode("N"),
						'RoutingNumber'=>urlencode($_SESSION['routing']),
						'AccountNumber'=>urlencode($_SESSION['account']),
						'AccountType'=>urlencode($_SESSION['accounttype'])
		);

		foreach($fields as $key=>$value){
						$fields_string .= $key.'='.$value.'&'; } rtrim($fields_string,'&'); $ch = curl_init();

		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0); 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

		$result = curl_exec($ch);
		
		$_SESSION['routing'] = "";
		$_SESSION['account'] = "";
		$_SESSION['accounttype'] = "";

		$pos = strrpos($result, 'APPROVED');
		
		if ($pos === false) {
			Mage::throwException("Your Payment Was Declined. Please Try Another Payment Method.");
			return false;
		}
		else {
			return true;
		}

    }

}
?>