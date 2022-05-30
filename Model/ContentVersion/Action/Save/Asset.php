<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion\Action\Save;

use Accorin\CmsContent\Model\ContentVersion\Action\ActionInterface;
use Accorin\CmsContent\Model\ContentVersion\Entry;
use Accorin\CmsContent\Model\ContentVersionFactory;
use Accorin\CmsContent\Model\ResourceModel\ContentVersion;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

class Asset implements ActionInterface
{
    /**
     * @var ContentVersionFactory
     */
    private $contentVersionFactory;

    /**
     * @var ContentVersion
     */
    private $contentVersionResource;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var File
     */
    private $fileSystem;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param ContentVersionFactory $contentVersionFactory
     * @param ContentVersion $contentVersionResource
     * @param Reader $reader
     * @param File $fileSystem
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ContentVersionFactory $contentVersionFactory,
        ContentVersion $contentVersionResource,
        Reader $reader,
        File $fileSystem,
        DirectoryList $directoryList
    ) {
        $this->contentVersionFactory = $contentVersionFactory;
        $this->contentVersionResource = $contentVersionResource;
        $this->reader = $reader;
        $this->fileSystem = $fileSystem;
        $this->directoryList = $directoryList;
    }

    /**
     * @inheritDoc
     */
    public function execute(Entry $entry)
    {
        $filePath = $this->reader->getModuleDir(Dir::MODULE_SETUP_DIR, $entry->getModule()) .
            DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR .
            $entry->getIdentifier();

        $destinationPath = $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR .
            $entry->getAdditional()->getDestination();
        $destinationDirectories = explode(DIRECTORY_SEPARATOR, $destinationPath);
        array_pop($destinationDirectories);
        $destinationDirectories = implode(DIRECTORY_SEPARATOR, $destinationDirectories);

        $this->fileSystem->mkdir($destinationDirectories, 0775, true);
        $this->fileSystem->cp($filePath, $destinationPath);

        if ($currentVersion = $entry->getSavedVersion()) {
            $currentVersion->setVersion($entry->getVersion());
            $this->contentVersionResource->save($currentVersion);
        } else {
            $version = $this->contentVersionFactory->create();
            $version->setData([
                'type' => 'assets',
                'identifier' => $entry->getIdentifier(),
                'version' => $entry->getVersion()
            ]);
            $this->contentVersionResource->save($version);
        }
    }
}
