<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Category;

use Magento\Framework\Api\SortOrder;
use Magefan\Blog\Model\Config\Source\CategoryDisplayMode;

/**
 * Blog category posts list
 */
class PostList extends \Magefan\Blog\Block\Post\PostList
{

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {

        parent::_preparePostCollection();
        if ($category = $this->getCategory()) {
            $this->_postCollection->addCategoryFilter($category);
        }

    }

    /**
     * Retrieve collection order field
     *
     * @return string
     */
    public function getCollectionOrderField()
    {
        //if ($this->getCategory()->getData('posts_sort_by')) {
            return self::POSTS_SORT_FIELD_BY_POSITION;
        //}

        return parent::getCollectionOrderField();
    }

    /**
     * Retrieve category instance
     *
     * @return \Magefan\Blog\Model\Category
     */
    public function getCategory()
    {
        $category_id = $this->getData('category_id');
        if($category_id)
        {
            $storeId = $this->_storeManager->getStore()->getId();
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $category = $this->_objectManager->create(\Magefan\Blog\Model\Category::class)->load($category_id);
            $category->setStoreId(1);
            return $category;
            
        }
        //echo "<pre>";print_R($category->getData());exit;
        return $data =$this->_coreRegistry->registry('current_blog_category');
         //echo "<pre>";print_R($data->getData());exit;
    }

    /**
     * Retrieve true when display of this block is allowed
     *
     * @return bool
     */
    protected function canDisplay()
    {
         $displayMode = $this->getCategory()->getData('display_mode');
        return ($displayMode == CategoryDisplayMode::POSTS);
    }

    /*
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->canDisplay()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $category = $this->getCategory();
        if ($category) {
            $this->_addBreadcrumbs($category);
            $this->pageConfig->addBodyClass('blog-category-' . $category->getIdentifier());
            $this->pageConfig->getTitle()->set($category->getMetaTitle());
            $this->pageConfig->setKeywords($category->getMetaKeywords());
            $this->pageConfig->setDescription($category->getMetaDescription());
            $this->pageConfig->addRemotePageAsset(
                $category->getCanonicalUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    $this->escapeHtml($category->getTitle())
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param  string $title
     * @param  string $key
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs($title = null, $key = null)
    {
        parent::_addBreadcrumbs();
        if ($breadcrumbsBlock = $this->getBreadcrumbsBlock()) {
            $category = $this->getCategory();
            $parentCategories = [];
            while ($parentCategory = $category->getParentCategory()) {
                $parentCategories[] = $category = $parentCategory;
            }

            for ($i = count($parentCategories) - 1; $i >= 0; $i--) {
                $category = $parentCategories[$i];
                $breadcrumbsBlock->addCrumb('blog_parent_category_' . $category->getId(), [
                    'label' => $category->getTitle(),
                    'title' => $category->getTitle(),
                    'link'  => $category->getCategoryUrl()
                ]);
            }

            $category = $this->getCategory();
            $breadcrumbsBlock->addCrumb('blog_category', [
                'label' => $category->getTitle(),
                'title' => $category->getTitle()
            ]);
        }
    }
}
