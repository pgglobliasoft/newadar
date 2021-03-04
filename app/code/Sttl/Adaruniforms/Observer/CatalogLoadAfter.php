<?php
namespace Sttl\Adaruniforms\Observer;

class CatalogLoadAfter implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
     \Psr\Log\LoggerInterface $logger,
     \Magento\Framework\App\Request\Http $request
    ) {

        $this->logger = $logger;
        $this->request = $request;
    }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
         $controller = $this->request->getControllerName();
         if($controller == 'product'){
            $product = $observer->getEvent()->getProduct();
            $product->setMetaTitle('Adar Uniforms - '.$product->getSku());
         }
    }

}