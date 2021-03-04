<?php


namespace Sttl\Importproductbrand\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ImportproductbrandRepositoryInterface
{

    /**
     * Save Importproductbrand
     * @param \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface $importproductbrand
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface $importproductbrand
    );

    /**
     * Retrieve Importproductbrand
     * @param string $importproductbrandId
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($importproductbrandId);

    /**
     * Retrieve Importproductbrand matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Importproductbrand
     * @param \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface $importproductbrand
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface $importproductbrand
    );

    /**
     * Delete Importproductbrand by ID
     * @param string $importproductbrandId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($importproductbrandId);
}
