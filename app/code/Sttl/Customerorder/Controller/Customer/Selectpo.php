<?php
namespace Sttl\Customerorder\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Selectpo extends \Magento\Framework\App\Action\Action
{
protected $resultPageFactory;

protected $sapHelper;

protected $session;

protected $storemanager;

protected $resultJsonFactory;

public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    PageFactory $resultPageFactory,
	\Sttl\Adaruniforms\Helper\Sap $sapHelper,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
	\Magento\Store\Model\StoreManagerInterface $storemanager
    )
{
    $this->session = $customerSession;
    parent::__construct($context);
    $this->resultPageFactory = $resultPageFactory;
	$this->sapHelper = $sapHelper;
	$this->_storemanager = $storemanager;
	$this->resultJsonFactory = $resultJsonFactory;
}
public function execute()
{

	$resultJson = $this->resultJsonFactory->create();

	$resultRedirect = $this->resultRedirectFactory->create();
    if (!$this->session->isLoggedIn())
    {
      $response = [
          'session_distroy' => true,
          'message' => __("Customer session expired please login.")
      ];
      return $resultJson->setData($response);
    }
    else
    {
    	$post = $this->getRequest()->getParams();
		if(isset($post['order_id'])){
            $order_id = $post['order_id'];
            $po_number = $post['po_number'];
            $itemdata = array();
			$response = [
                    'errors' => true,
                    'message' => __("There has some issue...")
                ];
                $distinstyle = $this->sapHelper->gettempOrdrstyle($order_id);
                $filnalHtml= "";

                  if($order_id > 0)
                  {
                    $style = '';
                    $submitcolor = '';

                        $values = array_map('array_pop', $distinstyle);
                        $implodedStyle = implode("','", $values);
                        $distinstyle = $this->sapHelper->getsizegroup($implodedStyle);
                        $sizegrouparray = array();
                        foreach($distinstyle as $key => $data)
                        {
                            $sizegrouparray[$data['SizeGroup']][] = $data['Style'];
                        }
                        $ItemStyles[] = array();
                                      // print_r($sizegrouparray);
                                    foreach ($distinstyle as $key => $data) {
                                        // if(array_key_exists($value, $styleInventory))
                                        // {
                                                $ItemStyles[0][] = $data['Style'];
                                        // }
                                    }
                        $filnalHtml ='';
                        foreach($ItemStyles as $key => $value)
                        {
                            $renderDataByColor = '';
                            $groupstyle =implode("','", $value);
                            $renderDataByColor = $this->sapHelper->newrenderMOrderItemHtml($order_id,'','','',$groupstyle);
                            $filnalHtml .= $renderDataByColor;
                        }
                        $filnalHtml .= $this->sapHelper->renderMOrderItemHtmltotal($order_id,'');
                    }

                $resultPage = $this->resultPageFactory->create();
                $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
				$response = [
                    'errors' => false,
                    'limetable' => $filnalHtml,
                    'message' => __("Qty Successfully edited"),
                    'base64_order_id' => base64_encode($order_id),
                    'base64_ncp_id' => base64_encode($po_number)
                ];
			}
			return $resultJson->setData($response);
		}
    }
}
