<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\ContentVersion\Action\Save;

use Accorin\CmsContent\Helper\Installs;
use Accorin\CmsContent\Model\ContentVersion\Formatters\FilePath;
use Accorin\CmsContent\Model\ContentVersionFactory;
use Accorin\CmsContent\Model\ResourceModel\ContentVersion;
use Accorin\CmsContent\Model\ContentVersion\Action\ActionInterface;
use Accorin\CmsContent\Model\ContentVersion\Entry;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\PageBuilder\Model\Template as PageBuilderTemplate;
use Magento\PageBuilder\Model\TemplateFactory;
use Magento\PageBuilder\Model\TemplateRepository;

class Template implements ActionInterface
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var TemplateRepository
     */
    private $templateRepository;

    /**
     * @var Installs
     */
    private $installsHelper;

    /**
     * @var ContentVersionFactory
     */
    private $contentVersionFactory;

    /**
     * @var ContentVersion
     */
    private $contentVersionResource;

    /**
     * @var FilePath
     */
    private $filePathFormatter;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @param TemplateFactory $templateFactory
     * @param TemplateRepository $templateRepository
     * @param Installs $installsHelper
     * @param ContentVersionFactory $contentVersionFactory
     * @param ContentVersion $contentVersionResource
     * @param FilePath $filePathFormatter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        TemplateFactory $templateFactory,
        TemplateRepository $templateRepository,
        Installs $installsHelper,
        ContentVersionFactory $contentVersionFactory,
        ContentVersion $contentVersionResource,
        FilePath $filePathFormatter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateRepository = $templateRepository;
        $this->installsHelper = $installsHelper;
        $this->contentVersionFactory = $contentVersionFactory;
        $this->contentVersionResource = $contentVersionResource;
        $this->filePathFormatter = $filePathFormatter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @param Entry $entry
     * @return bool|void
     * @throws LocalizedException
     */
    public function execute(Entry $entry)
    {
        $template = $this->resolveTemplate($entry);
        $template
            ->setName($entry->getIdentifier())
            ->setCreatedFor($entry->getAdditional()->getType() ?? 'any')
            ->setPreviewImage($entry->getAdditional()->getPreviewImage() ?? '')
            ->setTemplate(
                $this->installsHelper->getContentByType(
                    '',
                    $this->getFilePath($entry),
                    'html'
                )
            );

        $this->templateRepository->save($template);

        if ($currentVersion = $entry->getSavedVersion()) {
            $currentVersion->setVersion($entry->getVersion());
            $this->contentVersionResource->save($currentVersion);
        } else {
            $version = $this->contentVersionFactory->create();
            $version->setData([
                'type' => 'templates',
                'identifier' => $entry->getIdentifier(),
                'version' => $entry->getVersion()
            ]);
            $this->contentVersionResource->save($version);
        }
    }

    /**
     * Gets file install path from entry
     *
     * @param Entry $entry
     * @return string
     * @throws LocalizedException
     */
    private function getFilePath(Entry $entry): string
    {
        if (is_string($entry->getAdditional()->getData('file'))) {
            return $this->filePathFormatter->format($entry->getAdditional()->getData('file'));
        }

        return $this->filePathFormatter->format(
            sprintf('%s::templates/%s', $entry->getModule(), $entry->getIdentifier())
        );
    }

    /**
     * Fetch existing template to update, or return a new template
     *
     * @param Entry $entry
     * @return PageBuilderTemplate
     */
    private function resolveTemplate(Entry $entry): PageBuilderTemplate
    {
        if ($entry->getSavedVersion()) {
            // find existing template based on parameters available to us
            $filters = [];

            $filters[] = $this->filterGroupBuilder->addFilter(
                $this->filterBuilder
                    ->setField(TemplateInterface::KEY_NAME)
                    ->setValue($entry->getIdentifier())
                    ->create()
            )->create();

            if ($createdFor = $entry->getAdditional()->getType()) {

                $filters[] = $this->filterGroupBuilder->addFilter(
                    $this->filterBuilder
                        ->setField(TemplateInterface::KEY_CREATED_FOR)
                        ->setValue($entry->getAdditional()->getType())
                        ->create()
                )->create();
            }

            $searchCriteria = $this->searchCriteriaBuilder
                ->setFilterGroups($filters)
                ->setPageSize(1)
                ->create();

            try {
                $templates = $this->templateRepository->getList($searchCriteria);

                if ($templates->getTotalCount() > 0) {
                    /** @var PageBuilderTemplate $template */
                    foreach ($templates->getItems() as $template) {
                        return $template;
                    }
                }
            } catch (LocalizedException $localizedException) {
                return $this->templateFactory->create();
            }
        }

        return $this->templateFactory->create();
    }
}
