<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion;

use Magento\PageBuilder\Api\Data\TemplateSearchResultsInterfaceFactory;

/**
 * Factory class for @see \Magento\Framework\Api\SearchResults
 */
class SearchResultsFactory extends TemplateSearchResultsInterfaceFactory
{
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\Magento\\Framework\\Api\\SearchResults'
    ) {
        parent::__construct(
            $objectManager,
            $instanceName
        );
    }
}
