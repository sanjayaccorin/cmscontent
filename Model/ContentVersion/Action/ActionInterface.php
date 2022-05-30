<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion\Action;

use Accorin\CmsContent\Model\ContentVersion\Entry;

interface ActionInterface
{
    /**
     * @param Entry $entry
     * @return bool
     */
    public function execute(Entry $entry);
}
