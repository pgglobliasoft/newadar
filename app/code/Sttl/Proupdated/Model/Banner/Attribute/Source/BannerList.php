<?php
namespace Sttl\Proupdated\Model\Banner\Attribute\Source;

class BannerList extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

     protected $resourceConnection;
 
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions()
    {
        $connection  = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('au_magestore_bannerslider_banner');
        $query = 'SELECT * FROM ' . $tableName;
        $results = $this->resourceConnection->getConnection()->fetchAll($query);
        if (!$this->_options) {
            foreach ($results as $manufacturer) {
                $this->_options[] = [
                        'label' => __($manufacturer['name']),
                        'value' => $manufacturer['banner_id'],
                    ];
            }
            
        }
        return $this->_options;
    }
}
