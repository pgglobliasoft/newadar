<?php


namespace Sttl\Importproductbrand\Model\Data;

use Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface;

class Importproductbrand extends \Magento\Framework\Api\AbstractExtensibleObject implements ImportproductbrandInterface
{

    /**
     * Get importproductbrand_id
     * @return string|null
     */
    public function getImportproductbrandId()
    {
        return $this->_get(self::IMPORTPRODUCTBRAND_ID);
    }

    /**
     * Set importproductbrand_id
     * @param string $importproductbrandId
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setImportproductbrandId($importproductbrandId)
    {
        return $this->setData(self::IMPORTPRODUCTBRAND_ID, $importproductbrandId);
    }

    /**
     * Get brand_id
     * @return string|null
     */
    public function getBrandId()
    {
        return $this->_get(self::BRAND_ID);
    }

    /**
     * Set brand_id
     * @param string $brandId
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setBrandId($brandId)
    {
        return $this->setData(self::BRAND_ID, $brandId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Sttl\Importproductbrand\Api\Data\ImportproductbrandExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Sttl\Importproductbrand\Api\Data\ImportproductbrandExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get brand_url
     * @return string|null
     */
    public function getBrandUrl()
    {
        return $this->_get(self::BRAND_URL);
    }

    /**
     * Set brand_url
     * @param string $brandUrl
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setBrandUrl($brandUrl)
    {
        return $this->setData(self::BRAND_URL, $brandUrl);
    }

    /**
     * Get sku
     * @return string|null
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * Set sku
     * @param string $sku
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Get ipmort_file
     * @return string|null
     */
    public function getIpmortFile()
    {
        return $this->_get(self::IPMORT_FILE);
    }

    /**
     * Set ipmort_file
     * @param string $ipmortFile
     * @return \Sttl\Importproductbrand\Api\Data\ImportproductbrandInterface
     */
    public function setIpmortFile($ipmortFile)
    {
        return $this->setData(self::IPMORT_FILE, $ipmortFile);
    }
}
