<?php

namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\Filesystem;
use Zend_Mime;
class Myaccountemail extends \Magento\Framework\App\Action\Action
{

	protected $helperData;
	protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_logLoggerInterface;
    protected $_fileUploaderFactory;
    protected $customerSession;
    protected $resultJsonFactory;
    protected $sapHelper;
    /**
     * @var Filesystem
     */
    private $fileSystem;
    private $reader;


	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Request\Http $request,
       \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Filesystem $fileSystem,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\Filesystem\Driver\File $reader,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Sttl\Customerorder\Helper\Emaildata $helperData,
		\Sttl\Adaruniforms\Helper\Sap $sapHelper

	)
	{
		$this->_request = $request;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->fileSystem = $fileSystem;
		$this->helperData = $helperData;
		$this->reader = $reader;
		    $this->resultJsonFactory = $resultJsonFactory;
		$this->customerSession = $customerSession;
		$this->_fileUploaderFactory = $fileUploaderFactory;
		$this->sapHelper =$sapHelper;
		return parent::__construct($context);
	}

	public function execute()
	{

		// getCustomerQueryFeedback


		$resultJson = $this->resultJsonFactory->create();
        $response = '';			
		$deta = $_POST['feedback'];
	 	$fileexist = @$_FILES['fileupload']['name'];
		$mediaPath = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

		$attachmentData = array();
        if($_FILES){

	      	$target=$mediaPath.'Emailattachment/'.basename($_FILES['fileupload']['name']);
	      	move_uploaded_file($_FILES['fileupload']['tmp_name'],$target);
      		
        }
      					

		$emailids = $this->helperData->getGeneralConfig('multiple_email');
		
		$recipeantemail = explode(",",$emailids);
		
		$this->_inlineTranslation->suspend();
		
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		
		$mediaPath = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
		
		$senderemail = $this->customerSession->getCustomer()->getEmail();
		
		$sendername = $this->customerSession->getCustomer()->getName();
		
		$senderid = $this->customerSession->getCustomer()->getId();
		
		$catdNumber = $this->customerSession->getCustomer()->getCustomerNumber();
        
		$curentdate = date('Y-m-d H:i:s');

		$setcustomerquery = $this->sapHelper->setCustomerQueryFeedback($sendername, $senderemail, $deta, $catdNumber, $curentdate);

		$customerquery = $this->sapHelper->getCustomerQueryFeedback($sendername);

		$sapcustomername =  $this->sapHelper->getCustomerDetails(['CardName']);

		$request_id = $customerquery[0]['request_id'];
		
		$subject = $this->helperData->getGeneralConfig('multiple_email_subject');
        // $sentToEmail = $email;
        
        $templateParams = ['subject' => $subject,
          					'name'  => $sendername,
          					'cardcord-name' => $sapcustomername[0]['CardName'].' - '.$catdNumber,
          					'email' => $senderemail,
          					'id' => $request_id,
          					'comment' => $deta	
      					  ];
        $storeId = 1;
			
		if($fileexist != '')
		{
                 			 
            try{
				$ext = pathinfo($_FILES['fileupload']['name'], PATHINFO_EXTENSION);
				if($ext != 'jpeg' && $ext != 'jpg' && $ext != 'png' && $ext != 'gif' && $ext != 'pdf' && $ext != 'csv')
				{
					$response = [
			                        'errors' => true,
		                        	'message' => "Please select valid attachment.."
					            ];
					     return $resultJson->setData($response);
				}

				$pdf_file = $mediaPath.'Emailattachment/'.$_FILES['fileupload']['name'];
				             
				$transport = $this->_transportBuilder
								->setTemplateIdentifier(11)
								->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
								->setTemplateVars($templateParams)
								->setFrom(array('email' => $senderemail, 'name' => $sendername))
				                ->addTo($recipeantemail)
				                //->addTo('owner@example.com','owner')

				                ->addAttachment(file_get_contents($pdf_file),$ext,Zend_Mime::DISPOSITION_ATTACHMENT,Zend_Mime::ENCODING_BASE64,$_FILES['fileupload']['name'])
				                ->getTransport();
				                 
				                $transport->sendMessage();
				                 
				                $this->_inlineTranslation->resume();
				                // $this->messageManager->addSuccess('Email sent successfully');

				              
				                 $response = [
				                        'errors' => false,
				                        'message' => __("success")
				                    ];
				                    return $resultJson->setData($response);
				}
				catch(Exception $e) 
				{
					$response = [
			                        'errors' => true,
		                        	'message' => $e->getMessage()
					            ];
					     return $resultJson->setData($response);
				}
        }
        else
        {
             
            try
            {
              	$transport = $this->_transportBuilder
								->setTemplateIdentifier(11)
								->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
								->setTemplateVars($templateParams)
								->setFrom(array('email' => $senderemail, 'name' => $sendername))
				                ->addTo($recipeantemail)
				                //->addTo('owner@example.com','owner')
				                ->getTransport();
				                 
				                $transport->sendMessage();
				                 
				                $this->_inlineTranslation->resume();
				                // $this->messageManager->addSuccess('Email sent successfully');
				                $response = [
				                        'errors' => false,
				                        'message' => __("success")
				                    ];
				                    return $resultJson->setData($response);
			}
			catch(Exception $e) 
			{
			            $response = [
				                        'errors' => true,
				                        'message' => $e->getMessage()
				                    ];
				                    return $resultJson->setData($response);

			}
        }

	}
	

}