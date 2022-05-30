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
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Widget\Model\ResourceModel\Widget\Instance;
use Magento\Widget\Model\Widget\InstanceFactory;

class Widget implements ActionInterface
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
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var InstanceFactory
     */
    private $widgetInstanceFactory;

    /**
     * @var Instance
     */
    private $widgetResourceModel;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ContentVersionFactory $contentVersionFactory
     * @param ContentVersion $contentVersionResource
     * @param BlockRepositoryInterface $blockRepository
     * @param ThemeProviderInterface $themeProvider
     * @param InstanceFactory $widgetInstanceFactory
     * @param Instance $widgetResourceModel
     * @param State $appState
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ContentVersionFactory $contentVersionFactory,
        ContentVersion $contentVersionResource,
        BlockRepositoryInterface $blockRepository,
        ThemeProviderInterface $themeProvider,
        InstanceFactory $widgetInstanceFactory,
        Instance $widgetResourceModel,
        State $appState,
        SerializerInterface $serializer
    ) {
        $this->contentVersionFactory = $contentVersionFactory;
        $this->contentVersionResource = $contentVersionResource;
        $this->blockRepository = $blockRepository;
        $this->themeProvider = $themeProvider;
        $this->widgetInstanceFactory = $widgetInstanceFactory;
        $this->widgetResourceModel = $widgetResourceModel;
        $this->appState = $appState;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function execute(Entry $entry)
    {
        if ($entry->getSavedVersion()) {
            throw new LocalizedException(
                __('Updating existing widgets is not supported')
            );
        }

        $theme = $this->themeProvider->getThemeByFullPath($entry->getAdditional()->getData('theme_id'));

        $widget = $this->widgetInstanceFactory
            ->create()
            ->setData([
                'instance_type' => $entry->getAdditional()->getData('type'),
                'theme_id' => $theme->getId(),
                'title' => $entry->getAdditional()->getData('title'),
                'store_ids' => $entry->getStores(),
                'widget_parameters' => $this->getWidgetParameters($entry),
                'page_groups' => $this->formatPageGroups($entry)
            ]);

        $isSaved = $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            function ($widgetResource, $widget) {
                try {
                    $widgetResource->save($widget);
                    return true;
                } catch (\Exception $exception) {
                    return false;
                }
            },
            [$this->widgetResourceModel, $widget]
        );

        if (!$isSaved) {
            return false;
        }

        $version = $this->contentVersionFactory->create();
        $version->setData([
            'type' => 'widgets',
            'identifier' => $entry->getIdentifier(),
            'version' => $entry->getVersion()
        ]);
        $this->contentVersionResource->save($version);
    }

    /**
     * @param Entry $entry
     * @return string
     */
    private function getWidgetParameters(Entry $entry): string
    {
        $params = [];

        foreach ($entry->getAdditional()->getData('parameters') as $parameterName => $parameterValue) {
            if ($parameterName === 'block_id') {
                try {
                    $cmsBlock = $this->blockRepository->getById($parameterValue);
                    $params[$parameterName] = $cmsBlock->getId();
                } catch (LocalizedException $localizedException) {
                    $params[$parameterName] = $parameterValue;
                }
            } else {
                $params[$parameterName] = $parameterValue;
            }
        }

        return $this->serializer->serialize($params);
    }

    /**
     * For widget page group configuration, force pages to be null if it isn't specified
     *
     * @param Entry $entry
     * @return array
     */
    private function formatPageGroups(Entry $entry): array
    {
        $pageGroups = $entry->getAdditional()->getData('page_groups');

        foreach ($pageGroups as &$pageGroup) {
            if ($pageGroup['page_group'] === 'pages') {
                if (!isset($pageGroup['pages']['page_id'])) {
                    $pageGroup['pages']['page_id'] = null;
                }
            }
        }

        return $pageGroups;
    }
}
