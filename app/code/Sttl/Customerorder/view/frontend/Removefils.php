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
				$message = "files delete.";
                $response = array();
                $customerdata = $this->saphelper->getCustomerDetails();
                $filnalHtml = '';
                if(isset($customerdata[0]))
                {
                    $customerdata = $customerdata[0];
                    $orderData = $this->saphelper->getSapOrdersData($customerdata,$po_number);

                    if(!empty($orderData[0]))
                    {
                        $orderData = $orderData[0];
                        $distinstyle = $this->saphelper->gettempOrdrstyle($orderData['Id']);
                        $values = array_map('array_pop', $distinstyle);
                        $implodedStyle = implode("','", $values);
                        $distinstyle = $this->saphelper->getsizegroup($implodedStyle); 
                        $sizegrouparray = array();
                        foreach($distinstyle as $key => $data)
                        {
                            $sizegrouparray[$data['SizeGroup']][] = $data['Style'];
                        }
                        $filnalHtml ='';
                        foreach($sizegrouparray as $key => $value)
                        {
                          $renderDataByColor = '';
                          $groupstyle =implode("','", $value);
                          $renderDataByColor = $this->saphelper->newrenderOrderItemHtml($orderData['Id'],'','','',$groupstyle);  
                          $filnalHtml .= $renderDataByColor;
                        }
                        $filnalHtml .= $this->saphelper->renderOrderItemHtmltotal($orderData['Id'],'');
                        //$OrderInfoGrid = $this->saphelper->renderOrderItemHtml($orderData['Id']);
                        //$response['html'] = $filnalHtml;
                    }
                }
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