<?php
namespace Sttl\Inquiries\Controller\Groups;

use Sttl\Inquiries\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\DataObject;
use Sttl\Inquiries\Model\EmailNotification;
 
class Index extends \Magento\Framework\App\Action\Action
{
    private $context;
    private $contactsConfig;
	private $mail;
	private $EmailNotification;

    public function __construct(Context $context, MailInterface $mail ,EmailNotification $EmailNotification) {
        parent::__construct($context);
		$this->mail = $mail;
		$this->emailnotification = $EmailNotification;
        $this->context = $context;
    }
	
    public function execute()
    {
		$request = $this->getRequest()->getPostValue();
		$resultRedirect = $this->resultRedirectFactory->create();
		
		if (empty($request) || (!isset($request['g-recaptcha-response']) || empty($request['g-recaptcha-response']))) 
		{
			$this->messageManager->addError(__('Something went wrong, please try again.'));
			return $resultRedirect->setPath('groups.html');
        }
		
		try {
		
		$objGroups = $this->_objectManager->create("Sttl\Inquiries\Model\Groups");
		$objGroups->setName($request['name']);
		$objGroups->setEmail($request['email']);
		$objGroups->setComment($request['comment']);
		$objGroups->save();
		
		$this->emailnotification->Groupnotification($objGroups->getData());
		/*$this->messageManager->addSuccessMessage(
			__('Your contact request submitted sucessfully.')
		);*/
		
		} catch (\Exception $e) {
            //$this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
        }
		return $resultRedirect->setPath('groupthank');
    }
}