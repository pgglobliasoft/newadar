<?php
namespace Sttl\Adaruniforms\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class CheckSapUserBeforeObserver implements ObserverInterface
{

    protected $_customerCollectionFactory;
    protected $dataHelper;
    protected $_responseFactory;
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Sttl\Adaruniforms\Helper\Sap $dataHelper,
        MessageManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->dataHelper = $dataHelper;
        $this->_request = $request;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->_responseFactory = $responseFactory;

    }


    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $requestData = $this->_request->getPost();
        if(isset($requestData['customer_number']) && $requestData['customer_number'] != '' && isset($requestData['webaccess_code']) &&  $requestData['webaccess_code'] != '')
        {
            $CheckSapData = "MWEB_OCRD.CardCode = '".$requestData['customer_number']."'";
            $CheckSapData .= " AND MWEB_OCRD.WebAccessCode = '".$requestData['webaccess_code']."'";
            $customerCollection = "";
            $helperData = $this->dataHelper->checkCustomerExist($CheckSapData);
             if(count($helperData) > 0) { 
                    if($helperData[0]['CardCode']){
                       $customerCollection = $helperData[0]['CardCode'];
                    }
                }

                try {
                    if(!empty($customerCollection)){
                        return true;
                    }else{
                        return false;
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->messageManager->addException($e, __('Customer Code not found please insert customer Code & Web Access Code.'));
                    return $this->redirectFactory->create()->setPath('*/*/');
                }
         }
    }

}