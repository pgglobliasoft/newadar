<?php

namespace WeltPixel\Quickview\Controller\Catalog\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class ProView extends \Magento\Catalog\Controller\Product
{  

    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, 
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Sttl\Adaruniforms\Helper\Sap $saphelper,
        PageFactory $resultPageFactory
    ) {
        $this->viewHelper = $viewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_productRepository = $productRepository;
        $this->saphelper = $saphelper;
        parent::__construct($context);
    }



    public function execute()
    {
      
        
        $productId = (int) $this->getRequest()->getParam('id'); 
        $product =  $this->_productRepository->getById($productId);
        $parent_style =  $product->getSku(); 
        $StyleInventoryStatus = json_encode($this->saphelper->getStyleInventoryStatus($parent_style));
        try {
                    $response = [
                            'errors' => false,
                            'parent_style' => $parent_style ,
                            'parent_color_data' => $StyleInventoryStatus,
                            'message' => 'Fetch data successfully.'
                    ]; 
            } catch (\Exception $e) {
                $response = [
                    'errors' => true,
                    'message' => __('Invalid data.').$e->getMessage()
                ];
            }
        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($response);


        // return $result;
    }
}
