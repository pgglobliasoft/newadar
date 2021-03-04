<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Getfromstyle extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $session;

protected $dataObjectFactory;

protected $resultJsonFactory;

 protected $helper;

//protected $_customerRepositoryInterface;

public function __construct(
    context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
    \Magento\Framework\DataObjectFactory $dataObjectFactory,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Sttl\Adaruniforms\Helper\Sap $saphelper,
    \Magento\Framework\Json\Helper\Data $helper
    )
{
    $this->session = $customerSession;
    $this->helper = $helper;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
    $this->dataObjectFactory = $dataObjectFactory;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->saphelper = $saphelper;
}
        public function execute()
        {

            $resultJson = $this->resultJsonFactory->create();
            
            // $post = $this->getRequest()->getParams();

            $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());

            $po_number = $credentials['po_number'];
            $order_id = isset($credentials['order_id']) ? $credentials['order_id'] : "";
            $editOrderdItem = isset($credentials['editOrderdItem']) ? $credentials['editOrderdItem'] : "";
            $style = isset($credentials['style']) ? $credentials['style'] : "";
            $is_stylenumber = isset($credentials['is_stylenumber']) ? 1 : 0;

            if($is_stylenumber == 1){
                $renderDataPart = '';
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
                $productFactory = $objectManager->get('Magento\Catalog\Model\ProductFactory');
                $product = $productFactory->create();
                $product_collection_data = $product->loadByAttribute('sku', $style);

                $mg_product_data = array();

                $mg_product_data['name'] = $product_collection_data->getName();
                $mg_product_data['collection'] = $product_collection_data->getAttributeText('collecttion');
                $mg_product_data['sku'] = $product_collection_data->getSku();


                $parent_color_data = $this->saphelper->getStyleInventoryStatus($style);
                
                $customerdata = $this->saphelper->getCustomerDetails(["FlatDiscount","CardName","CardCode","Program","Tier","BulkDiscount"]);

                if((!empty($parent_color_data) && !empty($product_collection_data)) || !$editOrderdItem)
                {  
                    $all_parent_style_data = array(
                        "parent_style" => $style,
                        "parent_color_data" => $parent_color_data,
                        "product_collection_data" => $mg_product_data,
                        "customer_data" => $customerdata
                    );
                }


                if(empty($all_parent_style_data)){
                    $response = [
                        'errors' => true,
                        'message' => __("Item not found.")
                    ];
                }else{
                   $response = [
                        'errors' => false,
                        'parent_data'   => $all_parent_style_data,
                        'base64_order_id' => base64_encode($order_id),
                        'base64_ncp_id' => base64_encode($po_number),
                        'message' => __("Success.")
                    ]; 
                }
                return $resultJson->setData($response);    
            }
        }
}


