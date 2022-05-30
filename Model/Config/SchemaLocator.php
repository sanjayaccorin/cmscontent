<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

/**
 * Class SchemaLocator
 * @package Accorin\CmsContent\Model\Config
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * XML Schema definition
     */
    const CONFIG_FILE_SCHEMA = 'accorin_content.xsd';

    /**
     * @var string
     */
    protected $schema;

    /**
     * @var string
     */
    protected $perFileSchema;

    /**
     * SchemaLocator constructor.
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $configDir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Accorin_CmsContent');
        $this->schema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
        $this->perFileSchema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
    }

    /**
     * @inheritDoc
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @inheritDoc
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
