<?php
namespace Sttl\Customerorder\Controller\GetCustomer;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Sttl\Adaruniforms\Helper\Sap;
use \Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;

class Index extends \Magento\Framework\App\Action\Action
{
     protected $resultForwardFactory;
    protected $resultPageFactory;
    protected $saphelper;
    protected $resultJsonFactory;

    public function __construct(
            PageFactory $resultPageFactory,
            Context $context,  
            ForwardFactory $resultForwardFactory,
            JsonFactory $resultJsonFactory,
            Sap $saphelper,
            LoggerInterface $logger,
            Session $customerSession       
        )
    {
        
        parent::__construct($context);     
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    	$this->sapHelper = $saphelper;       
        $this->logger = $logger;     
        $this->session = $customerSession;   
    }

    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        if (!$this->session->isLoggedIn())
        {
            $response = [
                            'errors' => true,
                            'html'   => '',
                            'message' => __("Customer Session is expried.")
                        ];
        }
        else
        {                                     
               
            $post = $this->getRequest()->getParams();
            $page = $post['page'];
            $order_data = @$post['data'] ? $post['data'] : [] ; //array_slice(json_decode($post['data']), $page*30 , 30);            
            $orderpacakge = array('data' => $order_data , 'total' => $post['total'] ,'page' => $page);
            $renderDataPart = $resultPage->getLayout()
                                ->createBlock('Sttl\Customerorder\Block\Index')
                                ->setTemplate('Sttl_Customerorder::customer.phtml')
                                ->setOrderDataCollection($orderpacakge)                                                      
                                ->toHtml();

           $response = [
                            'errors' => false,
                            'html'   => $renderDataPart,
                            'message' => __("Success.")
                        ];
        }
        return $resultJson->setData($response);

    }

}