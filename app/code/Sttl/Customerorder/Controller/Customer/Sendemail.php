<?php
namespace Sttl\Customerorder\Controller\Customer; 

use Magento\Framework\App\RequestInterface;
 
class Sendemail extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context
        , \Magento\Framework\App\Request\Http $request
        , \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
        , \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_request = $request;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
 
    public function execute()
    {

        $receiverMail = "bhavinsen1992@gmail.com"; 
        $templateId = 10;        // id of email template
        $storeId = 1;           // desired store id
        $templateParams = [];   // params of template by array
        try{
        mail("bodararahul@gmail.com","My subject",'test em');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $transportBuilder = $objectManager->create("Magento\Framework\Mail\Template\TransportBuilder");
        $transport = $transportBuilder->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                    ->setTemplateVars($templateParams)
                    ->setFrom(array('email' => 'bodararahul@gmail.com', 'name' => 'SenderName'))
                    ->addTo($receiverMail, "RecaiverName")
                    ->setReplyTo('replyto@email.com')
                    ->getTransport();
        // echo "<pre>";
        // print_r($transport->getMessage());

        $test = $transport->sendMessage();
        var_dump($test);
        }catch(Exception $e) {
            echo $e->getMessage();
            die('here');
        }
    }
}