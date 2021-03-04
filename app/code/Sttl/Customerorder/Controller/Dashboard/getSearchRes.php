<?php
namespace Sttl\Customerorder\Controller\Dashboard;

use Magento\Framework\App\Action\Context;

class getSearchRes extends \Magento\Framework\App\Action\Action
{
protected $sapHelper;

protected $session;

protected $resultJsonFactory;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
	\Sttl\Adaruniforms\Helper\Sap $sapHelper,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Framework\App\State $state,
    \Sttl\Adaruniforms\Helper\Data $configData
    )
{
    parent::__construct($context);
	$this->sapHelper = $sapHelper;
	$this->resultJsonFactory = $resultJsonFactory;
    $this->session = $customerSession;
     $this->state = $state;
     $this->configData = $configData;
}
    public function execute()
    {
        $response = '';
        $post = $this->getRequest()->getParams();
        if(!empty($post))
        {
            $response = [
                'errors' => true,
                'order_data' => [],
                'message' => __("Searched String returend 0 rows.")
            ];
            $resultJson = $this->resultJsonFactory->create();
            if((isset($post['is_search']) && $post['search'] != '')){
                $CardCode = $this->getCustomerCustomerNumber();
                 $orders = $this->sapHelper->getSearchBy($CardCode, $post['search']);
                 $response = [
                    'errors' => false,
                    'order_data' => $orders,
                    'message' => __("Searched String returend ".count($orders)." rows.")
                ];
                return $resultJson->setData($response);
            }
        }
    }
    public function getCustomerCustomerNumber() {
        return $this->session->getCustomer()->getData('customer_number');
    }

}