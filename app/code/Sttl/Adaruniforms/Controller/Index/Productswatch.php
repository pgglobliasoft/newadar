<?php

namespace Sttl\Adaruniforms\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\PageFactory;
class Productswatch extends \Magento\Framework\App\Action\Action 
{
    protected $resultForwardFactory;
    protected $resultPageFactory;
    protected $saphelper;
    protected $resultJsonFactory;
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    PageFactory $resultPageFactory,
    \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Sttl\Adaruniforms\Helper\Sap $saphelper
    )
    {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->saphelper = $saphelper;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
    	$post = $this->getRequest()->getParams();
        if(isset($post) && isset($post['productstyle']))
    	{
            $style = $post['productstyle'];
        $parent_color_data = $this->saphelper->getStyleInventoryStatus($style);
        // print_r($parent_color_data);die;
            // $parent_color_data = json_decode($post['parent_color_data'], true);
            $resultJson = $this->resultJsonFactory->create();
    		$resultPage = $this->resultPageFactory->create();
    		
            $renderDataPart = '';
    		if(isset($parent_color_data[0]) && $parent_color_data[0] != '')
            {
                $renderDataPart = $resultPage->getLayout()
                                    ->createBlock('Sttl\Adaruniforms\Block\View')
                                    ->setParentStyle($style)
                                    ->setParentColorData($parent_color_data)
                                    ->setTemplate('Magento_Catalog::product/view/product_options_swatcher.phtml')
                                    ->toHtml();
            }
           
			$response = [
                            'errors' => false,
                            'html'   => $renderDataPart,
                            'message' => __("Success.")
                            ];
            return $resultJson->setData($response);
	   }else{
         $response = [
                            'errors' => true,
                            'html'   => '',
                            'message' => __("Customer Session is expried.")
                            ];
    		//$results->setData('error', 'custom session is expried');
    	}
        print_r($response);die;
    	return $resultJson->setData($response);
    }
}