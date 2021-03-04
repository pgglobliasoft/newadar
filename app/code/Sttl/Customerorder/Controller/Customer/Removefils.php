<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Removefils extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $session;

protected $dataObjectFactory;

protected $resultJsonFactory;

//protected $_customerRepositoryInterface;

public function __construct(
    context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
    \Magento\Framework\DataObjectFactory $dataObjectFactory,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Sttl\Adaruniforms\Helper\Sap $saphelper
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->dataObjectFactory = $dataObjectFactory;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->saphelper = $saphelper;
	
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

	$fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
	$this->mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();	
}


public function execute()
{
    $resultJson = $this->resultJsonFactory->create();
    if (!$this->session->isLoggedIn())
    {
        $response = [
            'session_distroy' => true,
            'message' => __("Customer session expired please login.")
        ];
        return $resultJson->setData($response);
    }
    else
    {
        $post = $this->getRequest()->getParams();
        $po_number = $post['po_number'];
        try {
                $filename = $this->mediaPath . "option2". DIRECTORY_SEPARATOR . $po_number . ".txt";
				@unlink($filename);
				$message = "aborted sucess.";
                $response = array();
				$filnalHtml = '';
                $response = [
                    'success' => true,
                    'message' => __($message),
                    'html' => $filnalHtml
                ];


                                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $message = $e->getMessage();
                
                $response = [
                    'errors' => true,
                    'message' => __($message)
                ];
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $response = [
                    'errors' => true,
                    'message' => __($message)
                ];
            }
		    return $resultJson->setData($response);
    }
}
    
    
}