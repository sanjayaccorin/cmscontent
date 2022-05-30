<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ResourceModel\ContentVersion;

use Accorin\CmsContent\Model\ContentVersion as ContentVersionModel;
use Accorin\CmsContent\Model\ResourceModel\ContentVersion as ContentVersionResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Accorin\CmsContent\Model\ResourceModel\ContentVersion
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ContentVersionModel::class, ContentVersionResourceModel::class);
    }
}