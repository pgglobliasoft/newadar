<?php

namespace DR\Gallery\Api\Data;

interface ImageInterface
{
    const RELATIVE_PATH_FROM_MEDIA_TO_FILE = 'gallery' . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR;
    const RELATIVE_PATH_FROM_MEDIA_TO_FILE_SMALL = 'gallery' . DIRECTORY_SEPARATOR . 'small image' . DIRECTORY_SEPARATOR;

    const ID = 'image_id';
    const NAME = 'name';
    const PATH = 'path';
    const STATUS = 'status';
    const DWONLOAD = 'download';
    const SMALLIMAGE = 'small_image';
    const DOWNLOADURL = 'download_url';
    const CUSTOMURL = 'custom_url';
    const CATEGORY = 'category_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Retrieve id
     *
     * @return int
     */
    public function getId();

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName();

    /**
     * Retrieve path
     *
     * @return string
     */
    public function getPath();
    
    /**
     * Retrieve category
     *
     * @return string
     */
    public function getCategoryId();

    /**
     * Retrieve small_image
     *
     * @return string
     */
    public function getSmallImage();

    /**
     * Retrieve download_url
     *
     * @return string
     */
    public function getDownloadUrl();

    /**
     * Retrieve custom_url
     *
     * @return string
     */
    public function getCustomUrl();

    /**
     * Retrieve status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Retrieve download
     *
     * @return int
     */
    public function getDownload();

    /**
     * Retrieve created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Retrieve updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set id
     *
     * @param int $id
     * @return ImageInterface
     */
    public function setId($id);

    /**
     * Set name
     *
     * @param string $name
     * @return ImageInterface
     */
    public function setName($name);

    /**
     * Set name
     *
     * @param string $category
     * @return ImageInterface
     */
    public function setCategoryId($category_id);

    /**
     * Set path
     *
     * @param string $path
     * @return ImageInterface
     */
    public function setPath($path);
    
        /**
     * Set small_image
     *
     * @param string $small_image
     * @return ImageInterface
     */
    public function setSmallImage($small_image);


    /**
     * Set downaload_url
     *
     * @param string $download_url
     * @return ImageInterface
     */
    public function setDownloadUrl($download_url);
  
    /**
     * Set custom_url
     *
     * @param string $custom_url
     * @return ImageInterface
     */
    public function setCustomUrl($custom_url);


    /**
     * Set status
     *
     * @param int $status
     * @return ImageInterface
     */
    public function setStatus($status);

     /**
     * Set download
     *
     * @param int $download
     * @return ImageInterface
     */
    public function setDownload($download);

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return ImageInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return ImageInterface
     */
    public function setUpdatedAt($updatedAt);
}