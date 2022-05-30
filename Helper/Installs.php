<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Helper;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element;
use Magento\Framework\App\State as AppState;

class Installs extends AbstractHelper
{
    /**
     * @var string
     */
    protected $_resources;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var Element\BlockFactory
     */
    protected $_viewBlockFactory;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @var AppState
     */
    protected $_appState;

    /**
     * @var BlockCollectionFactory
     */
    protected $_blockCollectionFactory;

    /**
     * Installs constructor.
     * @param Context $context
     * @param BlockFactory $blockFactory
     * @param Element\BlockFactory $viewBlockFactory
     * @param PageFactory $pageFactory
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param AppState $state
     */
    public function __construct(
        Context $context,
        BlockFactory $blockFactory,
        Element\BlockFactory $viewBlockFactory,
        PageFactory $pageFactory,
        BlockCollectionFactory $blockCollectionFactory,
        AppState $state
    ) {
        $this->_resources = __DIR__ . '/resource/';
        $this->_blockFactory = $blockFactory;
        $this->_viewBlockFactory = $viewBlockFactory;
        $this->_pageFactory = $pageFactory;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_appState = $state;
        parent::__construct($context);
    }

    /**
     * @param $blocksArray
     * @param $resources
     * @throws \Exception
     * @deprecated Use processCmsBlocks method
     */
    public function updateCmsBlocks($blocksArray, $resources)
    {
        $this->installCmsBlocks($blocksArray, $resources);
    }

    /**
     * @param $blocksArray
     * @param $resources
     * @throws \Exception
     * @deprecated Use processCmsBlocks method
     */
    public function installCmsBlocks($blocksArray, $resources)
    {
        $this->processCmsBlocks($blocksArray, $resources);
    }

    /**
     * @param $pagesArray
     * @param $resources
     * @throws \Exception
     */
    public function installCmsPage($pagesArray, $resources)
    {
        $this->installOrUpdateCmsPage($pagesArray, $resources);
    }

    /**
     * @param $pagesArray
     * @param $resources
     * @throws \Exception
     */
    public function installOrUpdateCmsPage($pagesArray, $resources)
    {
        foreach ($pagesArray as $cmsPage) {
            $content = $this->getPageContent($resources, $cmsPage['identifier']);
            $xml = $this->getLayoutContent($resources, $cmsPage['identifier']);

            $pageData = [
                'title' => $cmsPage['title'],
                'identifier' => $cmsPage['identifier'],
                'page_layout' => $cmsPage['page_layout'],
                'content_heading' => $cmsPage['content_heading'],
                'content' => $content,
                'is_active' => array_key_exists('is_active', $cmsPage) ? $cmsPage['is_active'] : null,
                'stores' => array_key_exists('stores', $cmsPage) ? $cmsPage['stores'] : null
            ];

            if ($this->pageExists($cmsPage['identifier'])) {
                $this->setPageData($pageData, 'upgrade');
            } else {
                $this->setPageData($pageData, 'install');
            }
        }
    }

    /**
     * @param $resources
     * @param $identifier
     * @return bool|string
     * @throws \Exception
     */
    public function getPageContent($resources, $identifier)
    {
        return $this->getContentByType($resources, $identifier, 'html');
    }

    /**
     * @param $resources
     * @param $identifier
     * @param $type
     * @return bool|string
     * @throws \Exception
     */
    public function getContentByType($resources, $identifier, $type)
    {
        return $this->resourceExists($resources, $identifier, $type) ? $this->getFileContent($resources, $identifier, $type) : '';
    }

    /**
     * @param $resource
     * @param $identifier
     * @param $type
     * @return bool
     */
    public function resourceExists($resource, $identifier, $type)
    {
        return file_exists($resource . $identifier . '.' . $type);
    }

    /**
     * @param $resources
     * @param $identifier
     * @param $type
     * @return bool|string
     * @throws \Exception
     */
    public function getFileContent($resources, $identifier, $type)
    {
        try {
            return file_get_contents($resources . $identifier . '.' . $type);
        } catch (\Exception $e) {
            throw new \Exception("Error:" . $resources . $identifier . '.' . $type . ': ' . $e);
        }
    }

    /**
     * @param $resources
     * @param $identifier
     * @return bool|string
     * @throws \Exception
     */
    public function getLayoutContent($resources, $identifier)
    {
        return $this->getContentByType($resources, $identifier, 'xml');
    }

