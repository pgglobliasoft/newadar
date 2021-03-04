<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Customerorder\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class EmailNotification
{

    const  XML_PATH_ORDER_SUBMIT_NOTIFICATION_TEMPLATE = 'customerorder/email/ordersubmit';
    const  XML_PATH_PAYMENT_CONFIRMATION_NOTIFICATION_TEMPLATE = 'customerinvoices/email/paymentconfirmation';
	
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

    private function sendEmailTemplate($data,$template,$sender,$templateParams = [],$storeId = null,
        $name = null,$templateId = '') {
        //$templateId = $this->scopeConfig->getValue($template, 'store', $storeId);
        $name = $data['name'];
        $email = $data['customer_email'];
        try{
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($data)
            ->setFrom(['name'=>'Adar','email'=>'info@adarmedicaluniforms.com'])
            ->addTo($email)
            ->getTransport();
            $return = $transport->sendMessage();  
        } catch (\Exception $e) {
            echo $e;
        }
        return true;
    }
    
    public function OrderSubmitnotification($data)
    {         
                
                // echo '<pre>'; print_r($data); die;
            $storeId = $this->storeManager->getStore()->getId();
            if(!$data['admin_customer_status']){             
                $data['mail_subject'] = 'New Adar Website Order PO. '.$data['numat_card_po'];                
                $data['customer_email'] = $data['customer_email'];
               
            }else{                        
                $data['mail_subject'] = 'Your order for BP. ['.$data['admin_customeremail']['bp_name'].'] was successfully submitted';                
                $data['customer_email'] = $data['admin_customeremail']['email'];
            }
            $data['name'] = 'Adar';
            $data['vew_order_status'] = $data['vew_order_status'];

            $data = $this->sendEmailTemplate(
                    $data,
                    self::XML_PATH_ORDER_SUBMIT_NOTIFICATION_TEMPLATE,
                    'Support',
                    [
                        'store' => $this->storeManager->getStore($storeId),
                        'numat_card_po' => $data['numat_card_po']      
                    ],
                    $storeId,
                    $data,
                    8
                );
        
    }
	
	public function PaymentConfirmation($data)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $data['mail_subject'] = 'Payment Confirmation';
        $data['customer_email'] = $data['customer_email'];
        $data['name'] = 'Adar';
        $data = $this->sendEmailTemplate(
            $data,
            self::XML_PATH_PAYMENT_CONFIRMATION_NOTIFICATION_TEMPLATE,
            'Support',
           [
             'store' => $this->storeManager->getStore($storeId),
             'payment_amount' => $data['payment_amount'],      
             'payment_date' => $data['payment_date']      
             ],
            $storeId,
            $data,
            9
            );
    }
}
