<?php

namespace Sttl\Proupdated\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;

class Announcement extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
         \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function prepareDataSource(array $dataSource) {
          $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
          $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $html = '';
                $per_actual = '';
                $result = $connection->fetchAll("SELECT title FROM au_sttl_import_note");
                $a = array();
                if($this->is_valid_json($item['read_json'])){
                    $perm = json_decode($item['read_json']);
                    foreach ($perm as $key => $parent_perm) {
                    $result1 = $connection->fetchAll("SELECT title FROM au_sttl_import_note WHERE id=".$key);
                        if(!empty($result1)){
                            if($parent_perm == 1){
                            $html .= "<ul>";
                                $html .= "<li><span class='grid-severity-notice'>".$result1[0]['title']."</span></li>";
                            $html .= "</ul>";
                        }else{
                            $html .= "<ul>";
                                $html .= "<li><span class='grid-severity-major'>".$result1[0]['title']."</span></li>";
                            $html .= "</ul>";
                        }
                     $a[] = $result1[0]['title'];
                        
                    }                    
                    
                    }
                    foreach ($result as $v2) {                        
                        if(in_array($v2['title'], $a)){
                        }else {
                            $html .= "<ul>";
                                $html .= "<li><span class='grid-severity-major'>".$v2['title']."</span></li>";
                            $html .= "</ul>";
                        }
                    }  

                }
                if($item['read_json'] == ''){
                    
                    
                    foreach ($result as $v2) {
                        $html .= "<ul>";
                            $html .= "<li><span class='grid-severity-major'>".$v2['title']."</span></li>";
                        $html .= "</ul>";
                    }
                }

                $item['read_json'] = $html;

            }
        }

        return $dataSource;
    }


    function is_valid_json( $raw_json ){
        return ( json_decode( $raw_json , true ) == NULL ) ? false : true ;
    }
}