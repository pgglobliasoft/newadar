<?php
namespace Sttl\Inquiries\Helper;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

class Emailsend extends AbstractHelper
{
    const EMAIL_TEMPLATE = 'email_section/sendmail/email_template';

    const EMAIL_SERVICE_ENABLE = 'email_section/sendmail/enabled';

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger
    )
    {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Send Mail
     *
     * @return $this
     *
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail()
    {
       echo $email = 'bhavinsen1992@gmail.com'; //set receiver mail 
       // die;

        $this->inlineTranslation->suspend();
        $storeId = $this->getStoreId();
        $vars = [
            'message_1' => 'CUSTOM MESSAGE STR 1',
            'message_2' => 'custom message str 2',
            'store' => $this->getStore()
        ];

        // set from email
        $sender = 'bodararahul@gmail.com';
        $transport = $this->transportBuilder->setTemplateIdentifier(
            'email_section_sendmail_email_template'
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $this->getStoreId()
            ]
        )->setTemplateVars(
            $vars
        )->setFrom(
            $sender
        )->addTo(
            $email
        )->addBcc(
           ['ng.globaliasoft@gmail.com', 'receiver2@example.com']
        )->getTransport();

        try {
            $transport->sendMessage();
            echo 'done';
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
            
        }
        echo 'sdsd'; die;
        $this->inlineTranslation->resume();

        return $this;
    }

    /*
     * get Current store id
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /*
     * get Current store Info
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }
}