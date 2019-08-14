<?php
 
class ICheckGateway_CreditCards_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{

    protected $_code = 'CreditCards';
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
			$url='https://icheckgateway.com/API/Route.aspx';
		}
		else{
			$url='https://icheckgateway.com/iRouteDemo/';
		}
		
		$expy = sprintf('%02d', $payment->getCcExpMonth()).substr($payment->getCcExpYear(),-2);
		
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
						'PaymentType'=>("CCARD"),
						'CCNumber'=>urlencode($payment->getCcNumber()),
						'CCExpire'=>urlencode($expy),
						'CCCVC'=>urlencode($payment->getCcCid())
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