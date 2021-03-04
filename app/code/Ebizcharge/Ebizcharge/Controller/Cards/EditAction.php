<?php
/**
* Allows a customer to edit the details of a saved payment method.
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


class EditAction extends \Magento\Customer\Controller\Address
{
    protected $regionFactory;
	private $pageFactory;
    protected $helperData;
	protected $token;
	protected $_tran;
	protected $_scopeConfig;

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
		\Ebizcharge\Ebizcharge\Model\Token $token,
		\Ebizcharge\Ebizcharge\Model\TranApi $tranApi)
    {
        $this->regionFactory = $regionFactory;
        $this->helperData = $helperData;
        $this->token = $token;
        $this->_tran = $tranApi;
		$this->_scopeConfig = $scopeConfig;
		$this->pageFactory = $resultPageFactory;
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
     * Retrieves the payment method parameters, and sets up the page
     * to edit the payment method.
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
		$cid = $this->getRequest()->getParam('cid');
        $mid = $this->getRequest()->getParam('mid');
		$method = $this->getRequest()->getParam('method');

		if ($cid && $mid && $method)
        {			
			$resultPage = $this->pageFactory->create();

            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');

            if ($navigationBlock)
            {
                $navigationBlock->setActive('ebizcharge/cards/listaction');
            }
        
			$resultPage->getConfig()->getTitle()->set(__('Edit Payment Method'));

			return $resultPage;
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
                ? $attributeValues['region_code'] : null];
		
		array_merge($attributeValues, $regionData);
    }
}