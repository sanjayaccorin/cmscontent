<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model;

use Accorin\CmsContent\Model\ResourceModel\ContentVersion as ContentVersionResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Class ContentVersion
 * @package Accorin\CmsContent\Model
 */
class ContentVersion extends AbstractModel
{
    /**
     * Initialize corresponding resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ContentVersionResourceModel::class);
        $this->_collectionName = ContentVersionResourceModel\Collection::class;
    }
}