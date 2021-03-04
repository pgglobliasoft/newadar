<?php
namespace Sttl\Customerorder\Controller\Customer;
use Magento\Framework\Controller\ResultFactory; 
class Myaccountcontroller extends \Magento\Framework\App\Action\Action
{
    protected $_cacheTypeList;
    protected $_cacheState;
    protected $_cacheFrontendPool;
    protected $resultPageFactory;
    protected $_messageManager; 
    private $_transportBuilder;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context,     
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder    
    ) {     
        $this->_messageManager = $messageManager;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context);            
    }
 
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);         
        $data = $this->getRequest()->getPost(); 
        $deta = $_POST['feedback'];  
        $attachmentData = array();
        if($_FILES){
            $attachmentData = $_FILES['fileupload'];
        }       
        $emailTempVariables = array();      
 
          $sender = [
                            'name' => 'rahul',
                            'email' => 'bhavinsen1992@gmail.com'
                        ];
 
        // $emailTemplateVariables['formdetail'] = $emailTempVariables;
        // $emailTemplateVariables['subject'] = 'inquery';
          $emailTemplateVariables = ['subject' => 'Customer Imquery',
                             'name'  => $sender['name'],
                             'email' => $sender['email'],
                             'comment' => $data 
                            ];
         
        $receiverInfo = array(
            'name' => 'bhavin',
            'email' => 'bhavinsen1992@gmail.com'
        );    
 
        $senderInfo = array(
            'name' => 'Owner',
            'email' => 'bhavinsen1992@gmail.com'
        );
        $this->_objectManager->get('Sttl\Customerorder\Helper\Emaildata')->yourCustomMailSendMethod($emailTemplateVariables,$senderInfo,$receiverInfo,$attachmentData);       
     
        $this->_messageManager->addSuccess(__("Success Message"));
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}


