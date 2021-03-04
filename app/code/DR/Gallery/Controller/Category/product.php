<?php
namespace DR\Gallery\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Product extends \Magento\Framework\App\Action\Action
    {
    protected $resultPageFactory;

    protected $session;


    public function __construct(
            \Magento\Framework\App\Action\Context $context,   
            PageFactory $resultPageFactory,
            \Magento\Catalog\Model\Product $product,
            ForwardFactory $resultForwardFactory,
            JsonFactory $resultJsonFactory,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     
        )
    {
        // $this->session = $customerSession;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->product = $product;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        // $this->_storeManager = $storeManager;

    }
    public function execute()
    {
       
                    $post = $this->getRequest()->getParams('id');  
        $resultJson = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $response = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load('11129'); 
        $_imagehelper = $objectManager->create('Magento\Catalog\Helper\Image');       
        $images = $product->getMediaGalleryImages();
        $data = [];          
        foreach ($images as $child) {
               $productImage = $_imagehelper->init($product, 'product_page_image_large')->setImageFile($child->getFile())->constrainOnly(FALSE)->keepAspectRatio(TRUE)->keepFrame(TRUE)->getUrl();
                $data[] = $productImage;

                    
        } 
        $response = [
                            'errors' => false,
                            'customer_list'   => $data,
                            'message' => __("Success.")
                        ];
        // echo '<pre>'; print_r($response);
        return $resultJson->setData($response);
    }

}