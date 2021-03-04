<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ManishJoy\ChildCustomer\Controller\Adminhtml\Resourceaccess;

use ManishJoy\ChildCustomer\Model\PostFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends \Magento\Backend\App\Action
{   
    protected $_postFactory;

    protected $resultPageFactory;

    private $jsonResultFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PostFactory $postFactory,
        PageFactory $resultPageFactory,
        JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->_postFactory = $postFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonResultFactory = $jsonResultFactory;

    }

    public function execute()
    {       
        $post = $this->getRequest()->getParams();

        if ($post) {
            $model = $this->_postFactory->create();
            date_default_timezone_set('America/New_York');
            if($post['all'] == 1){
                $perm = '{"accountinfo":{"1":"view_customer","2":"payment_info","3":"shipping_info","4":"user_manage"},"order":{"5":"place_oder","6":"view_order"},"invoice":{"7":"pay_invoice","8":"view_invoice"},"downlaod_library":{"9":"view_catalog","10":"view_inventory","11":"view_product"}}';
            }else{
               $perm = json_encode(isset($post['check_list'])? $post['check_list'] : []);
            }
            if(!empty($post['admin_customer_entity_id'])){
                $model->load($post['admin_customer_entity_id']);

                $model->addData([
                    "permission" => $perm,
                    "updated_at" => date('Y-m-d H:i:s'),
                ]); 
                  $saveData = $model->save();

                    if ($saveData) {
                        // $this->messageManager->addSuccess(__('Successfully Edit data.'));
                        $response = [
                                'errors' => false,
                                'id' => $model->getId(),
                                'message' => __("Successfully Edit permission."),
                            ];            
                    }else {
                        // $this->messageManager->addError(__('Invaild data.'));
                            $response = [
                                'errors' => true,
                                'html' => '',
                                'message' => __("Somthing Went to wrong."),
                            ];
                    }
            }else{
                $model->addData([
                    "parent_id" => 0,
                    "c_id" => $post['admin_customer_id'],
                    "fullname" => "",
                    "permission" => $perm,
                    "customercode" => "",
                    "webscesscode" => "",
                    "status" => "",
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                ]); 
                $saveData = $model->save();
                    if ($saveData) {
                        // $this->messageManager->addSuccess(__('Successfully save.'));  
                        $response = [
                                'errors' => false,
                                'id' => $model->getId(),
                                'message' => __("Successfully Edit permission."),
                            ];          
                    }else {
                        // $this->messageManager->addError(__('Invaild data.'));
                        $response = [
                                'errors' => true,
                                'html' => '',
                                'message' => __("Somthing Went to wrong."),
                            ];
                    }
                }
            }else {
                $response = [
                                'errors' => true,
                                'html' => '',
                                'message' => __("Somthing Went to wrong."),
                            ];
            // $this->messageManager->addError(__('Invaild data.'));
        }
        $result = $this->jsonResultFactory->create();
        return $result->setData($response);
    }
}
