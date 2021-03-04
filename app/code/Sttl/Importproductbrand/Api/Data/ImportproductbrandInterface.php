<?php


namespace Sttl\Importproductbrand\Api\Data;

interface ImportproductbrandInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const BRAND_ID = 'brand_id';
    const BRAND_URL = 'brand_url';
    const IMPORTPRODUCTBRAND_ID = 'importproductbrand_id';
    const SKU = 'sku';
    const IPMORT_FILE = 'ipmort_file';

    /**
     * Get importproductbrand_id
     * @return string|null
     */
    public function getImportproductbrandId();

    /**
     * Set importproductbrand_id
     * @param string $importproductbrandId
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setImportproductbrandId($importproductbrandId);

    /**
     * Get brand_id
     * @return string|null
     */
    public function getBrandId();

    /**
     * Set brand_id
     * @param string $brandId
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setBrandId($brandId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Sttl\Importproductbrand\Api\Data\ImportproductbrandExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Sttl\Importproductbrand\Api\Data\ImportproductbrandExtensionInterface $extensionAttributes
    );

    /**
     * Get brand_url
     * @return string|null
     */
    public function getBrandUrl();

    /**
     * Set brand_url
     * @param string $brandUrl
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setBrandUrl($brandUrl);

    /**
     * Get sku
     * @return string|null
     */
    public function getSku();

    /**
     * Set sku
     * @param string $sku
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setSku($sku);

    /**
     * Get ipmort_file
     * @return string|null
     */
    public function getIpmortFile();

    /**
     * Set ipmort_file
     * @param string $ipmortFile
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setIpmortFile($ipmortFile);
}
