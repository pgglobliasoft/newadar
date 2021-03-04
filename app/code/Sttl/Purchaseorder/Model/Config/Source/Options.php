<?php 
namespace Sttl\Purchaseorder\Model\Config\Source;
   
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;
   
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{ 


    public function GetStringAfterSecondSlashInURL($the_url)
    {
        $parts = explode("/",$the_url,9);
        $i = 0;
        foreach ($parts as $key => $value) {
          if($value === 'id')
          {
            return $parts[$i+1];
          }
          $i++;
        }
        return 0;
    }


    public function getAllOptions()
    {
        
        $data = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();   
        $str = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";    
        $product_id = $this->GetStringAfterSecondSlashInURL($str);

        if($product_id > 0){

            $productCollection = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $productCollection->load($product_id);
            if($product->getTypeId() !== 'simple' && $product->getTypeId() === 'configurable'){
                $_children = $product->getTypeInstance()->getUsedProducts($product);
              
                foreach (@$_children as $child){
                    // echo "Here are your child Product Ids ".$child->getID()."<br>";
                    $headtherprodut = $productCollection->load($child->getID());
                    // echo $headtherprodut->getId();
                    if(@$headtherprodut['heather_colors'] && $headtherprodut['heather_colors'] !== '26291' ){
                            
                            if(!in_array($headtherprodut['heather_colors'], $data)){
                                $data[]=$headtherprodut['heather_colors'];
                                $this->_options[] = [
                                                'label' => __($headtherprodut->getResource()->getAttribute('heather_colors')->getFrontend()->getValue($headtherprodut)),
                                                'value' => $headtherprodut['heather_colors'],
                                                ];
                            }
                            
                        
                    }
                }
            }
        }


        if(count($data) < 1){
            $this->_options = [ 
                            ['label'=>'no color', 'value'=>'0']
                            // ['label'=>'no color', 'value'=>'0'],
                        ];
        }
      
        // $this->_options = [ 
        //             ['label'=>'', 'value'=>''],
        //             ['label'=>'Small', 'value'=>'1'],
        //             ['label'=>'Medium', 'value'=>'2'],
        //             ['label'=>'Large', 'value'=>'3']
        //         ];
        // $this->_options = $data;




        
        return $this->_options;
    }
   
    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}