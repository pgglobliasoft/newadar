<?php

namespace Globala\Customapi\Api;

interface ProductApiInterface
{
    /**
     * get test Api data.
     *
     * @api
     *
     * @param string $id
     *
     * @return \Globala\Customapi\Model\Api
     */
    public function getApiData($id);
}