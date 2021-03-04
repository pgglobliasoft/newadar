<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Inquiries\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
//use Sttl\Inquiries\Model\Manager as ManagerFactory;




class EmailNotification
{


    const  XML_PATH_INQUIRIE_NOTIFICATION_TEMPLATE = 'inquiry/email/inquiryemail';
    const  XML_PATH_GROUP_NOTIFICATION_TEMPLATE = 'group/email/groupemail';


    /**#@-*/
    private $ManagerFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;



    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param CustomerViewHelper $customerViewHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        //ManagerFactory $ManagerFactory,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        //$this->ManagerFactory = $ManagerFactory;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Send email to customer when his password is reset
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function Inquirienotification($data)
    {

        $storeId = $this->storeManager->getStore()->getId();
        $data['mail_subject'] = 'Contact Us Website Inquiry from '.$data['name'];
        // $data['email'] = 'info@adarmedicaluniforms.com';
        // $data['name'] = 'Adar';
        //$data['email'] = 'haresh.patel@silvertouch.com';
        $data = $this->sendEmailTemplate(
            $data,
            self::XML_PATH_INQUIRIE_NOTIFICATION_TEMPLATE,
            'Support',
           [
             'store' => $this->storeManager->getStore($storeId),
             'name' => $data['name'],
             'email' => $data['email'],
             ],
            $storeId,
            $data
            );
    }

    /**
     * Send email Group
     *
     * @return void
     */
    public function Groupnotification($data)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $data['mail_subject'] = 'Group Website Inquiry from '.$data['name'];
        // $data['email'] = 'info@adarmedicaluniforms.com';
        // $data['name'] = 'Adar';
        $data = $this->sendEmailTemplate(
            $data,
            self::XML_PATH_GROUP_NOTIFICATION_TEMPLATE,
            'Support',
           [
             'store' => $this->storeManager->getStore($storeId),
             'name' => $data['name'],
             'email' => $data['email'],
             ],
            $storeId,
            $data
            );
    }

    private function sendEmailTemplate($data,$template,$sender,$templateParams = [],$storeId = null,
        $name = null
    ) {
        //$to_mail = 'aditya.pandya@silvertouch.com';
        $templateId = $this->scopeConfig->getValue($template, 'store', $storeId);
        $email = ['adarinquiries@gmail.com'];//, 'dashrath.gadhvi@silvertouch.com'
            $name = $data['name'];
        try{
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($data)
            ->setFrom(['name'=>$data['name'] ,'email'=>$data['email']])
            ->addTo('bodararahul@gmail.com','admin')
            ->getTransport();
            $return = $transport->sendMessage();
        } catch (\Exception $e) {
            echo $e;
        }
        return true;
    }
    
    public function Partnernotification($data)
    {
        
        $storeId = $this->storeManager->getStore()->getId();
        $data['mail_subject'] = 'Partner landing page inquiry '.$data['name'];
        //$data['email'] = 'info@adarmedicaluniforms.com';
        //$data['name'] = 'Adar';
        //$data['email'] = 'haresh.patel@silvertouch.com';
        $data = $this->sendEmailTemplate(
            $data,
            self::XML_PATH_INQUIRIE_NOTIFICATION_TEMPLATE,
            'Support',
           [
             'store' => $this->storeManager->getStore($storeId),
             'name' => $data['name'], 
             'email' => $data['email'],        
             ],
            $storeId,
            $data
            );
    }
}
