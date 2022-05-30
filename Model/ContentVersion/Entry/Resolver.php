<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion\Entry;

use Accorin\CmsContent\Model\Config\Data;
use Accorin\CmsContent\Model\ContentVersion\Entry;
use Accorin\CmsContent\Model\ContentVersion\EntryFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Store\Model\StoreManagerInterface;

class Resolver
{
    /**
     * @var Data
     */
    private $contentConfig;

    /**
     * @var EntryFactory
     */
    private $entryFactory;

    /**
     * @var DataObjectFactory
     */
    private $additionalDataFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Data $contentConfig
     * @param EntryFactory $entryFactory
     * @param DataObjectFactory $additionalDataFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $contentConfig,
        EntryFactory $entryFactory,
        DataObjectFactory $additionalDataFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->contentConfig = $contentConfig;
        $this->entryFactory = $entryFactory;
        $this->additionalDataFactory = $additionalDataFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @return Entry[]
     */
    public function execute(): array
    {
        $entries = [];
        $typeMap = ['blocks' => 'block', 'pages' => 'page'];

        foreach ($this->contentConfig->get() as $type => $data) {
            foreach ($data as $datum) {
                /**
                 * Transform type to singular version, as it gets matched against the value stored in the DB
                 * This is to keep things backwards compatible, while still allowing for future extensibility
                 */
                if (array_key_exists($type, $typeMap)) {
                    $type = $typeMap[$type];
                }

                /** @var Entry $entry */
                $entry = $this->entryFactory->create();
                $entry
                    ->setType($type)
                    ->setModule($datum['module'])
                    ->setVersion($datum['version'])
                    ->setIdentifier($datum['identifier'])
                    ->setDepends($datum['depends'] ?? [])
                    ->setStores($this->getStoreIdArray($datum));

                /** @var DataObject $additionalData */
                $additionalData = $this->additionalDataFactory->create();
                $additionalData->setData($datum);

                $entry->setAdditional($additionalData);

                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getStoreIdArray(array $data): array
    {
        if (array_key_exists('stores', $data)) {
            $stores = $this->storeManager->getStores(true, true);
            $storeIds = [];

            foreach ($data['stores'] as $storeCode) {
                if (array_key_exists($storeCode, $stores)) {
                    $storeIds[] = $stores[$storeCode]->getId();
                }
            }

            return count($storeIds) > 0 ? $storeIds : [0];
        }

        return [0];
    }
}
