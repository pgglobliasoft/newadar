<?php

namespace Sttl\Proupdated\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class UpdateRead extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $session;

    protected $dataObjectFactory;

    protected $resultJsonFactory;

    //protected $_customerRepositoryInterface;

    public function __construct(
        context $context,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Sttl\Proupdated\Model\ReadFactory $ReadFactory  
    ) {
        $this->session = $customerSession;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ReadFactory = $ReadFactory;
    }
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $response = '';
        if (!$this->session->isLoggedIn()) {
            $response = [
                'session_distroy' => true,
                'message' => __("Customer session expired please login.")
            ];
            return $resultJson->setData($response);
        } else {
            $data = $this->getRequest()->getPostValue();
            $c_id = $this->session->getCustomer()->getId();
            try {
            $rowData = $this->ReadFactory->create();
                $rowData->addData([
                    "id" => $data['id'],
                    "read_json" => json_encode($data['Readjson']),
                ]);
                // $rowData->load($data['id']);
                $rowData->save();
                $response = [
                        'errors' => false,
                        'message' => __("Read data save successfully.."),
                        'UpdateReadJosn' => json_encode($data['Readjson']),
                    ];
                return $resultJson->setData($response);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $message = $e->getMessage();
                $response = [
                    'errors' => true,
                    'message' => __($message)
                ];
            } catch (\Exception $e) {
                $response = [
                    'errors' => true,
                    'message' => __($e->getMessage())
                ];
            }
            return $resultJson->setData($response);
        }
    }

}
