<?php
class Netplus_Netpluspay_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'netpluspay';
    
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = true;
    protected $_canUseForMultishipping  = false;
    
    public function getOrderPlaceRedirectUrl() {
            return Mage::getUrl('netpluspay/payment/redirect', array('_secure' => true));
    }
 
}
