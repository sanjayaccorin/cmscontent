<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Module\Dir\Reader;
use Magento\Store\Model\Store;

/**
 * Adds/updates CMS pages
 */
class PageManager
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var PageFactory
     */
    private $pageFactory;

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
     * @param PageRepositoryInterface $pageRepository
     * @param PageFactory $pageFactory
     * @param Reader $moduleReader
     * @param ReadFactory $readFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        PageFactory $pageFactory,
        Reader $moduleReader,
        ReadFactory $readFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageFactory = $pageFactory;
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
    public function loadCmsPage(string $identifier)
    {
        $filter = $this->filterBuilder
            ->setField('identifier')
            ->setConditionType('eq')
            ->setValue($identifier)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$filter])
            ->create();

        $pages = $this->pageRepository->getList($searchCriteria)->getItems();
        if (!empty($pages)) {
            return \reset($pages);
        }

        return $this->pageFactory->create();
    }

    /**
     * @param array $pageData
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveCmsPage(array $pageData)
    {
        $identifier = $pageData['identifier'];
        if (empty($pageData['content'])) {
            $pageData['content'] = $this->getCmsPageHtmlContent($identifier);
        }

        $pageData['store_id'] = Store::DEFAULT_STORE_ID;

        $page = $this->loadCmsPage($identifier);
        $page->addData($pageData);

        $this->pageRepository->save($page);
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
    private function getCmsPageHtmlContent($identifier): string
    {
        return $this->getHtmlFileContent(
            \sprintf('Accorin_CmsContent::Setup/resources/pages/%s.html', $identifier)
        );
    }

    /**
     * @return PageRepositoryInterface
     */
    public function getPageRepository(): PageRepositoryInterface
    {
        return $this->pageRepository;
    }
}
