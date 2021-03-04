<?php
/**
* Updates the details of the edited payment method.
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


class UpdateAction extends \Magento\Customer\Controller\Address
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
		\Ebizcharge\Ebizcharge\Model\TranApi $tranApi)
    {
        $this->regionFactory = $regionFactory;
        $this->helperData = $helperData;
        $this->token = $token;
        $this->_tran= $tranApi;
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
     * Saves the updated payment information.
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
		$redirectUrl = null;

        if (!$this->_formKeyValidator->validate($this->getRequest()))
        {
            return $this->resultRedirectFactory->create()->setPath('*/*/listaction');
        }
		
		$cid = $this->getRequest()->getParam('cid');
        $mid = $this->getRequest()->getParam('mid');
		
		if ($cid && $mid)
        {			
			$ccExpMonth = $this->getRequest()->getParam('cc_exp_month');
			$ccExpYear = $this->getRequest()->getParam('cc_exp_year');
			$avsStreet = $this->getRequest()->getParam('avs_street');
			$avszip = $this->getRequest()->getParam('avs_zip');
			$cc_type = $this->getRequest()->getParam('cc_type');
			$cc_holder = $this->getRequest()->getParam('cc_holder');
			$default = $this->getRequest()->getParam('default');
			
			// transaction initiation start
			$isSandBox = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

			if ($isSandBox)
            {
				$this->_tran->usesandbox = true;
			}
			
			$this->_tran->key = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcekey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$this->_tran->userid = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourceid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$this->_tran->pin = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcepin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

			$this->_tran->software = 'Ebizcharge_Ebizcharge 1.0.0';
			// transaction initiation end
			
			$wsdl = $this->_tran->_getWsdlUrl();
			$ueSecurityToken = $this->_tran->_getUeSecurityToken();
			$client = new \Zend\Soap\Client($wsdl,$this->_tran->SoapParams());
			
			$paymentTypes = $this->_paymentconfig->getCcTypes();
			$MethodName = $cc_type;

			foreach ($paymentTypes as $code => $text)
            {
				if ($code == $cc_type) 
                {
					$MethodName = $text;
                }
			}
			
			try {
                
				//$paymentMethod = $client->getCustomerPaymentMethod($ueSecurityToken, $cid, $mid);
				$methodProfiles = $client->GetCustomerPaymentMethodProfile(
					array(
					'securityToken' => $ueSecurityToken,
					'customerToken' => $cid,
					'paymentMethodId' => $mid
				));

				$paymentMethod = $methodProfiles->GetCustomerPaymentMethodProfileResult;
				
                $paymentMethod->CardNumber = 'XXXXXX' . substr($paymentMethod->CardNumber, 6);
                $paymentMethod->MethodName = $MethodName . ' ' . substr($paymentMethod->CardNumber, -4) . ' - ' . $cc_holder;
                $paymentMethod->CardExpiration = $ccExpYear . '-' . $ccExpMonth;
                $paymentMethod->AvsStreet = $avsStreet;
                $paymentMethod->AvsZip = $avszip;

                if ($default)
                {
                    $paymentMethod->SecondarySort = $default ? 0 : 1;
                }
				
				$updatedMethodProfile = $client->updateCustomerPaymentMethodProfile(
					array(
					'securityToken' => $ueSecurityToken,
					'customerToken' => $cid,
					'paymentMethodProfile' => $paymentMethod
				));

				if (isset($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult))
                {
                    $this->messageManager->addSuccess(__('Payment method updated successfully.'));
					$url = $this->_buildUrl('*/*/listaction', ['_secure' => true]);

					return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));
                }
                else
                {
					$this->messageManager->addError(__('Unable to update payment method.'));
                }
            } catch (Exception $ex) {
				$this->messageManager->addException($ex, __('Unable to update customer payment method.'));
            }
		}
        else
        {
			$this->messageManager->addError(__('Unable to update payment method.'));
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