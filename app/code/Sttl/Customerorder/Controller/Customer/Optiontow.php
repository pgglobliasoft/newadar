<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Optiontow extends \Magento\Framework\App\Action\Action
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
        $customerDdta['CardCode'] = $this->session->getCustomer()->getData('customer_number');
        $post = $this->getRequest()->getParams();
        $po_number = $post['po_number'];
        $order_id = isset($post['order_id']) ? $post['order_id'] : "";
        $message = '';
        $enty_id = '';
        try {
			
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
                $message = $e->getMessage();
                $response = [
                    'errors' => true,
                    'message' => __($message)
                ];
            } catch (\Exception $e) {
                $response = [
                    'errors' => true,
                    'message' => __("Something went wrong2.")
                ];
            }
            return $resultJson->setData($response);
    }
}

}