<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Sttl\Adaruniforms\Helper\Sap;
use Magento\Customer\Model\Session;
class GetCutomer extends \Magento\Framework\App\Action\Action
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
            Session $customerSession       
        )
    {
        
        parent::__construct($context);     
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    	$this->sapHelper = $saphelper;       
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

            $renderDataPart = $resultPage->getLayout()
                                ->createBlock('Sttl\Customerorder\Block\Index')
                                ->setTemplate('Sttl_Customerorder::customer.phtml')
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