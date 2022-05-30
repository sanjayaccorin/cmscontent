<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion;

use Accorin\CmsContent\Model\ContentVersion;
use Codeception\Exception\ContentNotFound;
use Magento\Framework\DataObject;

class Entry extends DataObject
{
    const TYPE = 'type';
    const MODULE = 'module';
    const VERSION = 'version';
    const IDENTIFIER = 'identifier';
    const DEPENDS = 'depends';
    const ADDITIONAL = 'additional';
    const IS_CATEGORIZED = 'categorized';
    const SAVED_VERSION = 'saved_version';
    const STORES = 'stores';

    /**
     * @param string $type
     * @return Entry
     */
    public function setType(string $type): self
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @param string $module
     * @return Entry
     */
    public function setModule(string $module): self
    {
        return $this->setData(self::MODULE, $module);
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->getData(self::MODULE);
    }

    /**
     * @param string $version
     * @return Entry
     */
    public function setVersion(string $version): self
    {
        return $this->setData(self::VERSION, $version);
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->getData(self::VERSION);
    }

    /**
     * @param string $identifier
     * @return Entry
     */
    public function setIdentifier(string $identifier): self
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * @param array $depends
     * @return Entry
     */
    public function setDepends(array $depends): self
    {
        return $this->setData(self::DEPENDS, $depends);
    }

    /**
     * @return array
     */
    public function getDepends(): array
    {
        return $this->getData(self::DEPENDS) ?? [];
    }

    /**
     * @param DataObject $additional
     * @return Entry
     */
    public function setAdditional(DataObject $additional): self
    {
        return $this->setData(self::ADDITIONAL, $additional);
    }

    /**
     * @return DataObject|null
     */
    public function getAdditional(): ?DataObject
    {
        return $this->getData(self::ADDITIONAL);
    }

    /**
     * @param bool $categorized
     * @return Entry
     */
    public function setIsCategorized(bool $categorized): self
    {
        return $this->setData(self::IS_CATEGORIZED, $categorized);
    }

    /**
     * @return bool
     */
    public function isCategorized(): bool
    {
        return (bool)$this->getData(self::IS_CATEGORIZED);
    }

    /**
     * @param ContentVersion $contentVersion
     * @return Entry
     */
    public function setSavedVersion(ContentVersion $contentVersion): self
    {
        return $this->setData(self::SAVED_VERSION, $contentVersion);
    }

    /**
     * @return ContentVersion
     */
    public function getSavedVersion(): ?ContentVersion
    {
        return $this->getData(self::SAVED_VERSION);
    }

    /**
     * @param array $storeIds
     * @return Entry
     */
    public function setStores(array $storeIds): self
    {
        return $this->setData(self::STORES, $storeIds);
    }

    /**
     * @return array
     */
    public function getStores(): array
    {
        return $this->getData(self::STORES) ?? [0];
    }
}
