<?php
/**
* Handles all the payment functions - authorize, capture, refund, etc.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Model;

ini_set('memory_limit', '1000M');
ini_set('max_execution_time', -1);
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

use \Magento\Framework\DataObject;
use Magento\Payment\Model\Method\TransparentInterface;
use \Ebizcharge\Ebizcharge\Block\Form\Card;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class Payment extends \Magento\Payment\Model\Method\Cc
{
	protected $_formBlockType = 'Ebizcharge\Ebizcharge\Block\Form\Card';

    const CODE = 'ebizcharge_ebizcharge';
    protected $_code = self::CODE;
	
	protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;

    protected $_canSaveCc = false;

    protected $_authMode = 'auto';
	
    protected $_countryFactory;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array();
    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];
	protected $backendAuthSession;
	protected $customerSession;
	protected $_token;
	protected $_tran;
	protected $config;
	protected $_paymentConfig;
	protected $_scopeConfig;
	protected $sessionQuote;
	
    public function __construct(
    \Magento\Framework\Model\Context $context,
     \Magento\Framework\Registry $registry, 
     \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, 
     \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, 
     \Magento\Payment\Helper\Data $paymentData, 
     \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
     \Magento\Payment\Model\Method\Logger $logger,
     \Magento\Framework\Module\ModuleListInterface $moduleList,
     \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
     \Magento\Directory\Model\CountryFactory $countryFactory, 
	 \Magento\Backend\Model\Auth\Session $backendAuthSession,
	 \Magento\Customer\Model\Session $customerSession,
     \Ebizcharge\Ebizcharge\Model\TranApi $tranApi,
	 \Ebizcharge\Ebizcharge\Model\Token $token,
	 \Ebizcharge\Ebizcharge\Model\Config $config,
	 \Magento\Payment\Model\Config $paymentConfig,
	 \Magento\Backend\Model\Session\Quote $sessionQuote,
	 \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
	 \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
      array $data = array())
    {
        $this->_tran = $tranApi;
		$this->_token = $token;
		$this->config = $config;
		$this->_paymentConfig = $paymentConfig;
		$this->_scopeConfig = $scopeConfig;
		$this->sessionQuote = $sessionQuote;
		
		parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data);

        $this->_countryFactory = $countryFactory;
		$this->backendAuthSession = $backendAuthSession;
		$this->customerSession = $customerSession;
		
        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
        $this->_supportedCurrencyCodes[] = $this->getConfigData('currency');
    }

   /**
    * Validates payment object.
    * @return \Ebizcharge\Ebizcharge\Model\Payment 
    */
    public function validate()
    {
		$info = $this->getInfoInstance();
		return $this;
	}
	
	/**
	* Assigns preliminary form data (ebizcharge.js - getData()) to $infoInstance, which is the payment object.
	* First Load Cards from #14 and #15 and assign to Customer and set for eBiz #16
	*/
	public function assignData(\Magento\Framework\DataObject $data)
    {
		$_tmpData = $data->_data;
		
        $_serializedAdditionalData = serialize($_tmpData['additional_data']);

        $additionalDataRef = $_serializedAdditionalData;
        $additionalDataRef = unserialize($additionalDataRef);

        $_paymentToken = $additionalDataRef['paymentToken'];
        $_saveCard = isset($additionalDataRef['ebzc_save_payment']) ? $additionalDataRef['ebzc_save_payment'] : false;

        $_ebzcAvsStreet = isset($additionalDataRef['ebzc_avs_street']) ? $additionalDataRef['ebzc_avs_street'] : null;
        $_ebzcAvsZip = isset($additionalDataRef['ebzc_avs_zip']) ? $additionalDataRef['ebzc_avs_zip'] : null;

        $additionalData = $data->getData(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA);

        $infoInstance = $this->getInfoInstance();
		$infoInstance->setAdditionalInformation('payment_token', $_paymentToken);
		$infoInstance->setAdditionalInformation('save_card', $_saveCard);
		
		if (!is_object($additionalData))
		{
			$additionalData = new DataObject($additionalData ?: []);
		}

		if ($additionalData->getEbzcOption() == "new")
		{				
			$infoInstance->setCcType($additionalData->getCcType())
                ->setCcOwner($additionalData->getCcOwner())
                ->setCcLast4(substr($additionalData->getCcNumber(), -4))
                ->setCcNumber($additionalData->getCcNumber())
                ->setCcCid($additionalData->getCcCid())
                ->setCcExpMonth($additionalData->getCcExpMonth())
                ->setCcExpYear($additionalData->getCcExpYear())
                ->setCcSsIssue($additionalData->getCcSsIssue())
                ->setCcSsStartMonth($additionalData->getCcSsStartMonth())
                ->setCcSsStartYear($additionalData->getCcSsStartYear())
                ->setEbzcOption($additionalData->getEbzcOption())
                ->setEbzcCustId($additionalData->getEbzcCustId())
                ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

			$infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
			$infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
			$infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
			$infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
		}
		elseif ($additionalData->getEbzcOption() == "saved")
		{
			// initiate payment transaction start
			$tran = $this->_tran;
			$isSandBox = $this->config->isSandboxMode();

			if ($isSandBox)
			{
				$tran->usesandbox = true;
			}
			
			$tran->key = $this->config->getSourceKey();
			$tran->userid = $this->config->getSourceId();
			$tran->pin = $this->config->getSourcePin();
			$tran->software = 'Ebizcharge_Ebizcharge 1.0.0';
			// initiate payment transaction end
			
			$wsdl = $this->_tran->_getWsdlUrl();
			$ueSecurityToken = $this->_tran->_getUeSecurityToken();
			$client = new \Zend\Soap\Client($wsdl,$this->_tran->SoapParams());

			$methodProfiles = $client->GetCustomerPaymentMethodProfile(
				array(
				'securityToken' => $ueSecurityToken,
				'customerToken' => $additionalData->getEbzcCustId(),
				'paymentMethodId' => $additionalData->getEbzcMethodId()
			));

			$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfileResult;
			
			$infoInstance->setEbzcOption($additionalData->getEbzcOption())
            	->setEbzcMethodId($additionalData->getEbzcMethodId())
                ->setEbzcCustId($additionalData->getEbzcCustId())
                ->setCcType($paymentMethods->CardType)
                ->setCcOwner($paymentMethods->MethodName)
                ->setCcLast4(substr($paymentMethods->CardNumber, -4))
                ->setCcNumber($paymentMethods->CardNumber)
                ->setCcExpMonth(substr($paymentMethods->CardExpiration, 5, 2))
                ->setCcExpYear(substr($paymentMethods->CardExpiration, 0, 4))
                ->setCcCid($additionalData->getCcCid())
                ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
			$infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
			$infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
			$infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
			
		}
		elseif ($additionalData->getEbzcOption() == "update")
		{
			// initiate payment transaction start
			$tran = $this->_tran;
			$isSandBox = $this->config->isSandboxMode();

			if ($isSandBox)
			{
				$tran->usesandbox = true;
			}
			
			$tran->key = $this->config->getSourceKey();
			$tran->userid = $this->config->getSourceId();
			$tran->pin = $this->config->getSourcePin();
			$tran->software = 'Ebizcharge_Ebizcharge 1.0.0';
			// initiate payment transaction end
			
			$wsdl = $this->_tran->_getWsdlUrl();
			$ueSecurityToken = $this->_tran->_getUeSecurityToken();
			$client = new \Zend\Soap\Client($wsdl,$this->_tran->SoapParams());

			try {
				$methodProfiles = $client->GetCustomerPaymentMethodProfile(
					array(
					'securityToken' => $ueSecurityToken,
					'customerToken' => $additionalData->getEbzcCustId(),
					'paymentMethodId' => $additionalData->getEbzcMethodId()
				));

				$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfileResult;
				
			} catch (Exception $ex) {
				throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: update Method:' . $ex->getMessage()));
			}
			
			$infoInstance->setEbzcOption($additionalData->getEbzcOption())
            	->setEbzcMethodId($additionalData->getEbzcMethodId())
                ->setEbzcCustId($additionalData->getEbzcCustId())
                ->setCcType($paymentMethods->CardType)
                ->setCcOwner($paymentMethods->MethodName)
                ->setCcLast4(substr($paymentMethods->CardNumber, -4))
                ->setCcNumber($paymentMethods->CardNumber)
                ->setCcExpMonth($additionalData->getCcExpMonth())
                ->setCcExpYear($additionalData->getCcExpYear())
                ->setCcCid($additionalData->getCcCid())
                ->setEbzcAvsStreet($additionalData->getEbzcAvsStreet())
                ->setEbzcAvsZip($additionalData->getEbzcAvsZip())
                ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
			$infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
			$infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
			$infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
			$infoInstance->setAdditionalInformation('ebzc_avs_street', $_ebzcAvsStreet);
			$infoInstance->setAdditionalInformation('ebzc_avs_zip', $_ebzcAvsZip);
		}
		else
		{
			$infoInstance->setEbzcSavePayment($additionalData->getEbzcSavePayment());
			$infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
			return parent::assignData($additionalData);
		}

		return $this;
    }

    /**
     * Authorizes payment.
     *
     * @param \Magento\Payment\Model\InfoInterface
     * @param float
     * @return \Ebizcharge\Ebizcharge\Model\Payment
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
		// initialize transaction object
		$tran = $this->_initTransaction($payment);		
		// general payment data
		$tran->cardholder = $payment->getCcOwner();
		$tran->card = $payment->getCcNumber();
		$tran->cardtype = $payment->getCcType();
		$tran->exp = $payment->getCcExpMonth() . substr($payment->getCcExpYear(), 2, 2);
		$tran->cvv2 = $payment->getCcCid();
		$tran->amount = $amount;
		//$tran->ebzc_avs_street = $payment->getEbzcAvsStreet();
		//$tran->ebzc_avs_zip = $payment->getEbzcAvsZip();
		
		if ($this->getConfigData('custreceipt'))
		{
			$tran->custreceipt = true;
			$tran->custreceipt_template = $this->getConfigData('custreceipt_template');
		}
		
		// if order exists,  add order data
		$order = $payment->getOrder();

		if (!empty($order))
		{
			$orderid = $order->getIncrementId();
			$tran->invoice = $orderid;
			$tran->orderid = $orderid;
			$tran->ponum = $orderid;
			$tran->ip = $order->getRemoteIp();
			$tran->custid = $order->getCustomerId();
			$tran->email = $order->getCustomerEmail();
			
			$tran->tax = $order->getTaxAmount();
			$tran->shipping = $order->getShippingAmount();
			
			// avs data
			list($avsstreet) = $order->getBillingAddress()->getStreet();
			$tran->street = $avsstreet;
			$tran->zip = $order->getBillingAddress()->getPostcode();
			
			$tran->description = ($this->getConfigData('description') ? str_replace('[orderid]', $orderid, $this->getConfigData('description')) : "Magento Order #" . $orderid);
			
			// New Function #1 added by IF
			// set order shipping info
            $tran->setOrderShipping($order);
			// New Function #2 added by IF
    		// add order billing info
			$tran->setOrderBilling($order);
			
			foreach ($order->getAllVisibleItems() as $item)
			{
				$tran->addLine($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQtyOrdered(), $item->getTaxAmount());
                $tran->addLineItem($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQtyOrdered(), $item->getTaxAmount());
			}

			if ($tran->custid == null) 
			{
				$tran->custid = "Guest";
			}
		}
		
		if ($this->getConfigData('payment_action') == self::ACTION_AUTHORIZE && $this->_authMode != 'capture')
		{
			$tran->command = 'authonly';
		}
		else
		{
			$tran->command = 'sale';
		}
		
		// get magento customer session
		if (!$order->getCustomerId())
		{
			// Processing a guest checkout = Function Change #1 Ebiz Method Senario #1 , Useage#1
			$tran->RunTransaction();
		}
		else
		{
			// For loggedin Customer
			if ($payment->getAdditionalInformation('ebzc_option') == 'new')
            {
				//new method added by customer
				if ($this->getConfigData('save_payment') || $payment->getAdditionalInformation('ebzc_save_payment'))
				{
                    if ($payment->getAdditionalInformation('ebzc_cust_id') == null)
                    {
                    	// AddCustomer, AddPayementMethod Function Change #2 Ebiz Method Senario #2
						$tran->TokenProcess($order->getCustomerId(), $payment);
                    }
                    else
                    {
                    	// SearchCustomer, AddPayementMethod Function Change #3 Ebiz Method Senario #3 
						$tran->NewPaymentProcess($order->getCustomerId(), $payment->getAdditionalInformation('ebzc_cust_id'), $payment);
                    }
				}
				else
				{
					// Processing a guest checkout = Function Change #1 Ebiz Method Senario #1, Useage#2
					$tran->RunTransaction();
				}
			}
            elseif ($payment->getAdditionalInformation('ebzc_option') == 'saved')
			{
				// Existing payment method selected by customer
				// Function Change #4 Ebiz Method Senario #4
				$tran->SavedProcess($payment->getAdditionalInformation('ebzc_cust_id'), $payment->getAdditionalInformation('ebzc_method_id'));
            }
			elseif ($payment->getAdditionalInformation('ebzc_option') == 'update')
			{
				// Existing payment method selected by customer
				// Function Change #5 Ebiz Method Senario #5
				$tran->UpdateProcess($payment->getAdditionalInformation('ebzc_cust_id'), $payment->getAdditionalInformation('ebzc_method_id'), $payment);
			}
			else
			{
				//first time processing the transaction
				if ($this->getConfigData('save_payment') || $payment->getAdditionalInformation('ebzc_save_payment'))
				{
					// AddCustomer, AddPayementMethod Function Change #2 Ebiz Method Senario #2 Done
					$tran->TokenProcess($order->getCustomerId(), $payment);
				}
				else
				{
					// Processing a guest checkout = Function Change #1 Ebiz Method Senario #1 Done, Useage#3
					$tran->RunTransaction();
				}
			}
		}
		
		// store response variables
		$payment->setCcApproval($tran->authcode)
			->setCcTransId($tran->refnum)
			->setCcAvsStatus($tran->avs_result_code)
			->setCcCidStatus($tran->cvv2_result_code);
			
		// add the special ebzc fields to the database
		$payment->getMethodInstance()->getInfoInstance()->setEbzcCustId($payment->getAdditionalInformation('ebzc_cust_id'));
		$payment->getMethodInstance()->getInfoInstance()->setEbzcMethodId($payment->getAdditionalInformation('ebzc_method_id'));
		$payment->getMethodInstance()->getInfoInstance()->setEbzcSavePayment($payment->getAdditionalInformation('ebzc_save_payment'));
		$payment->getMethodInstance()->getInfoInstance()->setEbzcOption($payment->getAdditionalInformation('ebzc_option'));
		$payment->getMethodInstance()->getInfoInstance()->setEbzcAvsStreet($payment->getAdditionalInformation('ebzc_avs_street'));
		$payment->getMethodInstance()->getInfoInstance()->setEbzcAvsZip($payment->getAdditionalInformation('ebzc_avs_zip'));
		//$payment->getMethodInstance()->getInfoInstance()->setEbzcAvsStreet($tran->street);
		//$payment->getMethodInstance()->getInfoInstance()->setEbzcAvsZip($tran->zip);

		if ($tran->resultcode == 'A')
		{
			if ($this->getConfigData('payment_action') == self::ACTION_AUTHORIZE)
			{
				$payment->setLastTransId('0');
			}
			else
			{
				$payment->setLastTransId($tran->refnum);
			}
		
			if (!$payment->getParentTransactionId() || $tran->refnum != $payment->getParentTransactionId())
			{
				$payment->setTransactionId($tran->refnum);
			}

			$payment->setIsTransactionClosed(0)
				->setTransactionAdditionalInfo('trans_id', $tran->refnum);
				
			$payment->setStatus(self::STATUS_APPROVED);
		
		}
		elseif ($tran->resultcode == 'D')
		{
			throw new \Magento\Framework\Exception\LocalizedException(__('Payment authorization transaction has been declined:  ' . $tran->error));
        }
        else
        {
			throw new \Magento\Framework\Exception\LocalizedException(__('Payment authorization error:  ' . $tran->error . '(' . $tran->errorcode . ')'));
        }
		
		return $this;
    }

    /**
     * Processes quicksale.
     *
     * @param \Magento\Payment\Model\InfoInterface
     * @param float
     * @return \Ebizcharge\Ebizcharge\Model\Payment
     */
	public function quicksale(\Magento\Payment\Model\InfoInterface $payment, $amount) 
	{
        if (!$payment->getLastTransId())
		{
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to find previous transaction to reference'));
		}
        // initialize transaction object
        $tran = $this->_initTransaction($payment);
        $tran->command = 'capture';
        $tran->amount = $amount;

        if ($this->getConfigData('custreceipt')) 
		{
            $tran->custreceipt = true;
            $tran->custreceipt_template = $this->getConfigData('custreceipt_template');
        }

        $orderid = $payment->getOrder()->getIncrementId();
        $tran->description = ($this->getConfigData('description') ?
            str_replace('[orderid]', $orderid, $this->getConfigData('description')) :
            "Order #" . $orderid);

        $tran->setTransactionData($payment);

        $tran->RunTransaction();

        if ($tran->resultcode == 'A') 
		{
            if ($tran->refnum)
                $payment->setLastTransId($tran->refnum);
            $payment->setStatus(self::STATUS_APPROVED);

            if (!$payment->getParentTransactionId() ||
                    $tran->refnum != $payment->getParentTransactionId()) {
                $payment->setTransactionId($tran->refnum);
            }
            $payment->setIsTransactionClosed(0)
                    ->setTransactionAdditionalInfo('trans_id', $tran->refnum);
        } 
		elseif ($tran->resultcode == 'D') 
		{
            throw new \Magento\Framework\Exception\LocalizedException(__('Payment authorization transaction has been declined:  ' . $tran->error));
        } 
		else 
		{
            throw new \Magento\Framework\Exception\LocalizedException(__('Payment authorization error:  ' . $tran->error . '(' . $tran->errorcode . ')'));
        }

        return $this;
    }

	/**
     * Refunds payment.
     *
     * @param \Magento\Payment\Model\InfoInterface
     * @param float
     * @return \Ebizcharge\Ebizcharge\Model\Payment
     * @throws \Magento\Framework\Validator\Exception
	 * order -> invoice -> credit memo -> refund
     */
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount) 
	{

        if (!$payment->getLastTransId())
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to find previous transaction to reference'));

        $error = false;
        $tran = $this->_initTransaction($payment);

        $tran->command = 'refund';
        $tran->amount = $amount;
        if ($this->getConfigData('custreceipt')) {
            $tran->custreceipt = true;
            $tran->custreceipt_template = $this->getConfigData('custreceipt_template');
        }

        $orderId = $payment->getOrder()->getIncrementId();
        $tran->description = ($this->getConfigData('description') ?
            str_replace('[orderid]', $orderId, $this->getConfigData('description')) :
            "Order #" . $orderId);

        $tran->setTransactionData($payment);

        if (!$tran->RefundTransaction()) 
		{

            $payment->setStatus(self::STATUS_ERROR);
            $error = __('Error in authorizing the payment: ' . $tran->error);
			throw new \Magento\Framework\Exception\LocalizedException(__('Payment Declined: ' . $tran->error . $tran->errorcode));
        } 
		else 
		{
            $payment->setStatus(self::STATUS_APPROVED);
            if ($tran->refnum != $payment->getParentTransactionId()) {
                $payment->setTransactionId($tran->refnum);
            }
            $shouldCloseCaptureTransaction = $payment->getOrder()->canCreditmemo() ? 0 : 1;
            $payment->setIsTransactionClosed(1)
                    ->setShouldCloseParentTransaction($shouldCloseCaptureTransaction)
                    ->setTransactionAdditionalInfo('trans_id', $tran->refnum);
        }

        if ($error !== false) {
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }
        return $this;
    }
	
    /**
     * Captures payment.
     *
     * @param \Magento\Payment\Model\InfoInterface
     * @param float
     * @return \Ebizcharge\Ebizcharge\Model\Payment
     * @throws \Magento\Framework\Validator\Exception
	 * n authorised -> invoiced -> online capture
     */
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount) 
	{
        // we have already captured the original auth,  we need to do full sale
        if ($payment->getLastTransId() && $payment->getOrder()->getTotalPaid() > 0) {
            return $this->quicksale($payment, $amount);
        }
        // if we don't have a transid than we are need to authorize
        if (!$payment->getParentTransactionId()) {
            $this->_authMode = 'capture';
            return $this->authorize($payment, $amount);
        }

        $tran = $this->_initTransaction($payment);
        $tran->command = 'capture';
        $tran->amount = $amount;

        $orderId = $payment->getOrder()->getIncrementId();
        $tran->description = ($this->getConfigData('description') ?
            str_replace('[orderid]', $orderId, $this->getConfigData('description')) :
            "Order #" . $orderId);

        $tran->setTransactionData($payment);

        $tran->RunTransaction();

        // look at result code
        if ($tran->resultcode == 'A') 
		{
            $payment->setStatus(self::STATUS_APPROVED);
            $payment->setLastTransId($tran->refnum);

            if (!$payment->getParentTransactionId() ||
                    $tran->refnum != $payment->getParentTransactionId()) {
                $payment->setTransactionId($tran->refnum);
            }
            $payment->setIsTransactionClosed(0)
                    ->setTransactionAdditionalInfo('trans_id', $tran->refnum);

            return $this;
        } 
		elseif ($tran->resultcode == 'D') 
		{
			throw new \Magento\Framework\Exception\LocalizedException(__('Payment authorization transaction has been declined: ' . $tran->error));
        } 
		else 
		{
			throw new \Magento\Framework\Exception\LocalizedException(__('Payment authorization error: ' . $tran->error . '(' . $tran->errorcode . ')'));
        }
    }

	public function canVoid()
	{
		return true;
    }
	
	/**
     * Voids transaction.
     *
     * @param \Magento\Payment\Model\InfoInterface
     * @return \Ebizcharge\Ebizcharge\Model\Payment
     */

	public function void(\Magento\Payment\Model\InfoInterface $payment) 
	{
        if ($payment->getCcTransId()) 
		{
            $order = $payment->getOrder();
            $tran = $this->_initTransaction($payment);
            $tran->amount = $order->getGrandTotal();
            $tran->command = 'creditvoid';

            $tran->setTransactionData($payment);

            $tran->RunTransaction();

            if ($tran->resultcode == 'A') 
			{
                $payment->setStatus(self::STATUS_SUCCESS);
            } 
			elseif ($tran->resultcode == 'D') 
			{
                $payment->setStatus(self::STATUS_ERROR);
				throw new \Magento\Framework\Exception\LocalizedException(__('Payment authorization transaction has been declined: ' . $tran->error));
            } 
			else 
			{
                $payment->setStatus(self::STATUS_ERROR);
				throw new \Magento\Framework\Exception\LocalizedException(__('Payment authorization error: '. $tran->error . '(' . $tran->errorcode . ')'));
            }
        } 
		else 
		{
            $payment->setStatus(self::STATUS_ERROR);
			throw new \Magento\Framework\Exception\LocalizedException(__('Invalid transaction id '));
        }
        return $this;
    }
	
	/**
     * Cancels transaction.
     *
     * @param \Magento\Payment\Model\InfoInterface
     * @return \Ebizcharge\Ebizcharge\Model\Payment
	 * order -> dropdown -> cancel order
     */
	
	public function cancel(\Magento\Payment\Model\InfoInterface $payment) 
	{
        if ($payment->getCcTransId()) 
		{
            $order = $payment->getOrder();
			$tran = $this->_initTransaction($payment);
			$tran->amount = $order->getGrandTotal();
            $tran->command = 'creditvoid';

            $tran->setTransactionData($payment);

            if ($tran->RunTransaction()) 
			{
                return $this;
            } 
			else
			{
				throw new \Magento\Framework\Exception\LocalizedException(__('Transaction not void'));
			}
        } 
		else 
		{
            $payment->setStatus(self::STATUS_ERROR);
			throw new \Magento\Framework\Exception\LocalizedException(__('Invalid transaction id '));
        }
        return $this;
    }
	
	 /**
     * Setup the ebizcharge transaction api class.
     *
     * Much of this code is common to all commands
     *
     * @param Mage_Sales_Model_Document $pament
     * @return Mage_Ebizcharge_Model__tran
     */
    protected function _initTransaction(\Magento\Payment\Model\InfoInterface $payment)
    {
        $tran = $this->_tran;

        if ($this->getConfigData('sandbox'))
        {
            $tran->usesandbox = true;
        }
		$tran->key = $this->getConfigData('sourcekey');
		$tran->userid = $this->getConfigData('sourceid');
        $tran->pin = $this->getConfigData('sourcepin');
        $tran->software = 'Ebizcharge_Ebizcharge 1.0.0';
        return $tran;
    }
	
	/**
	* Returns Ebizcharge customer ID.
	* #1 for delete customer card
	*/
	public function getEbzcCustId()
	{
		if ($this->backendAuthSession->isLoggedIn())
		{
            $customerId = $this->sessionQuote->getCustomerId();
        }
        else
        {
            $customerId = $this->customerSession->getId();
        }

        $_ebzc_cust_id = $this->_token->getCollection()->addFieldToFilter('mage_cust_id', $customerId)
			->getFirstItem()
			->getEbzcCustId();
        
        return $_ebzc_cust_id;
    }
	
	/**
	* Returns saved payment methods.
	* getSavedCards Load in Dropdown Frontend #14 added by IF Done
	*/
	public function getSavedCards()
	{
        if ($this->backendAuthSession->isLoggedIn())
        {
            $customerId = $this->sessionQuote->getCustomerId();
        }
        else
        {
            $customerId = $this->customerSession->getId();
        }
        
		$_ebzc_cust_id = $this->_token->getCollection()->addFieldToFilter('mage_cust_id', $customerId)
			->getFirstItem()
			->getEbzcCustId();
		
		$storedCards = [];

		if ($_ebzc_cust_id)
		{
			$tran = $this->_tran;
			$isSandBox = $this->config->isSandboxMode();

			if ($isSandBox)
			{
				$tran->usesandbox = true;
			}
			
			$tran->key = $this->config->getSourceKey();
			$tran->userid = $this->config->getSourceId();
			$tran->pin = $this->config->getSourcePin();
			$tran->software = 'Ebizcharge_Ebizcharge 1.0.0';
			
			$wsdl = $tran->_getWsdlUrl();
			$ueSecurityToken = $tran->_getUeSecurityToken();
			$client = new \Zend\Soap\Client($wsdl,$tran->SoapParams());

			try {
				$methodProfiles = $client->getCustomerPaymentMethodProfiles(
					array(
					'securityToken' => $ueSecurityToken,
					'customerToken' => $_ebzc_cust_id
				));

//				if(count($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile) > 1) 
//				{
//					$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
//				} else {
//					$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult;
//				}				
//				return $paymentMethods;
				//$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
				
				
				if (!isset ($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile))
				{
					$paymentMethods = null;
				} else {
					$paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
				}
				return $paymentMethods;
				
			} catch (Exception $ex) {
				return NULL;
				//throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: ' . $ex->getMessage()));
			}
			
			foreach ($paymentMethods as $method)
			{
				$storedCards[] = [
					'MethodID' => $method->MethodID,
					'SecondarySort' => $method->SecondarySort,
					'MethodName' => $method->MethodName,
					'CardExpiration' => $method->CardExpiration
					];
			}
		}
		
        return $storedCards;
    }
	
	//---------- Unknown Functions ----------------
    /**
    * Checks whether CVV is required.
    */
    public function hasVerification()
    {
		$info = $this->getInfoInstance();

		if ($info->getEbzcOption())
		{
			if ($this->config->getArea() =='adminhtml')
			{
				if ($this->getConfigData('request_card_code_admin'))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			elseif ($this->config->getArea() == 'frontend')
			{
				if ($this->getConfigData('request_card_code'))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			$configData = $this->getConfigData('useccv');
			
			if (is_null($configData))
			{
				return true;
	        }
			
			return (bool) $configData;
		}
    }

	public function getConfigInterface()
    {
        return $this;
    }
	
	/**
     * Check whether an Ebizcharge customer ID is
     * associated with current magento customer ID
     *
     * @return boolean
     */
    public function hasToken()
    {
		if ($this->backendAuthSession->isLoggedIn())
		{
            $customerId = $this->sessionQuote->getCustomerId();
        }
        else
        {
            $customerId = $this->customerSession->getId();
        }

        $_ebzc_cust_id = $this->_token->getCollection()->addFieldToFilter('mage_cust_id', $customerId)
			->getFirstItem()
			->getEbzcCustId();

        return empty($_ebzc_cust_id) ? FALSE : TRUE;
    }
	
    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;

    	if ($quote && ($quote->getBaseGrandTotal() < $this->_minAmount || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount)))
        {
            return false;
        }

        $hasSourceKey = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcekey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$hasSourceKey)
        {
           	return false;
        }

        //return parent::isAvailable($quote);
    }
}