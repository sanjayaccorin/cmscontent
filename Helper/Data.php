<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

class Data extends AbstractHelper
{

    /**
     * @var BlockFactory \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var LayoutInterface
     */
    protected $_layout;
    protected $galleryReadHandler;

    /**
     * Data constructor.
     * @param Context $context
     * @param BlockFactory $blockFactory
     * @param LayoutInterface $layout
     */
    public function __construct(
        Context $context,
        BlockFactory $blockFactory,
        LayoutInterface $layout,
        GalleryReadHandler $galleryReadHandler
    ) {

        $this->_layout = $layout;
        $this->_blockFactory = $blockFactory;
        $this->galleryReadHandler = $galleryReadHandler;
        parent::__construct($context);
    }

    /**
     * @param $config
     * @param string $scope
     * @return mixed
     */
    public function getConfig($config, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($config, $scope);
    }

    /**
     * Get CMS Block Content from the Database
     * @param $config
     * @return mixed
     */
    public function getCmsBlock($config)
    {
        return $this->_layout->createBlock('Magento\Cms\Block\Block')
            ->setBlockId($config)
            ->toHtml();
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getCmsBlockFromConfig($config)
    {
        return $this->getCmsBlock($this->getConfig($config));
    }

    /**
     * @param $config
     * @return string
     */
    public function getImageFromConfig($config)
    {
        $folderName = \Accorin\CmsContent\Model\Config\Backend\Image::UPLOAD_DIR;
        $storeLogoPath = $this->getConfig(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $imageUrl = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        return $imageUrl;
    }

    /** Add image gallery to $product */
    public function addGallery($product) {
      $this->galleryReadHandler->execute($product);
    }
}
