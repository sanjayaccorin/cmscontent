<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ContentVersion
 * @package Accorin\CmsContent\Model\ResourceModel
 */
class ContentVersion extends AbstractDb
{
    /**
     * Defines main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('accorin_content_version', 'content_id');
    }
}