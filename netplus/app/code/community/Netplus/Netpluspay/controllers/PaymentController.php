<?php
/*rokucalutech*/
class Netplus_Netpluspay_PaymentController extends Mage_Core_Controller_Front_Action {

  
	
	// The redirect action is triggered when someone places an order
	public function redirectAction() {
		echo $this->getLayout()->createBlock('core/template')->setTemplate('netpluspay/redirect.phtml')->tohtml();			
	}
	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	
	public function responseAction() {		 
		 
		$params = $this->getRequest()->getParams();
		$status  =   $params['code'];		 
		$order_id  =  $params['order_id'] ;
		$trans_ref = $params['transaction_id'];
		 
        if($status !='00')
        {
			$this->cancelAction();
			Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
			exit;
			$validated = false;				
		}else{
				
				// Payment was successful, so update the order's state, send order email and move to the success page
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($order_id);

				$order->addStatusToHistory(
					$order->getStatus(),
					Mage::helper('netpluspay')->__('Customer successfully returned from Netpluspay')
				);

				$payment = $order->getPayment();


				$payment->setTransactionId($trans_ref);
				$payment->setAdditionalInformation('transaction_id', $trans_ref);

				$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'NetPlusPay Gateway has authorized the payment.');

				$order->addStatusToHistory(
					$order->getStatus(),
					Mage::helper('netpluspay')->__('Netpluspay Payment Complete. Order ID :'.$this->order_id.', Transaction ID :'.$trans_ref)
				);
				
				$order->sendNewOrderEmail();
				$order->setEmailSent(true);
				$order->save();
			
				Mage::getSingleton('checkout/session')->unsQuoteId();
				
				Mage::log('About to redirect to the success page');
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
				
			}
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
		
		
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
			}
        }
	}
}