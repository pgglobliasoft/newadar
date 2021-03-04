<?php
namespace Sttl\Fdirectory\Controller\Adminhtml\Index;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Save extends \Magento\Backend\App\Action
{
	protected $configWriter;
	
	public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
		\Magento\Backend\App\Action\Context $context
    )
    {
		parent::__construct($context);
        $this->configWriter = $configWriter;
    }
	
    public function execute()
    {
		$data = $this->getRequest()->getPost('resource');
		try {
			if (empty($data))
				$data = [];
			
			$this->configWriter->save('sttl/image_library/permission',  json_encode($data), $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
			$this->messageManager->addSuccess(__('Image Library updated successfully.'));
			
		} catch (\Exception $e) {
			$this->messageManager->addError(__('Error while updating Image Library, Please try again.'));
		}
		$resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setPath('fdirectory/index/');
        return $resultRedirect;
	}
}
?>