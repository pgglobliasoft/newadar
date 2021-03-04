<?php
namespace Sttl\Proupdated\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Sttl\Proupdated\Model\PostFactory;
use Sttl\Proupdated\Model\ReadFactory;
use Sttl\Proupdated\Model\ResourceModel\Post\Collection;

class Read extends \Magento\Framework\View\Element\Template {

	protected $_session;

	protected $customerSession;

	public $StoreManagerInterface;

	public function __construct(
		Context $context,
		Session $Session,
		ReadFactory $ReadFactory,
		PostFactory $PostFactory,
		Collection $Collection,
		\Magento\Store\Model\StoreManagerInterface $StoreManagerInterface,
		array $data = []
	) {
		parent::__construct($context);
		$this->_session = $Session;
		$this->_ReadFactory = $ReadFactory;
		$this->PostFactory = $PostFactory;
		$this->readCollection = $Collection;
		$this->StoreManagerInterface = $StoreManagerInterface;
	}

	public function _idcollection() {
		$data = [];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$collection = $objectManager->create('Sttl\Proupdated\Model\ResourceModel\Post\Collection')->addFieldToFilter('status', ['eq' => 1]);
		foreach ($collection as $key => $value) {
			$data[$value->getId()] = 0;
		}
		return $data;
	}

	public function InsertData() {
		return $this->_session->getCustomer()->getId();
	}

	public function getCustomerId() {
		return $this->_session->getCustomer()->getId();
	}

	public function getImportcollection() {

		$customerId = $this->_session->getCustomer()->getId();
		$data = $this->_ReadFactory->create()->load($customerId, 'customer_id');
		$idjson = $this->_idcollection();
		if (!$data->getId() && $data->getId() !== 1) {
			$model = $this->_ReadFactory->create();
			$model->addData([
				"customer_id" => $customerId,
				"read_json" => json_encode($idjson),
				"updated_at" => date("Y-m-d H:i:s"),
			]);
			$model->save();
		} else {
			$array = json_decode($data->getReadJson(), true);
			foreach ($idjson as $key => $value) {
				if (!array_key_exists($key, $array)) {
					$array[$key] = 0;
				}
			}
			$model = $this->_ReadFactory->create()->load($data->getId());
			$model->addData([
				"read_json" => json_encode($array),
				"updated_at" => date("Y-m-d H:i:s"),
			]);
			$model->save();
		}
		$data = $this->_ReadFactory->create()->load($customerId, 'customer_id');
		return $data->getData();
	}

}