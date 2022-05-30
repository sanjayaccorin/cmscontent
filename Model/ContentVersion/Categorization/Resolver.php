<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion\Categorization;

use Accorin\CmsContent\Model\ContentVersion;
use Accorin\CmsContent\Model\ContentVersion\Categorization;
use Accorin\CmsContent\Model\ContentVersion\CategorizationFactory;
use Accorin\CmsContent\Model\ContentVersion\Entry;
use Accorin\CmsContent\Model\ResourceModel\ContentVersion\Collection;
use Accorin\CmsContent\Model\ResourceModel\ContentVersion\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

class Resolver
{
    /**
     * @var CollectionFactory
     */
    private $contentVersionCollectionFactory;

    /**
     * @var CategorizationFactory
     */
    private $categorizationFactory;

    /**
     * @var Categorization
     */
    private $newCategory;

    /**
     * @var Categorization
     */
    private $updateCategory;

    /**
     * @var array
     */
    private $savedVersionsMap = [];

    /**
     * @var array
     */
    private $entryMap = [];

    /**
     * @param CollectionFactory $contentVersionCollectionFactory
     * @param CategorizationFactory $categorizationFactory
     */
    public function __construct(
        CollectionFactory $contentVersionCollectionFactory,
        CategorizationFactory $categorizationFactory
    ) {
        $this->contentVersionCollectionFactory = $contentVersionCollectionFactory;
        $this->categorizationFactory = $categorizationFactory;
    }

    /**
     * @param Entry[] $entries
     * @return Categorization[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $entries): array
    {
        foreach ($entries as $entry) {
            $this->entryMap[$entry->getType()][$entry->getIdentifier()] = $entry;
        }

        foreach ($entries as $entry) {
            $this->processEntry($entry);
        }

        return [
            $this->getNewCategorization(),
            $this->getUpdateCategorization()
        ];
    }

    /**
     * @param Entry $entry
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processEntry(Entry $entry): void
    {
        if (!$entry->isCategorized()) {
            $savedVersions = $this->getSavedVersionsMap();

            if (isset($savedVersions[$entry->getType()][$entry->getIdentifier()])) {
                $savedVersion = $savedVersions[$entry->getType()][$entry->getIdentifier()];
                $entry->setSavedVersion($savedVersion);

                if (version_compare($entry->getVersion(), $savedVersion->getVersion(), '>')) {
                    foreach ($entry->getDepends() as $dependType => $dependentEntries) {
                        foreach ($dependentEntries as $dependentEntryName) {
                            $dependentEntry = $this->entryMap[$dependType][$dependentEntryName];
                            $this->processEntry($dependentEntry);
                        }
                    }

                    $categorization = $this->getUpdateCategorization();
                    $items = $categorization->getItems();
                    $items[] = $entry;

                    $categorization->setItems($items);
                }
            } else {
                foreach ($entry->getDepends() as $dependType => $dependentEntries) {
                    foreach ($dependentEntries as $dependentEntryName) {
                        if (!isset($this->entryMap[$dependType][$dependentEntryName])) {
                            throw new LocalizedException(__('Content Version dependencies must exist as a accorin_content.xml configuration'));
                        }
                        $dependentEntry = $this->entryMap[$dependType][$dependentEntryName];
                        $this->processEntry($dependentEntry);
                    }
                }

                $categorization = $this->getNewCategorization();
                $items = $categorization->getItems();
                $items[] = $entry;

                $categorization->setItems($items);
            }

            $entry->setIsCategorized(true);
        }
    }

    /**
     * @return array
     */
    private function getSavedVersionsMap(): array
    {
        if (!$this->savedVersionsMap) {
            /** @var Collection $contentCollection */
            $contentCollection = $this->contentVersionCollectionFactory->create();
            $savedVersionsMap = [];

            /** @var ContentVersion $contentItem */
            foreach ($contentCollection->getItems() as $contentItem) {
                $savedVersionsMap[$contentItem->getType()][$contentItem->getIdentifier()] = $contentItem;
            }

            $this->savedVersionsMap = $savedVersionsMap;
        }

        return $this->savedVersionsMap;
    }

    /**
     * @return Categorization
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getNewCategorization(): Categorization
    {
        if (!$this->newCategory) {
            /** @var Categorization $categorization */
            $categorization = $this->categorizationFactory->create();
            $categorization->setType(Categorization::IS_NEW)
                ->setItems([]);

            $this->newCategory = $categorization;
        }

        return $this->newCategory;
    }

    /**
     * @return Categorization
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getUpdateCategorization(): Categorization
    {
        if (!$this->updateCategory) {
            /** @var Categorization $categorization */
            $categorization = $this->categorizationFactory->create();
            $categorization->setType(Categorization::IS_UPDATE)
                ->setItems([]);

            $this->updateCategory = $categorization;
        }

        return $this->updateCategory;
    }
}
