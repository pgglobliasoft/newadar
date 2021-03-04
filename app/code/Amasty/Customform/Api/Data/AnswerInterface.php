<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Api\Data;

/**
 * @api
 */
interface AnswerInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ANSWER_ID = 'answer_id';
    const FORM_ID = 'form_id';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const IP = 'ip';
    const CUSTOMER_ID = 'customer_id';
    const RESPONSE_JSON = 'response_json';
    const ADMIN_RESPONSE_EMAIL = 'admin_response_email';
    const ADMIN_RESPONSE_MESSAGE = 'admin_response_message';
    const ADMIN_RESPONSE_STATUS = 'admin_response_status';

    /**
     * @return int
     */
    public function getAnswerId();

    /**
     * @param int $answerId
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setAnswerId($answerId);

    /**
     * @return int
     */
    public function getFormId();

    /**
     * @param int $formId
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setFormId($formId);

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * @param string $storeId
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getIp();

    /**
     * @param string $ip
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setIp($ip);

    /**
     * @return string
     */
    public function getResponseJson();

    /**
     * @param string $json
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setResponseJson($json);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getAdminResponseEmail();

    /**
     * @param string $responseEmail
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setAdminResponseEmail($responseEmail);

    /**
     * @return string
     */
    public function getResponseMessage();

    /**
     * @param string $responseMessage
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setResponseMessage($responseMessage);

    /**
     * @return string
     */
    public function getRecipientEmail();

    /**
     * @return string
     */
    public function getCustomerName();

    /**
     * @return string
     */
    public function getAdminResponseStatus();

    /**
     * @param string $responseStatus
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     */
    public function setAdminResponseStatus($responseStatus);
}
