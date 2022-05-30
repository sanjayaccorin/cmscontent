<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion\Action;

use Accorin\CmsContent\Model\ContentVersion\Entry;

class ActionPool implements ActionInterface
{
    /**
     * @var ActionInterface[]
     */
    private $processors = [];

    /**
     * @param ActionInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function execute(Entry $entry)
    {
        if (array_key_exists($entry->getType(), $this->processors)) {
            return $this->processors[$entry->getType()]->execute($entry);
        } else {
            return false;
        }
    }
}
