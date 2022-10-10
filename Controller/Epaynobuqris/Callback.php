<?php
/**
 * Copyright (c) 2017. All rights reserved Duitku Nobuqris.
 *
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software.
 * It is also not legal to do any changes to the software and distribute it in your own name / brand.
 *
 * All use of the payment modules happens at your own risk. We offer a free test account that you can use to test the module.
 *
 * @author    Duitku Nobuqris
 * @copyright Duitku Nobuqris (http://duitku.com)
 * @license   Duitku Nobuqris
 *
 */
namespace Duitku\Nobuqris\Controller\Epaynobuqris;

use \Magento\Framework\Webapi\Exception;
use \Magento\Framework\Webapi\Response;
use \Duitku\Nobuqris\Model\Method\Epay\Payment as EpayPayment;
use \Duitku\Nobuqris\Helper\DuitkuConstants;

class Callback extends \Duitku\Nobuqris\Controller\AbstractActionController
{

    /**
     * Callback Action
     */
    public function execute()
    {
    	
        $posted = $this->getRequest()->getParams();
		        /** @var \Magento\Sales\Model\Order */
        $order = null;
        $message = "Callback Failed: ";
        $responseCode = Exception::HTTP_BAD_REQUEST;
        if ($this->validateCallback($posted, $order, $message)) {
        	
            $message = $this->processCallback($posted, $order, $responseCode);
        }

        $id = isset($order) ? $order->getIncrementId() : 0;
         if ($responseCode !== Response::HTTP_OK) {
            $this->_logError(EpayPayment::METHOD_CODE, $id, $message);
            if (isset($order)) {
                $order->addStatusHistoryComment($message);
                $order->save();
            }
        }
        $callBackResult = $this->_createCallbackResult($responseCode, $message, $id);
       
        return $callBackResult;
    }

    /**
     * Validate the callback
     *
     * @param mixed $posted
     * @param \Magento\Sales\Model\Order $order
     * @param string $message
     * @return bool
     */
    public function validateCallback($posted, &$order, &$message)
    {
        //Validate response
        if (!isset($posted)) {
            $message .= "Response is null";
            return false;
        }

        //Validate parameteres
        if (!$posted['merchantOrderId'] || !$posted['resultCode'] || !$posted['reference'] || !$posted['signature'] ) {
            $message .= "Parameteres are missing. Request: " . json_encode($posted);
            return false;
        }

        //Validate Order
        $order = $this->_getOrderByIncrementId($posted['merchantOrderId']);
        if (!isset($order)) {
            $message .= "The Order could be found or created";
            return false;
        }

        //Validate Payment
        $payment = $order->getPayment();
        if (!isset($payment)) {
            $message .= "The Payment object is null";
            return false;
        }
        
		$merchantCode = isset($posted['merchantCode']) ? $posted['merchantCode'] : null; 
		$amount = isset($posted['amount']) ? $posted['amount'] : null; 
		$merchantOrderId = isset($posted['merchantOrderId']) ? $posted['merchantOrderId'] : null;
		$signature = isset($posted['signature']) ? $posted['signature'] : null; 
		$obj = \Magento\Framework\App\ObjectManager::getInstance();
		$apiKey = $obj->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/duitku_Nobuqrisepay/api_key',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId());;
		$params = $merchantCode . $amount . $merchantOrderId . $apiKey;
		$resultCode = isset($posted['resultCode']) ? $posted['resultCode']:null;


	    //check signature
	    if ($signature != hash("sha256", $params)) {
		   $message .= "Signature is invalid";
		   return false;			
	    }

	    if ($resultCode != '00' && $resultCode != '01') {
		$message .= "failed transaction";
		return false;
    	}
		
        return true;
    }

    /**
     * Process the callback from Duitku
     * @param mixed $posted
     * @param \Magento\Sales\Model\Order $order
     * @param int $responseCode
     * @return void
     */
    public function processCallback($posted, $order, &$responseCode)
    {
        $ePayTransactionId = $posted['reference'];
        $payment = $order->getPayment();
        try {
            $pspReference = $payment->getAdditionalInformation(EpayPayment::METHOD_REFERENCE);      
            if($order->getId() && ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT || $order->getState() == \Magento\Sales\Model\Order::STATE_NEW))
            {
                if ($posted['resultCode'] == '01') {      /** check if result code is failed **/
                    $comment =  __("Notification - order was canceled");
                    $message = "Callback Success - Order canceled";
                    $order->registerCancellation($comment)->save();
                    $responseCode = Response::HTTP_OK;
                } 
                else if ($posted['resultCode'] == '00') {   /** check if result code is success **/
                    $paymentMethod = $this->_getPaymentMethodInstance($order->getPayment()->getMethod());
                    $this->_processCallbackData($order,
                        $paymentMethod,
                        $ePayTransactionId,
                        EpayPayment::METHOD_REFERENCE,
                        $this->_duitkuHelper->getDuitkuEpayConfigData(DuitkuConstants::ORDER_STATUS),
                        $payment
                    );
                $message = "Callback Success - Order created";
                }
            }
            else {
                $message = "Callback Success - Order already created";
            }
            $responseCode = Response::HTTP_OK;
        
        } catch (\Exception $ex) {
            
            $this->_duitkuLogger->addEpayError($order->getId(), $ex->getMessage());
            $message = "Callback Failed - " .$ex->getMessage();
            $responseCode = Exception::HTTP_INTERNAL_ERROR;
        }

        return $message;
    }
}
