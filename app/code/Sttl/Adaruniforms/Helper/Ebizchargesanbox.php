<?php
namespace Sttl\Adaruniforms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Soap\Client;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;

ini_set('memory_limit', '1000M');
ini_set('max_execution_time', -1);

class Ebizcharge extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    protected $scopeConfig;

	protected $_storeManager;

	protected $eBizConnect;

	protected $_session;

	protected $_customerRepositoryInterface;

    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Customer\Model\Session $session
    )
    {
		$this->_storeManager = $storeManager;
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->_session = $session;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
        echo 'sanboc'; die;
		$WebserviceUrl = 'https://sandbox.ebizcharge.com/soap/gate/0AE595C1/ebizcharge.wsdl';
		//$WebserviceUrl = 'https://secure.ebizcharge.com/soap/gate/0AE595C1/ebizcharge.wsdl';
		$soap_params = array(
								  'soap_version' => SOAP_1_1,
								  'encoding' => 'UTF-8',
								  'stream_context' => stream_context_create(array(
										  'ssl' => array(
											   'verify_peer' => false,
												'verify_peer_name' => false,
												'allow_self_signed' => true
										  )
									)),
								  //'trace'      => true,
								  //'ex                                                                                                                                                                                                   ceptions' => true, // disable exceptions
								  //'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
								  'cache_wsdl' => false // disable any caching on the wsdl, encase you alter the wsdl server
								);
		$this->eBizConnect = new \Zend\Soap\Client($WebserviceUrl,$soap_params);
    }

	public function Connect()
	{
		return $this->eBizConnect;
	}

	public function getToken()
	{
		// Creating a ueSecurityToken
		$sourcekey = '_uKAs82JnckjK9p9d8KHZaHHA6a7aHeL';// SandBox
		//$sourcekey = 'VR8pH67rLgRDMjhYUVS7sSv5frCeStwt';// Live
		//Input your merchant console generated source key
		$pin = 'wSOwCvU'; // SandBox
		//$pin = 'kCOhMZx'; // Live
		// generate random seed value
		$seed=time() . rand();
		// make hash value using sha1 function
		$clear= $sourcekey . $seed . $pin;
		$hash=sha1($clear);
		// assembly ueSecurityToken as an array
		// (php5 will correct the type for us)
		$token=array(
					'SourceKey'=>$sourcekey,
					'PinHash'=>array(
							'Type'=>'sha1',
							'Seed'=>$seed,
							'HashValue'=>$hash
							),
					'ClientIP'=>$_SERVER['REMOTE_ADDR']
					);
		return $token;
   }

   public function getCustomerById($id = 0)
   {
	   try
		{
			return $this->eBizConnect->getCustomer(self::getToken(), $id);
		}
		catch(SoapFault $e)
		{
			return $e->getMessage();
		}
   }

   public function saveCardByCustomerId($id = 0, $data = array(), $default = false, $verify = false)
   {
		try
		{
			return $this->eBizConnect->addCustomerPaymentMethod(self::getToken(), $id, $data,$default, $verify);
		}
		catch(SoapFault $e)
		{
			return $e->getMessage();
		}
   }
   
   public function runTransaction($Request = array())
   {
		try
		{
			return $this->eBizConnect->runTransaction(self::getToken(), $Request);
		}
		catch(SoapFault $e)
		{
			//echo $client->__getLastRequest();
			//echo $client->__getLastResponse();
			return $e->getMessage();
		}
   }
   
   public function runCustomerTransaction($custNum,$PayMethod,$Request = array())
   {
   		try
		{
			return $this->eBizConnect->runCustomerTransaction(self::getToken(),$custNum,$PayMethod, $Request);
		}
		catch(SoapFault $e)
		{
			//echo $client->__getLastRequest();
			//echo $client->__getLastResponse();
			return $e->getMessage();
		}
   }

   public function searchCustomerByParams($search_query = array(),$match_all = true,$start = 0,$limit = 10 ,$sort = 'fname')
   {
		/*
		$search=array(
					array(
					'Field'=>'Created',
					'Type'=>'gt',
					'Value'=>'2010-08-08')
				);
		$start=0;
		$limit=10;
		$matchall=true;
		$Sort = "fname";
		*/
		try
		{
			return $this->eBizConnect->searchCustomers(self::getToken(),$search_query,$match_all,$start,$limit,$sort);
		}
		catch(SoapFault $e)
		{
			//echo "\n\nResponse: " . $client->__getLastResponse();
			//echo "\n\nRequest: " . $client->__getLastRequest();
			return $e->getMessage();
		}
   }
   

   public function addCustomer($CustomerData = array())
   {
   		try
		{
			return $this->eBizConnect->addCustomer(self::getToken(),$CustomerData);
		}
		catch(SoapFault $e)
		{
			echo $client->__getLastRequest();
			echo $client->__getLastResponse();
			return $e->getMessage();
		}
   }

   public function addCustomerPaymentMethod($custNum = 0, $save_card = array(), $Default = false, $Verify = false)
   {
   		try
		{
			return $this->eBizConnect->addCustomerPaymentMethod(self::getToken(),$custNum, $save_card, $Default, $Verify);
		}
		catch(SoapFault $e)
		{
			echo $client->__getLastRequest();
			echo $client->__getLastResponse();
			return $e->getMessage();
		}
   }

}
