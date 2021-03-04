<?php
namespace ManishJoy\ChildCustomer\Helper;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use ManishJoy\ChildCustomer\Model\PostFactory;

class Customer extends AbstractHelper {

	protected $_storeManager;

	protected $_session;

	public function __construct(
		Context $context,
		StoreManagerInterface $storeManager,
		PostFactory $postFactory,
		CustomerFactory $customerFactory,
		Session $customerSession
	) {

		$this->customerFactory = $customerFactory;
		$this->session = $customerSession;
		$this->_postFactory = $postFactory;
		$this->_storeManager = $storeManager;
		parent::__construct($context);

	}

	/**
	 * @ Return array
	 */
	public function getChildCustomerdata($cid) {

		$post = $this->_postFactory->create()
			->getCollection()->addFieldToFilter('c_id', $cid);
		return $post->getFirstItem();

	}

	/**
	 * update child login time
	 */
	public function setChildCustomerActive($id, $active) {
		// echo "string";
		$model = $this->_postFactory->create()->load($id);
		if (!empty($model) && $model->getId() > 1) {
			date_default_timezone_set('America/New_York');
			$model->setData([
				"entity_id" => $id,
				"active" => $active,
				"updated_at" => date('Y-m-d H:i:s'),
			]);
			$saveData = $model->save();
			if ($saveData) {
				return true;
			} else {
				return false;
			}
			echo "string";die;
		}
		return false;

	}

}
