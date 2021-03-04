<?php
namespace Sttl\Inquiries\Controller\Inquiries;

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
		
		if (empty($request)) 
		{
			$this->messageManager->addError(__('Something went wrong, please try again.'));
			return $resultRedirect->setPath('contact');
        }
		
		try {
		$objInquiries = $this->_objectManager->create("Sttl\Inquiries\Model\Inquiries");
		
		$objInquiries->setSubject($request['subject']);
		$objInquiries->setHearFrom($request['hear_from']);
		$objInquiries->setName($request['name']);
		$objInquiries->setEmail($request['email']);
		$objInquiries->setPhoneNo($request['phone_no']);
		$objInquiries->setCompany($request['company']);
		$objInquiries->setAddress($request['address']);
		$objInquiries->setSubAddress($request['sub_address']);
		$objInquiries->setCity($request['city']);
		$objInquiries->setState($request['state']);
		$objInquiries->setZipcode($request['zipcode']);
		$objInquiries->setComment($request['comment']);
		
		$objInquiries->save();
		
		// $this->sendEmail($objInquiries->getData());
		$this->emailnotification->Inquirienotification($objInquiries->getData());
		$this->messageManager->addSuccessMessage(
			__('Your contact request submitted sucessfully.')
		);
		
		} catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
        }
		return $resultRedirect->setPath('contactthank');
    }
	
	private function sendEmail($post)
    {
		// print_r($post);die;
		try {
			
        $this->mail->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
		} catch (Exception $e) {
			echo $e;die;	
		}
    }
}