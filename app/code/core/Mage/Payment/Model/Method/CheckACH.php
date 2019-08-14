<?php

class Mage_Payment_Model_Method_CheckACH extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'checkach';
    protected $_formBlockType = 'payment/form_CheckACH';
    protected $_infoBlockType = 'payment/info_CheckACH';

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setRoutingNum($data->getRoutingNum())
		;
		$_SESSION['routingnum'] = $data->getRoutingnum();
		$_SESSION['account'] = $data->getAccountnum();
		$_SESSION['accounttype'] = $data->getAccounttype();
        return $this;
    }

    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }

    public function getMailingAddress()
    {
        return $this->getConfigData('mailing_address');
    }

}
