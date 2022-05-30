<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Module\Dir\Reader;
use Magento\Store\Model\Store;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Adds/updates CMS Block
 */

class BlockManager
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */

    private $searchCriteriaBuilder;

    /**
     * @param BlockRepository $blockRepository
     * @param BlockFactory $blockFactory
     * @param Reader $moduleReader
     * @param ReadFactory $readFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        BlockFactory $blockFactory,
        Reader $moduleReader,
        ReadFactory $readFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->moduleReader = $moduleReader;
        $this->readFactory = $readFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string $identifier
     * @return \Magento\Cms\Api\Data\PageInterface|\Magento\Cms\Model\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadCmsBlock(string $identifier)
    {
        $filter = $this->filterBuilder
            ->setField('identifier')
            ->setConditionType('eq')
            ->setValue($identifier)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$filter])
            ->create();

        $blocks = $this->blockRepository->getList($searchCriteria)->getItems();
        if (!empty($blocks)) {
            return \reset($blocks);
        }

        return $this->blockFactory->create();
    }

    /**
     * @param array $pageData
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveCmsBlock(array $blockData)
    {
        $identifier = $blockData['identifier'];
        if (empty($blockData['content'])) {
            $blockData['content'] = $this->getCmsBlockHtmlContent($identifier);
        }

        $blockData['stores'] = Store::DEFAULT_STORE_ID;

        $block = $this->loadCmsBlock($identifier);
        $block->addData($blockData);
        // echo '<pre>'; print_r($blockData['store_id']); exit;
        $this->blockRepository->save($block);
    }

    /**
     * Reads HTML file with CMS page content
     *
     * @param string $path
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getHtmlFileContent($path): string
    {
        list($moduleName, $filePath) = explode('::', $path);

        $directoryRead = $this->readFactory->create(
            $this->moduleReader->getModuleDir('', $moduleName)
        );

        if ($directoryRead->isExist($filePath) && $directoryRead->isFile($filePath)) {
            return $directoryRead->readFile($filePath);
        }

        return '';
    }

    /**
     * Loads CMS page content from HTML file
     *
     * @param string $identifier
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getCmsBlockHtmlContent($identifier): string
    {
        return $this->getHtmlFileContent(
            \sprintf('Accorin_CmsContent::Setup/resources/blocks/%s.html', $identifier)
        );
    }

    /**
     * @return blockRepositoryInterface
     */
    public function getblockRepository(): blockRepositoryInterface
    {
        return $this->blockRepository;
    }
}
