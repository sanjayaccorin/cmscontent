<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion\Action\PostProcess;

use Accorin\CmsContent\Model\ContentVersion\Action\ActionInterface;
use Accorin\CmsContent\Model\ContentVersion\Entry;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;

class Page implements ActionInterface
{
    /**
     * @var BlockCollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * @var GetPageByIdentifierInterface
     */
    private $pageByIdentifier;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * PageInstallPostProcessor constructor.
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param GetPageByIdentifierInterface $pageByIdentifier
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        BlockCollectionFactory $blockCollectionFactory,
        GetPageByIdentifierInterface $pageByIdentifier,
        PageRepositoryInterface $pageRepository
    ) {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->pageByIdentifier = $pageByIdentifier;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Entry $entry)
    {
        $page = $this->pageByIdentifier->execute($entry->getIdentifier(), 0);
        $pageContent = $page->getContent();

        $matches = [];
        if (preg_match_all('/block_id=\"(.*?)\"/', $pageContent, $matches)) {
            $collection = $this->blockCollectionFactory->create();
            $blocksIdMappings = $collection
                ->addFieldToSelect(['block_id', 'identifier'])
                ->addFieldToFilter('identifier', ['in' => $matches[1]])
                ->load();

            /** @var BlockInterface $blockIdMapping */
            foreach ($blocksIdMappings as $blockIdMapping) {
                $content = preg_replace(
                    '/block_id=\"' . $blockIdMapping->getIdentifier() . '\"/',
                    'block_id="' . $blockIdMapping->getId() . '"',
                    $page->getContent()
                );
                $page->setContent($content);
            }

            $this->pageRepository->save($page);
        }
    }
}
