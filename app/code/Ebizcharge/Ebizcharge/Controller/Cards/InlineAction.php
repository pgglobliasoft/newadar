<?php
/**
* Deletes the customer's saved payment method during the checkout process.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;

class InlineAction extends \Magento\Framework\App\Action\Action
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    private $errorsMap = [];
    private $jsonFactory;
    private $fkValidator;
    private $token;
    protected $_tran;
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $jsonFactory
     * @param Validator $fkValidator
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param PaymentTokenManagement $paymentTokenManagement
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $jsonFactory,
        Validator $fkValidator,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Ebizcharge\Ebizcharge\Model\Token $token,
		\Ebizcharge\Ebizcharge\Model\TranApi $tranApi)
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->fkValidator = $fkValidator;
        $this->token = $token;
        $this->_tran = $tranApi;
        $this->_scopeConfig = $scopeConfig;

        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')];
    }
	
	/**
     * Deletes customer's payment method.
     */
    public function execute()
    {
        $cid = $this->getRequest()->getParam('cid');
		$mid = $this->getRequest()->getParam('mid');
	
		if ($cid === null || $mid === null)
        {
            return false;
        }

        try {
			// initiate transaction start
			$isSandBox = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
            if ($isSandBox)
            {
				$this->_tran->usesandbox = true;
			}
			
			$this->_tran->key = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcekey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$this->_tran->userid = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourceid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$this->_tran->pin = $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcepin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

			$this->_tran->software = 'Ebizcharge_Ebizcharge 1.0.0';
			// initiate transaction end
			
			$wsdl = $this->_tran->_getWsdlUrl();
			$ueSecurityToken = $this->_tran->_getUeSecurityToken();
			$client = new \Zend\Soap\Client($wsdl, $this->_tran->SoapParams());
			
			//$client->deleteCustomerPaymentMethod($ueSecurityToken, $cid, $mid);
			$params = array(
                    'securityToken' => $ueSecurityToken,
                    'customerToken' => $cid,
                    'paymentMethodId' => $mid,
                );
			  
            $client->deleteCustomerPaymentMethodProfile($params);
			/*if ($client->deleteCustomerPaymentMethodProfile($params)) {
				throw new \Magento\Framework\Exception\LocalizedException(__('Payment Method deleted successfully'));
            }*/
        } catch (\Exception $e) {
			return false;
        }

        //return true;
		return $this->_redirect('ebizcharge/cards/listaction');
    }
}