<?php
/**
 * Limesharp_Stockists extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Limesharp
 * @package   Limesharp_Stockists
 * @copyright 2016 Claudiu Creanga
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Claudiu Creanga
 */
namespace Limesharp\Stockists\Cron;
/**
 * Class Importdata
 */
 use \Psr\Log\LoggerInterface;
 use Magento\Framework\App\Action\Action;
 use Magento\Framework\App\Action\Context;

class Importdata
{
  protected $logger;
  protected $connectionFactory = null;
  private $connection;
  protected $StockistModel;
    /**
     * (cron process)
     *
     * @return void
     */
     public function __construct(Context $context,
      \Magento\Framework\App\ResourceConnection $resourceConnection,
      LoggerInterface $logger,
      \Limesharp\Stockists\Model\StockistRepository $StockistModel,
      \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory
   ) {
        $this->logger = $logger;
        $this->StockistModel = $StockistModel;
        $this->connectionFactory = $connectionFactory;
        $this->connection = $resourceConnection->getConnection('custom');
        //parent::__construct($context);
    }


    public function execute()
    {
      try {
         $this->logger->info('not 256 connect');
          // $select = $this->connection->select()->from(['Locations']);
          // $data = $this->connection->fetchAll($select);
          // if(count($data) > 0)
          // {
          //   $this->StockistModel->importDatalocation($data);
          // }
      } catch (\Exception $e) {
        $this->logger->info('not connect');
          echo sprintf($e);
      }
    }
}
