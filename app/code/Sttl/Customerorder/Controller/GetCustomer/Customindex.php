<?php
/**
 * Category     Webizon
 * Package      Webizon_ApiConnector
 * Developer    Manimaran A
 * Date         03/03/2018
 * Copyright © 2018 Webizon Technologies. All rights reserved.
 */
namespace Sttl\Customerorder\Controller\GetCustomer;

class Customindex extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;
    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    protected $config;
    /**
     * @param Context $context
     * @param \Magento\Indexer\Model\IndexerFactory $resultLayoutFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Framework\Indexer\ConfigInterface $config
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->config = $config;
        parent::__construct($context);
    }
    /**
     * Regenerate full index
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if(isset($params['run'])){
            if($params['run'] == 'all'){
                $this->reindexAll();
            }else{
                $this->reindexOne($params['run']);
            }
        }
        echo 'run sucessfully';
    }
    /**
     * Regenerate single index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexOne($indexId){
        $indexer = $this->indexerFactory->create()->load($indexId);
        $indexer->reindexAll();
    }
    /**
     * Regenerate all index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexAll(){
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
    }
}