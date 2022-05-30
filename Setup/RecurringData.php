<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Setup;

use Accorin\CmsContent\Model\ContentVersion\Action\ProcessContent;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RecurringData
 *
 * @package Accorin\CmsContent\Setup
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var ProcessContent
     */
    private $processContent;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProcessContent $processContent
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProcessContent $processContent,
        LoggerInterface $logger
    ) {
        $this->processContent = $processContent;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->processContent->execute();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
