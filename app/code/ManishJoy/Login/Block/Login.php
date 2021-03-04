<?php
namespace ManishJoy\Login\Block;

class Login extends \Magento\Framework\View\Element\Template {
	protected $_session;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Backend\Model\Session $session,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magestore\Bannerslider\Model\BannerFactory $bannerFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		array $data = []
	) {

		$this->_customerSession = $session;
		$this->_storeManager = $storeManager;
		$this->formKey = $formKey;
		$this->_bannerFactory = $bannerFactory;
		parent::__construct($context, $data);
	}

	public function isLoggedIn() {
		return $this->_customerSession->isLoggedIn();
	}

	public function getMediaUrl() {
		return $this->_storeManager->getStore()
			->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

	/**
	 * get form key
	 *
	 * @return string
	 */
	public function getFormKey() {
		return $this->formKey->getFormKey();
	}

	public function renderImage() {

		$banner = $this->_bannerFactory->create()->setStoreViewId(1)->load(33);
		$srcImage = $this->_storeManager->getStore()->getBaseUrl(
			\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
		) . $banner->getImage();

		return $srcImage;
	}

}