    /**
     * @param $pageId
     * @return mixed
     */
    public function pageExists($pageId)
    {
        return $this->_pageFactory->create()->load($pageId, 'identifier')->getData();
    }

    /**
     * @param $pageData
     * @param $type
     */
    public function setPageData($pageData, $type)
    {
        $page = $this->_pageFactory->create();

        if ($type == 'upgrade') {
            $page = $page->load($pageData['identifier']);

            if (is_null($pageData['is_active'])) {
                $pageData['is_active'] = !is_null($page->getIsActive()) ? $page->getIsActive() : 1;
            }

            if (is_null($pageData['stores'])) {
                $pageData['stores'] = count($page->getStores()) ? $page->getStores() : [0];
            }
        } else {
            if (is_null($pageData['is_active'])) {
                $pageData['is_active'] = 1;
            }

            if (is_null($pageData['stores'])) {
                $pageData['stores'] = [0];
            }

            $page->setIdentifier($pageData['identifier']);
        }

        $page->setTitle($pageData['title'])
            ->setPageLayout($pageData['page_layout'])
            ->setContentHeading($pageData['content_heading'])
            ->setContent($pageData['content'])
            ->setIsActive($pageData['is_active'])
            ->setStores($pageData['stores'])
            ->save();
    }

    /**
     * @param $pagesArray
     * @param $resources
     * @throws \Exception
     */
    public function updateCmsPages($pagesArray, $resources)
    {
        $this->installOrUpdateCmsPage($pagesArray, $resources);
    }

    /**
     * @param $blocksArray
     * @param $resources
     * @throws \Exception
     */
    public function processCmsBlocks($blocksArray, $resources)
    {
        try {
            $this->_appState->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            unset($e);
            // Area Code is already set.
        }

        $this->installOrUpdateCmsBlock($blocksArray, $resources);
    }

    /**
     * @param $blocksArray
     * @param $resources
     * @throws \Exception
     */
    public function installOrUpdateCmsBlock($blocksArray, $resources)
    {
        foreach ($blocksArray as $cmsBlock) {
            $blockId = $cmsBlock['identifier'];
            $content = $this->getBlockContent($resources, $blockId);

            $blockData = [
                'title' => $cmsBlock['title'],
                'identifier' => $blockId,
                'content' => $content,
                'is_active' => array_key_exists('is_active', $cmsBlock) ? $cmsBlock['is_active'] : null,
                'stores' => array_key_exists('stores', $cmsBlock) ? $cmsBlock['stores'] : null
            ];

            if ($this->blockExists($blockId)) {
                $this->setBlockData($blockData, 'upgrade');
            } else {
                $this->setBlockData($blockData, 'install');
            }
        }
    }

    /**
     * @param $resources
     * @param $identifier
     * @return bool|string
     * @throws \Exception
     */
    public function getBlockContent($resources, $identifier)
    {
        return $this->getContentByType($resources, $identifier, 'html');
    }

    /**
     * @param $blockId
     * @return bool
     */
    public function blockExists($blockId)
    {
        $collection = $this->_blockCollectionFactory->create();
        $collection->addFieldToFilter('identifier', $blockId);

        return $collection->getSize() > 0;
    }

    /**
     * @param $blockData
     * @param $type
     */
    public function setBlockData($blockData, $type)
    {
        if ($type == 'upgrade') {
            $collection = $this->_blockCollectionFactory->create();
            $collection->addFieldToFilter('identifier', $blockData['identifier']);

            $block = $collection->getFirstItem();

            if (is_null($blockData['is_active'])) {
                $blockData['is_active'] = !is_null($block->getIsActive()) ? $block->getIsActive() : 1;
            }

            if (is_null($blockData['stores'])) {
                $blockData['stores'] = count($block->getStores()) ? $block->getStores() : [0];
            }
        } else {
            $block = $this->_blockFactory->create();

            if (is_null($blockData['is_active'])) {
                $blockData['is_active'] = 1;
            }

            if (is_null($blockData['stores'])) {
                $blockData['stores'] = [0];
            }
        }

        foreach ($blockData as $key => $value) {
            $block->setData($key, $value);
        }

        $block->save();
    }

    /**
     * @param $pagesArray
     * @param $resources
     * @throws \Exception
     */
    public function processCmsPages($pagesArray, $resources)
    {
        try {
            $this->_appState->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Area Code is already set.
        }

        $this->installOrUpdateCmsPage($pagesArray, $resources);
    }
}
