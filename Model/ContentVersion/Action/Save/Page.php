<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion\Action\Save;

use Accorin\CmsContent\Helper\Installs;
use Accorin\CmsContent\Model\ContentVersion\Action\ActionInterface;
use Accorin\CmsContent\Model\ContentVersion\Entry;
use Accorin\CmsContent\Model\ContentVersionFactory;
use Accorin\CmsContent\Model\ResourceModel\ContentVersion;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

class Page implements ActionInterface
{
    /**
     * @var Installs
     */
    private $installs;

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
     * @param Installs $installs
     * @param ContentVersionFactory $contentVersionFactory
     * @param ContentVersion $contentVersionResource
     * @param Reader $reader
     */
    public function __construct(
        Installs $installs,
        ContentVersionFactory $contentVersionFactory,
        ContentVersion $contentVersionResource,
        Reader $reader
    ) {
        $this->installs = $installs;
        $this->contentVersionFactory = $contentVersionFactory;
        $this->contentVersionResource = $contentVersionResource;
        $this->reader = $reader;
    }

    /**
     * @inheritDoc
     */
    public function execute(Entry $entry)
    {
        $moduleDirectory = $this->reader->getModuleDir(Dir::MODULE_SETUP_DIR, $entry->getModule()) .
            DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR;

        $this->installs->processCmsPages([[
            'title' => $entry->getAdditional()->getTitle(),
            'identifier' => $entry->getIdentifier(),
            'page_layout' => $entry->getAdditional()->getPageLayout(),
            'content_heading' => $entry->getAdditional()->getContentHeading(),
            'stores' => $entry->getStores()
        ]], $moduleDirectory);

        if ($currentVersion = $entry->getSavedVersion()) {
            $currentVersion->setVersion($entry->getVersion());
            $this->contentVersionResource->save($currentVersion);
        } else {
            $version = $this->contentVersionFactory->create();
            $version->setData([
                'type' => 'page',
                'identifier' => $entry->getIdentifier(),
                'version' => $entry->getVersion()
            ]);
            $this->contentVersionResource->save($version);
        }
    }
}
