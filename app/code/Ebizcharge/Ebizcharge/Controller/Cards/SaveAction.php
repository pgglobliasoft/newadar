<?php
/**
* Saves a payment method to the customer's saved payment methods. This is
* passed the data from the "Add New Credit Card" form.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Result\PageFactory;
// new added
use Magento\Framework\App\Bootstrap;
use Magento\framework\Exception\ValidatorException;

class SaveAction extends \Magento\Customer\Controller\Address
{
    protected $regionFactory;
	protected $helperData;
	protected $token;
	protected $_tran;
	protected $_scopeConfig;
    protected $_paymentconfig;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param FormFactory $formFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param DataObjectProcessor $dataProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param RegionFactory $regionFactory
     * @param HelperData $helperData
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        FormFactory $formFactory,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        DataObjectProcessor $dataProcessor,
        DataObjectHelper $dataObjectHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        RegionFactory $regionFactory,
        HelperData $helperData,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Payment\Model\Config $paymentconfig,
		\Ebizcharge\Ebizcharge\Model\Token $token,
		\Ebizcharge\Ebizcharge\Model\TranApi $tranApi

		)
    {
        $this->regionFactory = $regionFactory;
        $this->helperData = $helperData;
        $this->token = $token;
        $this->_tran = $tranApi;
		$this->_scopeConfig = $scopeConfig;
		$this->_paymentconfig = $paymentconfig;
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory);
    }

    /**
     * Adds the new payment method to the customer's account, and
     * redirects the user to the "Manage My Payment Methods" page.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
		$redirectUrl = null;
		$live_ebzc_cust_id = '';

        if (!$this->_formKeyValidator->validate($this->getRequest()))
        {
            return $this->resultRedirectFactory->create()->setPath('*/*/listaction');
        }
		
		$existingAddressData = [];
		
		$addressForm = $this->_formFactory->create('customer_address', 'customer_address_edit', $existingAddressData);
        $addressData = $addressForm->extractData($this->getRequest());
        $billing = $addressForm->compactData($addressData);
		
		$this->updateRegionData($billing);
		
		if (!isset($billing['street'][1]))
		{
			$billing['street'][1] = 'N/A';
			$billingAVS = $billing['street'][0];
			
		} else {
			
			$billing['street'][1] = $billing['street'][1];
			$billingAVS = $billing['street'][0] . ' ' . $billing['street'][1];
		}
		
		$paymentTypes = $this->_paymentconfig->getCcTypes();
		$payment = $this->getRequest()->getParam('payment');

		$default = isset($payment['default']) ? true : false;

		$MethodName = $payment['cc_type'];
		
		foreach ($paymentTypes as $code => $text)
		{
			if ($code == $payment['cc_type'])
			{
				$MethodName = $text;
			}
		}

		// Verifies that the expiration date has not already passed.
		$checkExpiration = $payment['cc_exp_year'] . "-" . $payment['cc_exp_month'];
		$currentDate = date('Y-m');

		if (strtotime($currentDate) > strtotime($checkExpiration))
		{
			$this->messageManager->addError(__('Unable to save the payment method. The credit card was expired.'));
			$url = $this->_buildUrl('*/*/listaction');
			return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
		}
		
		//----- New Payment Method --------
		$paymentMethod = array(
			'MethodName' =>  $MethodName . ' ' . substr($payment['cc_number'], -4) . ' - ' . $payment['cc_holder'],
            'SecondarySort' => $default ? 0 : 1,
            'Created' => date('Y-m-d\TH:i:s'),
            'Modified' => date('Y-m-d\TH:i:s'),
            'AvsStreet' => $billingAVS,
            'AvsZip' => $billing['postcode'],
            'CardCode' => isset($payment['cc_cid']) ? $payment['cc_cid'] : '',
            'CardExpiration' => $checkExpiration,
            'CardNumber' => $payment['cc_number'],
            'CardType' => $payment['cc_type']
		);
		//----- New Payment Method --------
		
		// Transaction Parameters Start
		$isSandBox = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

		if ($isSandBox)
		{
			$this->_tran->usesandbox = true;
		}
		
		$this->_tran->key = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcekey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$this->_tran->userid = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourceid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_tran->pin = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcepin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $this->_tran->software = 'Ebizcharge_Ebizcharge 1.0.0';

		$wsdl = $this->_tran->_getWsdlUrl();
		$ueSecurityToken = $this->_tran->_getUeSecurityToken();
		$client = new \Zend\Soap\Client($wsdl,$this->_tran->SoapParams());
		// Transaction Parameters End
		
		$mage_cust_id = $this->_getSession()->getCustomerId();

		$ebzc_cust_id = $this->token->getCollection()->addFieldToFilter('mage_cust_id', $mage_cust_id)
			->getFirstItem()
			->getEbzcCustId();
		
		$customer_data = $this->_getSession()->getCustomer()->getData();
		
		$local_ebzc_cust_id = $ebzc_cust_id;
		$live_search_customer = $this->_tran->SearchCustomers($mage_cust_id);
		
		if ($live_search_customer == 'Not Found')
		{
			# Case 1 Local = No, Live = No
			if (($local_ebzc_cust_id == null) && ($live_search_customer == 'Not Found'))
			{
				$customerData = array(
				'CustomerId' => $mage_cust_id,
				'FirstName' => $customer_data['firstname'],
				'LastName' => $customer_data['lastname'],
				'CompanyName' => $billing['company'],
				'Phone' => isset($billing['telephone']) ? $billing['telephone'] : '',
				'CellPhone' => isset($billing['telephone']) ? $billing['telephone'] : '',
				'Fax' => isset($billing['fax']) ? $billing['fax'] : '',
				'Email' => $customer_data['email'],
				'WebSite' => '',
				'BillingAddress' => array(
					'FirstName' => $billing['firstname'],
					'LastName' => $billing['lastname'],
					'CompanyName' => $billing['company'],
					'Address1' => $billing['street'][0],
					'Address2' => $billing['street'][1],
					'City' => $billing['city'],
					'State' => $billing['region'],
					'ZipCode' => $billing['postcode'],
					'Country' => $billing['country_id']),
				'ShippingAddress' => array(
					'FirstName' => $billing['firstname'],
					'LastName' => $billing['lastname'],
					'CompanyName' => $billing['company'],
					'Address1' => $billing['street'][0],
					'Address2' => $billing['street'][1],
					'City' => $billing['city'],
					'State' => $billing['region'],
					'ZipCode' => $billing['postcode'],
					'Country' => $billing['country_id']),
				'PaymentMethodProfiles' => $paymentMethod
				);
			
				try {
					$addCustomerEbiz = $client->AddCustomer(
							array(
							'securityToken' => $ueSecurityToken,
							'customer' => $customerData
						));

					if (($addCustomerEbiz->AddCustomerResult->Status) == 'Success')
					{
						$GetCustomerToken = $client->GetCustomerToken(
							array(
							'securityToken' => $ueSecurityToken,
							'CustomerId' => $mage_cust_id,
							'customerInternalId' => $addCustomerEbiz->AddCustomerResult->CustomerInternalId
						));
						$GetCustomerTokenResult = $GetCustomerToken->GetCustomerTokenResult;
						// Save token in ebizcharge_token table
						//$this->_tran->addEbzcToken($mage_cust_id, $GetCustomerTokenResult);
						$token = $this->token;
						$token->setMageCustId($mage_cust_id);
						$token->setEbzcCustId($GetCustomerTokenResult);
						$token->save();
						// add new payment method
							$paymentMethodProfile = $client->addCustomerPaymentMethodProfile(
								array(
								'securityToken' => $ueSecurityToken,
								'customerInternalId' => $addCustomerEbiz->AddCustomerResult->CustomerInternalId,
								'paymentMethodProfile' => $paymentMethod
							));

							$paymentMethodId = $paymentMethodProfile->AddCustomerPaymentMethodProfileResult;

							if (isset($paymentMethodId)) {
								$this->messageManager->addSuccess(__('Credit card saved successfully.'));
								$url = $this->_buildUrl('*/*/listaction', ['_secure' => true]);
							} else {
								$this->messageManager->addError(__('Unable to obtain Method ID.'));
							}

					}
					else
					{
						$url = $this->_buildUrl('*/*/listaction');
						$this->messageManager->addError(__('Unable to save customer payment method.'));
					}
				} 
				catch (\Exception $ex) {
					$this->messageManager->addException($ex, __('Unable to save customer payment method.'.$ex->getMessage()));
				}
			}
			# Case 2 Local = Yes, Live = No
			elseif (($local_ebzc_cust_id != null) && ($live_search_customer == 'Not Found'))
			{
				$customerData = array(
				'CustomerId' => $mage_cust_id,
				'FirstName' => $customer_data['firstname'],
				'LastName' => $customer_data['lastname'],
				'CompanyName' => $billing['company'],
				'Phone' => isset($billing['telephone']) ? $billing['telephone'] : '',
				'CellPhone' => isset($billing['telephone']) ? $billing['telephone'] : '',
				'Fax' => isset($billing['fax']) ? $billing['fax'] : '',
				'Email' => $customer_data['email'],
				'WebSite' => '',
				'BillingAddress' => array(
					'FirstName' => $billing['firstname'],
					'LastName' => $billing['lastname'],
					'CompanyName' => $billing['company'],
					'Address1' => $billing['street'][0],
					'Address2' => $billing['street'][1],
					'City' => $billing['city'],
					'State' => $billing['region'],
					'ZipCode' => $billing['postcode'],
					'Country' => $billing['country_id']),
				'ShippingAddress' => array(
					'FirstName' => $billing['firstname'],
					'LastName' => $billing['lastname'],
					'CompanyName' => $billing['company'],
					'Address1' => $billing['street'][0],
					'Address2' => $billing['street'][1],
					'City' => $billing['city'],
					'State' => $billing['region'],
					'ZipCode' => $billing['postcode'],
					'Country' => $billing['country_id']),
				'PaymentMethodProfiles' => $paymentMethod
				);
			
				try {
					$addCustomerEbiz = $client->AddCustomer(
							array(
							'securityToken' => $ueSecurityToken,
							'customer' => $customerData
						));

					if (($addCustomerEbiz->AddCustomerResult->Status) == 'Success')
					{
						$GetCustomerToken = $client->GetCustomerToken(
							array(
							'securityToken' => $ueSecurityToken,
							'CustomerId' => $mage_cust_id,
							'customerInternalId' => $addCustomerEbiz->AddCustomerResult->CustomerInternalId
						));
						$GetCustomerTokenResult = $GetCustomerToken->GetCustomerTokenResult;
						// Update token in ebizcharge_token table
						$this->_tran->runUpdateCustomer('ebizcharge_token', $mage_cust_id, $GetCustomerTokenResult);
						// add new payment method
							$paymentMethodProfile = $client->addCustomerPaymentMethodProfile(
								array(
								'securityToken' => $ueSecurityToken,
								'customerInternalId' => $addCustomerEbiz->AddCustomerResult->CustomerInternalId,
								'paymentMethodProfile' => $paymentMethod
							));

							$paymentMethodId = $paymentMethodProfile->AddCustomerPaymentMethodProfileResult;

							if (isset($paymentMethodId)) {
								$this->messageManager->addSuccess(__('Credit card saved successfully.'));
								$url = $this->_buildUrl('*/*/listaction', ['_secure' => true]);
							} else {
								$this->messageManager->addError(__('Unable to obtain Method ID.'));
							}

					}
					else
					{
						$url = $this->_buildUrl('*/*/listaction');
						$this->messageManager->addError(__('Unable to save customer payment method.'));
					}
				} 
				catch (\Exception $ex) {
					$this->messageManager->addException($ex, __('Unable to save customer payment method.'.$ex->getMessage()));
				}
			}
			# Case 6 In all other cases default
			else
			{
				$this->messageManager->addError(__('Error occured in adding process.'));
			}
		}
		else
		{
			$live_ebzc_cust_id = $this->_tran->GetCustomerToken($mage_cust_id);
			
			# Case 5 Local = Yes, Live = Yes , Token = Same
			if (($local_ebzc_cust_id != null) && ($live_ebzc_cust_id != null) && ($local_ebzc_cust_id == $live_ebzc_cust_id))
			{
				// For Payment method add start
				try {
						// Get Customer
						$getCustomerEbizData = $client->GetCustomer(
							array(
							'securityToken' => $ueSecurityToken,
							'customerId' => $mage_cust_id
						));

							// Customer Update Start
							try {
								// Address Update start
								if($getCustomerData = $getCustomerEbizData->GetCustomerResult)
								{
									$BillingAddress = $getCustomerData->BillingAddress;
									$ShippingAddress = $getCustomerData->ShippingAddress;
									$PaymentMethodProfiles = $getCustomerData->PaymentMethodProfiles;
									$customerData = array(
											'FirstName' => $billing['firstname'],
											'LastName' => $billing['lastname'],
											'CompanyName' => $billing['company'],
											'Address1' => $billing['street'][0],
											'Address2' => $billing['street'][1],
											'City' => $billing['city'],
											'State' => $billing['region'],
											'ZipCode' => $billing['postcode'],
											'Country' => $billing['country_id']
										);
									$getCustomerData->BillingAddress = $customerData;

									$customerDataUpdated = array(
									'CustomerInternalId' => $getCustomerData->CustomerInternalId,
									'CustomerId' => $getCustomerData->CustomerId,
									'FirstName' => $getCustomerData->FirstName,
									'LastName' => $getCustomerData->LastName,
									'CompanyName' => $getCustomerData->CompanyName,
									'Phone' => $getCustomerData->Phone,
									'CellPhone' => $getCustomerData->CellPhone,
									'Fax' => $getCustomerData->Fax,
									'Email' => $getCustomerData->Email,
									'WebSite' => $getCustomerData->WebSite,
									'BillingAddress' => $getCustomerData->BillingAddress,
									'ShippingAddress' => $ShippingAddress,
									'PaymentMethodProfiles' => $PaymentMethodProfiles,
									);

									$updatedMethodProfile = $client->updateCustomer(
										array(
										'securityToken' => $ueSecurityToken,
										'customer' => $customerDataUpdated,
										//'customerID' => $mage_cust_id,
										'customerID' => $getCustomerData->CustomerId,
										'customerInternalId' => $getCustomerData->CustomerInternalId,
									));

									// For ebiz data update
									if (($updatedMethodProfile->UpdateCustomerResult->Status) == 'Success')
									//if ($updateCustomerData)
									{
										$this->messageManager->addSuccess(__('Address updated successfully.'));
									}
									else
									{
										$this->messageManager->addError(__('Address is not updated.'));
									}
									// Address Update end

								}

							} 
							catch (\Exception $ex) {
								$this->messageManager->addError(__('Exception: '.$ex->getMessage()));
							}
							// Customer Update end

						$paymentMethodProfile = $client->addCustomerPaymentMethodProfile(array(
							'securityToken' => $ueSecurityToken,
							'customerInternalId' => $getCustomerEbizData->GetCustomerResult->CustomerInternalId,
							'paymentMethodProfile' => $paymentMethod
						));

						$paymentMethodId = $paymentMethodProfile->AddCustomerPaymentMethodProfileResult;

					if (isset($paymentMethodId)) {
						$this->messageManager->addSuccess(__('Credit card saved successfully.'));
						$url = $this->_buildUrl('*/*/listaction', ['_secure' => true]);
					} else {
						$this->messageManager->addError(__('Unable to obtain Method ID.'));
					}

				} 
				catch (\Exception $ex) 
				{
					$this->messageManager->addError(__('Exception: '.$ex->getMessage()));
				}
				// For Payment method add end
			}
			# Case 4 Local = Yes, Live = Yes , Token = Diff
			elseif (($local_ebzc_cust_id != null) && ($live_ebzc_cust_id != null) && ($local_ebzc_cust_id != $live_ebzc_cust_id))
			{
				$this->messageManager->addError(__('Customer already exist.'));
			}
			# Case 6 In all other cases default
			else
			{
				$this->messageManager->addError(__('Error occured in adding process.'));
			}

		}
		
		$url = $this->_buildUrl('*/*/listaction');
		return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
    }
	
	public function execute_old()
    {
		$redirectUrl = null;

        if (!$this->_formKeyValidator->validate($this->getRequest()))
        {
            return $this->resultRedirectFactory->create()->setPath('*/*/listaction');
        }
		
		$existingAddressData = [];
		
		$addressForm = $this->_formFactory->create('customer_address', 'customer_address_edit', $existingAddressData);
        $addressData = $addressForm->extractData($this->getRequest());
        $billing = $addressForm->compactData($addressData);
		
		$this->updateRegionData($billing);
		
		if (!isset($billing['street'][1]))
		{
			$billing['street'][1] = 'N/A';
			$billingAVS = $billing['street'][0];
			
		} else {
			
			$billing['street'][1] = $billing['street'][1];
			$billingAVS = $billing['street'][0] . ' ' . $billing['street'][1];
		}
		
		$paymentTypes = $this->_paymentconfig->getCcTypes();
		$payment = $this->getRequest()->getParam('payment');

		$default = isset($payment['default']) ? true : false;

		$MethodName = $payment['cc_type'];
		
		foreach ($paymentTypes as $code => $text)
		{
			if ($code == $payment['cc_type'])
			{
				$MethodName = $text;
			}
		}

		// Verifies that the expiration date has not already passed.
		$checkExpiration = $payment['cc_exp_year'] . "-" . $payment['cc_exp_month'];
		$currentDate = date('Y-m');

		if (strtotime($currentDate) > strtotime($checkExpiration))
		{
			$this->messageManager->addError(__('Unable to save the payment method. The credit card was expired.'));
			$url = $this->_buildUrl('*/*/listaction');
			return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
		}
		
		
		//----- New Payment Method --------
		$paymentMethod = array(
			'MethodName' =>  $MethodName . ' ' . substr($payment['cc_number'], -4) . ' - ' . $payment['cc_holder'],
            'SecondarySort' => $default ? 0 : 1,
            'Created' => date('Y-m-d\TH:i:s'),
            'Modified' => date('Y-m-d\TH:i:s'),
            'AvsStreet' => $billingAVS,
            'AvsZip' => $billing['postcode'],
            'CardCode' => isset($payment['cc_cid']) ? $payment['cc_cid'] : '',
            'CardExpiration' => $checkExpiration,
            'CardNumber' => $payment['cc_number'],
            'CardType' => $payment['cc_type']
		);
		//----- New Payment Method --------
		
		$isSandBox = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

		if ($isSandBox)
		{
			$this->_tran->usesandbox = true;
		}
		
		$this->_tran->key = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcekey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$this->_tran->userid = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourceid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_tran->pin = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcepin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $this->_tran->software = 'Ebizcharge_Ebizcharge 1.0.0';

		$wsdl = $this->_tran->_getWsdlUrl();
		$ueSecurityToken = $this->_tran->_getUeSecurityToken();
		
		$mage_cust_id = $this->_getSession()->getCustomerId();

		$ebzc_cust_id = $this->token->getCollection()->addFieldToFilter('mage_cust_id', $mage_cust_id)
			->getFirstItem()
			->getEbzcCustId();
		
		$client = new \Zend\Soap\Client($wsdl,$this->_tran->SoapParams());
		
		$customer_data = $this->_getSession()->getCustomer()->getData();
		
		if ($ebzc_cust_id)
		{
			// For Payment method add start
			try {
				// Customer Update Start
				try {
					// Address Update start
					$getCustomerEbizData = $client->GetCustomer(
						array(
						'securityToken' => $ueSecurityToken,
						'customerId' => $mage_cust_id
					));
					
					if($getCustomerData = $getCustomerEbizData->GetCustomerResult)
					{
						$BillingAddress = $getCustomerData->BillingAddress;
						$ShippingAddress = $getCustomerData->ShippingAddress;
						$PaymentMethodProfiles = $getCustomerData->PaymentMethodProfiles;
						$customerData = array(
								'FirstName' => $billing['firstname'],
								'LastName' => $billing['lastname'],
								'CompanyName' => $billing['company'],
								'Address1' => $billing['street'][0],
								'Address2' => $billing['street'][1],
								'City' => $billing['city'],
								'State' => $billing['region'],
								'ZipCode' => $billing['postcode'],
								'Country' => $billing['country_id']
							);
						$getCustomerData->BillingAddress = $customerData;
						
						$customerDataUpdated = array(
						'CustomerInternalId' => $getCustomerData->CustomerInternalId,
						'CustomerId' => $getCustomerData->CustomerId,
						'FirstName' => $getCustomerData->FirstName,
						'LastName' => $getCustomerData->LastName,
						'CompanyName' => $getCustomerData->CompanyName,
						'Phone' => $getCustomerData->Phone,
						'CellPhone' => $getCustomerData->CellPhone,
						'Fax' => $getCustomerData->Fax,
						'Email' => $getCustomerData->Email,
						'WebSite' => $getCustomerData->WebSite,
						'BillingAddress' => $getCustomerData->BillingAddress,
						'ShippingAddress' => $ShippingAddress,
						'PaymentMethodProfiles' => $PaymentMethodProfiles,
						);
						
						$updatedMethodProfile = $client->updateCustomer(
							array(
							'securityToken' => $ueSecurityToken,
							'customer' => $customerDataUpdated,
							'customerID' => $ebzc_cust_id,
							'customerInternalId' => $getCustomerData->CustomerInternalId,
						));
						
						// For ebiz data update
						if (($updatedMethodProfile->UpdateCustomerResult->Status) == 'Success')
						//if ($updateCustomerData)
						{
							$this->messageManager->addSuccess(__('Address updated successfully.'));
						}
						else
						{
							$this->messageManager->addError(__('Address is not updated.'));
						}
						// Address Update end
						
					}
									
					
				} 
				catch (Exception $ex) {
					$this->messageManager->addError(__($ex->getMessage()));
				}
				// Customer Update end
				// find CustomerInternalId using SearchCustomers method
                    $searchCustomer = $client->SearchCustomers(array(
                        'securityToken' => $ueSecurityToken,
                        'customerId' => $mage_cust_id,
                        'start' => 0,
                        'limit' => 1
                    ));

                    if($ebzcCustomer = $searchCustomer->SearchCustomersResult->Customer) {
                        $paymentMethodProfile = $client->addCustomerPaymentMethodProfile(array(
                            'securityToken' => $ueSecurityToken,
                            'customerInternalId' => $ebzcCustomer->CustomerInternalId,
                            'paymentMethodProfile' => $paymentMethod
                        ));

                        $paymentMethodId = $paymentMethodProfile->AddCustomerPaymentMethodProfileResult;
                    }

					if (isset($paymentMethodId)) {
                        $this->messageManager->addSuccess(__('Credit card saved successfully.'));
						$url = $this->_buildUrl('*/*/listaction', ['_secure' => true]);
                    } else {
                        $this->messageManager->addError(__('Unable to obtain Method ID.'));
                    }
				
			} catch (Exception $ex) {
				$this->messageManager->addError(__($ex->getMessage()));
			}
			// For Payment method add end
		}
		else
		{
			$customerData = array(
				'CustomerId' => $mage_cust_id,
				'FirstName' => $customer_data['firstname'],
				'LastName' => $customer_data['lastname'],
				'CompanyName' => $billing['company'],
				'Phone' => isset($billing['telephone']) ? $billing['telephone'] : '',
				'CellPhone' => isset($billing['telephone']) ? $billing['telephone'] : '',
				'Fax' => isset($billing['fax']) ? $billing['fax'] : '',
				'Email' => $customer_data['email'],
				'WebSite' => '',
				'BillingAddress' => array(
					'FirstName' => $billing['firstname'],
					'LastName' => $billing['lastname'],
					'CompanyName' => $billing['company'],
					'Address1' => $billing['street'][0],
					'Address2' => $billing['street'][1],
					'City' => $billing['city'],
					'State' => $billing['region'],
					'ZipCode' => $billing['postcode'],
					'Country' => $billing['country_id']),
				'ShippingAddress' => array(
					'FirstName' => $billing['firstname'],
					'LastName' => $billing['lastname'],
					'CompanyName' => $billing['company'],
					'Address1' => $billing['street'][0],
					'Address2' => $billing['street'][1],
					'City' => $billing['city'],
					'State' => $billing['region'],
					'ZipCode' => $billing['postcode'],
					'Country' => $billing['country_id']),
				'PaymentMethodProfiles' => $paymentMethod
				);
			
			try {
				$addCustomerEbiz = $client->AddCustomer(
						array(
						'securityToken' => $ueSecurityToken,
						'customer' => $customerData
					));
				
				if (($addCustomerEbiz->AddCustomerResult->Status) == 'Success')
				{
					$GetCustomerToken = $client->GetCustomerToken(
						array(
						'securityToken' => $ueSecurityToken,
						'CustomerId' => $mage_cust_id,
						'customerInternalId' => $addCustomerEbiz->AddCustomerResult->CustomerInternalId
					));
					$GetCustomerTokenResult = $GetCustomerToken->GetCustomerTokenResult;
					// Save token in ebizcharge_token table
					//$this->_tran->addEbzcToken($mage_cust_id, $GetCustomerTokenResult);
					$token = $this->token;
					$token->setMageCustId($mage_cust_id);
					$token->setEbzcCustId($GetCustomerTokenResult);
					$token->save();
					// add new payment method
						$paymentMethodProfile = $client->addCustomerPaymentMethodProfile(
							array(
                            'securityToken' => $ueSecurityToken,
                            'customerInternalId' => $addCustomerEbiz->AddCustomerResult->CustomerInternalId,
                            'paymentMethodProfile' => $paymentMethod
                        ));

                        $paymentMethodId = $paymentMethodProfile->AddCustomerPaymentMethodProfileResult;
					
						if (isset($paymentMethodId)) {
							$this->messageManager->addSuccess(__('Credit card saved successfully.'));
							$url = $this->_buildUrl('*/*/listaction', ['_secure' => true]);
						} else {
							$this->messageManager->addError(__('Unable to obtain Method ID.'));
						}
				
				}
				else
				{
					$url = $this->_buildUrl('*/*/listaction');
					$this->messageManager->addError(__('Unable to save customer payment method.'));
				}
			} catch (Exception $ex) {
				$this->messageManager->addException($e, __('Unable to save customer payment method.'));
			}
		}
		
		$url = $this->_buildUrl('*/*/listaction');
		return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
    }
	
	/**
     * Updates region data.
     *
     * @param array $attributeValues
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateRegionData(&$attributeValues)
    {
        if (!empty($attributeValues['region_id']))
        {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        $regionData = [
            RegionInterface::REGION_ID => !empty($attributeValues['region_id']) ? $attributeValues['region_id'] : null,
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
                ? $attributeValues['region_code']
                : null];
		
		array_merge($attributeValues, $regionData);
    }
	
}