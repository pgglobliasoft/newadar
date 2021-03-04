<?php
namespace ManishJoy\CustomerLogin\Model\Cookie;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class Custom {
	/**
	 * @var \Magento\Framework\Stdlib\CookieManagerInterface
	 */
	protected $_cookieManager;

	/**
	 * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
	 */
	protected $_cookieMetadataFactory;

	/**
	 * @var \Magento\Framework\Session\SessionManagerInterface
	 */
	protected $_sessionManager;

	/**
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	protected $_objectManager;

	/**
	 * @var Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
	 */
	protected $_remoteAddressInstance;

	/**
	 * [__construct ]
	 *
	 * @param CookieManagerInterface                    $cookieManager
	 * @param CookieMetadataFactory                     $cookieMetadataFactory
	 * @param SessionManagerInterface                   $sessionManager
	 * @param \Magento\Framework\ObjectManagerInterface $objectManager
	 */
	public function __construct(
		CookieManagerInterface $cookieManager,
		CookieMetadataFactory $cookieMetadataFactory,
		SessionManagerInterface $sessionManager,
		\Magento\Framework\ObjectManagerInterface $objectManager
	) {
		$this->_cookieManager = $cookieManager;
		$this->_cookieMetadataFactory = $cookieMetadataFactory;
		$this->_sessionManager = $sessionManager;
		$this->_objectManager = $objectManager;
		$this->_remoteAddressInstance = $this->_objectManager->get(
			'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress'
		);
	}

	/**
	 * Get data from cookie set in remote address
	 *
	 * @return value
	 */
	public function get($name) {
		return $this->_cookieManager->getCookie($this->getRemoteAddress());
	}

	/**
	 * Set data to cookie in remote address
	 *
	 * @param [string] $value    [value of cookie]
	 * @param integer  $duration [duration for cookie]
	 *
	 * @return void
	 */
	public function set($name, $value, $duration = 86400) {
		$metadata = $this->_cookieMetadataFactory
			->createPublicCookieMetadata()
			->setDuration($duration)
			->setPath($this->_sessionManager->getCookiePath())
			->setDomain($this->_sessionManager->getCookieDomain());

		$this->_cookieManager->setPublicCookie(
			$name,
			$value,
			$metadata
		);
	}

	/**
	 * delete cookie remote address
	 *
	 * @return void
	 */
	public function delete($name) {
		$this->_cookieManager->deleteCookie(
			$name,
			$this->_cookieMetadataFactory
				->createCookieMetadata()
				->setPath($this->_sessionManager->getCookiePath())
				->setDomain($this->_sessionManager->getCookieDomain())
		);
	}

	/**
	 * used to get remote address, in which cookie data is set
	 *
	 * @return [string] [returns remote address]
	 */
	public function getRemoteAddress() {
		return $this->_remoteAddressInstance->getRemoteAddress();
	}
}