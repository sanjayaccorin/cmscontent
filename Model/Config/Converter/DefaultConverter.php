<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\Config\Converter;

use Magento\Framework\Config\ConverterInterface;

class DefaultConverter implements ConverterInterface
{
    /**
     * @var string
     */
    protected $typeName;

    /**
     * @var string
     */
    protected $entryXPath;

    /**
     * @param string $typeName
     * @param string $entryXPath
     */
    public function __construct(string $typeName = '', string $entryXPath = '')
    {
        $this->typeName = $typeName;
        $this->entryXPath = $entryXPath;
    }

    /**
     * @param \DOMDocument $source
     * @return array|void
     */
    public function convert($source)
    {
        $xpath = new \DOMXPath($source);
        $items = $xpath->query($this->entryXPath);
        $result = [];

        foreach ($items as $item) {
            $data = [];

            foreach ($item->childNodes as $typeEntry) {
                if ($typeEntry->nodeName[0] === '#') {
                    continue;
                }

                if ($typeEntry->localName === 'depends') {
                    $data[$typeEntry->localName] = [
                        'blocks' => [],
                        'pages' => []
                    ];

                    foreach ($typeEntry->childNodes as $dependNode) {
                        if ($dependNode->nodeName[0] === '#') {
                            continue;
                        }

                        $data[$typeEntry->localName][$dependNode->localName][] = $dependNode->getAttribute('identifier');
                    }
                } elseif ($typeEntry->localName === 'stores') {
                    $data[$typeEntry->localName] = [];

                    foreach ($typeEntry->childNodes as $storeNode) {
                        if ($storeNode->nodeName[0] === '#') {
                            continue;
                        }

                        $data[$typeEntry->localName][] = $storeNode->getAttribute('code');
                    }
                } else {
                    $data[$typeEntry->localName] = $typeEntry->textContent;
                }
            }

            foreach ($item->attributes as $attribute) {
                $data[$attribute->localName] = $attribute->value;
            }

            $result[] = $data;
        }

        return [
            $this->typeName => $result
        ];
    }
}
