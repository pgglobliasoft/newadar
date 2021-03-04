<?php
namespace ManishJoy\Login\Ui\Component\Form\Element;


class DataProvider extends \Magento\Ui\Component\Form\Element\Input
{
  /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
      parent::prepare();

      $customValue = true;

      $config = $this->getData('config');

      if(isset($config['dataScope']) && $config['dataScope']=='admin_custom'){
        $config['default']= $customValue;
        $this->setData('config', (array)$config);
      }
    }
}