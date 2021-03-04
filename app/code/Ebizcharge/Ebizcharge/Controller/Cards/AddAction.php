<?php
/**
* Adds a payment method to the customer's saved payment methods.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NotFoundException;


class AddAction extends \Magento\Framework\App\Action\Action
{
    private $pageFactory;
	
	protected $customerSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
		$this->customerSession = $customerSession;
    }

    /**
     * Renders the page found in the XML layout file, sets
     * a page title, and sets the active link to Ebizcharge.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();

        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');

        if ($navigationBlock)
        {
            $navigationBlock->setActive('ebizcharge/cards/listaction');
        }

        $resultPage->getConfig()->getTitle()->set(__('Add New Credit Card'));

        return $resultPage;
    }
	
	/**
     * Checks to see if the customer is logged in.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate())
        {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }
}