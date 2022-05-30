<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Setup;

use Accorin\CmsContent\Helper\Installs;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AbstractDataPatch
 * @package Accorin\CmsContent\Setup
 */
abstract class AbstractDataPatch implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var Reader */
    private $reader;

    /** @var Installs */
    private $installs;

    const MODULE_KEY = 'Accorin_CmsContent';

    /**
     * AbstractDataPatch constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Reader $reader
     * @param Installs $installs
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Reader $reader,
        Installs $installs
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->reader = $reader;
        $this->installs = $installs;
    }

    /**
     * @return DataPatchInterface|void
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $dir = $this->reader->getModuleDir(
            Dir::MODULE_SETUP_DIR,
            $this::MODULE_KEY
        ) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'patches' . DIRECTORY_SEPARATOR;

        $blocks = $this->getBlocks();
        if (count($blocks) > 0) {
            $this->installs->processCmsBlocks($blocks, $dir);
        }

        $pages = $this->getPages();
        if (count($pages) > 0) {
            $this->installs->processCmsPages($pages, $dir);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    protected function getBlocks(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getPages(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}