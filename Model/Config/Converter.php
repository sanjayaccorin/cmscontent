<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\Config;

use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * @param ConverterInterface[] $converters
     */
    public function __construct(array $converters = [])
    {
        $this->converters = $converters;
    }

    /**
     * @param \DOMDocument $source
     * @return array|void
     */
    public function convert($source)
    {
        $data = [];

        foreach ($this->converters as $converter) {
            $data = array_merge_recursive($data, $converter->convert($source));
        }

        return $data;
    }
}
