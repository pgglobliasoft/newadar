<?php

namespace DR\Gallery\Block\Adminhtml\Image\Edit;

use DR\Gallery\Api\Data\ImageInterface;
use DR\Gallery\Model\Image\Source\Status;
use DR\Gallery\Model\Image\Source\Category;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;

class Form extends Generic
{
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('image_form');
        $this->setTitle(__('Document'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('dr_gallery_image');

        /** @var DataForm $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data']]
        );

        $form->setHtmlIdPrefix('image_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField(
                ImageInterface::ID,
                'hidden',
                ['name' => ImageInterface::ID]
            );
        }

        $fieldset->addField(
            'publish',
            'multiselect',
			[
				'name' => 'publish[]',
				'label' => __('Publish'),
				'title' => __('Publish'),
				'class' => 'publish_restrict',
				'values' => [
					['label' => __('Public'), 'value' => 0],
					['label' => __('Private'), 'value' => 1]
				],
				'style' => 'height:65px',
                'required' => true
			]
        );
        $fieldset->addField(
            ImageInterface::CATEGORY,
            'select',
            [
                'label' => __('Category'),
                'title' => __('Category'),
                'name' => ImageInterface::CATEGORY,
                'required' => true,
                'options' => $model->getCustomCategory()
            ]
        );


        $fieldset->addField(
            ImageInterface::NAME,
            'text',
            ['name' => ImageInterface::NAME, 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );

        $fieldset->addField(
            ImageInterface::PATH,
            'image',
            ['name' => ImageInterface::PATH, 'label' => __('File'), 'title' => __('File'),'note' => '<strong style="text-align:center;width:100%;display: block;">OR</strong>']
        );


        $fieldset->addField(
            ImageInterface::CUSTOMURL,
            'text',
            ['name' => ImageInterface::CUSTOMURL, 'label' => __('URL'), 'title' => __('URL')]
        );

        $fieldset->addField(
            ImageInterface::DOWNLOADURL,
            'text',
            ['name' => ImageInterface::DOWNLOADURL, 'label' => __('Download URL'), 'title' => __('Download Link')]
        );
 

        $fieldset->addField(
            ImageInterface::SMALLIMAGE, 
            'image',
            ['name' => ImageInterface::SMALLIMAGE, 'label' => __('Small Image'), 'title' => __('Small Image'),'required' => true]
        )->setAfterElementHtml(
            '
            <script>
                require([
                    "jquery",
                    "jquery/ui",
                    "jquery/validate",
                    "mage/translate"
                ], function($){
                    $("#image_small_image").addClass("small-image");                    
                    $.validator.addMethod(
                        "small-image", function (v, elm) {
                            var data = $("#edit_form").serializeArray().reduce(function(obj, item) {
                                obj[item.name] = item.value;
                                return obj;
                            }, {});                             
                            var extensions = ["jpeg", "jpg", "png","JPEG","PNG","JPG"];
                            if (!v) {
                                    if(!data["small_image[value]"]){
                                         return false;
                                    }else{
                                        return true;
                                    }  
                            }else{ return true; }
                            with (elm) {
                                var ext = value.substring(value.lastIndexOf(".") + 1);
                                for (i = 0; i < extensions.length; i++) {
                                    if (ext == extensions[i]) {
                                        return true;
                                    }
                                }
                            }
                            return false;
                        }, $.mage.__("Inserted image have disallowed file type."));
                  });
           </script>
        '
    );

        $fieldset->addField(
            ImageInterface::STATUS,
            'select',
            [
                'label' => __('URL'),
                'title' => __('Status'),
                'name' => ImageInterface::STATUS,
                'required' => true,
                'options' => $model->getAvailableStatuses()
            ]
        );
         $fieldset->addField(
             ImageInterface::DWONLOAD,
            'select',
            [
                'label' => __('DWONLOAD PDF'),
                'title' => __('DWONLOAD PDF'),
                'name' =>  ImageInterface::DWONLOAD,
                'required' => true,
                'options' => $model->getAvailableStatuses()
            ]
        );
         

        // $fieldset->addField(
        //      ImageInterface::DWONLOAD,
        //     'checkbox',
        //     [
        //         'label' => __('Document Download'),
        //         'name' => ImageInterface::DWONLOAD,               
        //         'onchange'   => 'this.value = this.checked ? 1 : 0;',
        //         'checked' => true ,
        //         'class' =>'admin__control-checkbox'
        //     ]
        // )->setAfterElementHtml('<label class="admin__field-label"  for="download"> &nbsp;&nbsp; enable to Download Document</label>');

        if (!$model->getId()) {
            $model->setData('status', Status::ENABLED);
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function toOptionArray()
    {
        return [
            [
                'value'=>1,
                'label'=>"catlog"
            ],
            [
                'value'=>2,
                'label'=>"Swatch Card"
            ]
        ];
    }
}