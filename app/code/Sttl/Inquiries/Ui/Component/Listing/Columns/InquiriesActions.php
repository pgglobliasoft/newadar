<?php

namespace Sttl\Inquiries\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Sttl\Brand\Block\Adminhtml\Brand\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class InquiriesActions extends Column
{
    const INQUIRIES_URL_PATH_EDIT = 'sttl_inquiries/inquiries/edit';
    const INQUIRIES_URL_PATH_DELETE = 'sttl_inquiries/inquiries/delete';
    protected $actionUrlBuilder;
    protected $urlBuilder;
    private $editUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::INQUIRIES_URL_PATH_EDIT
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['id']]),
                        'label' => __('View')
                    ];
                    /**$item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::INQUIRIES_URL_PATH_DELETE, ['id' => $item['id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete ${ $.$data.title }'),
                            'message' => __('Are you sure you wan\'t to delete a ${ $.$data.title } record?')
                        ]
                    ];**/
                }
            }
        }
        return $dataSource;
    }
}