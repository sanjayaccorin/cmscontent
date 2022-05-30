<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Test\Unit\Model\Config;

use Accorin\CmsContent\Model\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var \DOMDocument
     */
    private $document;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var Converter\DefaultConverter
     */
    private $pagesConverter;

    /**
     * @var Converter\DefaultConverter
     */
    private $blocksConverter;

    /**
     * @var Converter\DefaultConverter
     */
    private $assetsConverter;

    /**
     * @var Converter\WidgetConverter
     */
    private $widgetsConverter;

    /**
     * Prepare test
     */
    protected function setUp()
    {
        $document = new \DOMDocument();

        $xml = <<<XML
<?xml version="1.0" ?>
<!--
/**
 * @package     Accorin/CmsContent
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
 -->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Accorin_CmsContent:etc/accorin_content.xsd">
    <pages>
        <page identifier="a-page">
            <title>A Page</title>
            <page_layout>1column</page_layout>
            <content_heading><![CDATA[Some Title]]></content_heading>
            <version>1.0.2</version>
            <module>Vendor_Module</module>
            <depends>
                <block identifier="a-block" />
            </depends>
            <stores>
                <store code="test" />
            </stores>
        </page>
    </pages>
    <blocks>
        <block identifier="a-block">
            <title>Some Block</title>
            <version>1.0.0</version>
            <module>Vendor_Module</module>
            <depends>
                <block identifier="b-block" />
            </depends>
        </block>
        <block identifier="b-block">
            <title>Some other Block</title>
            <version>1.0.1</version>
            <module>Vendor_Module</module>
        </block>
        <block identifier="No Depend">
            <title>nono</title>
            <version>1.0.1</version>
            <module>Vendor_Module</module>
        </block>
    </blocks>
    <assets>
        <asset identifier="icon-512.png">
            <version>1.0.0</version>
            <module>Vendor_Module</module>
            <destination>wysiwyg/sample/icon-512.png</destination>
        </asset>
    </assets>
    <widgets>
        <widget identifier="a-widget">
            <theme_id>frontend/Accorin/site</theme_id>
            <type>block</type>
            <title>Some Test Widget</title>
            <module>Vendor_Module</module>
            <version>1.0.0</version>
            <depends>
                <block identifier="a-block" />
            </depends>
            <parameters>
                <parameter name="block_id" value="a-block" />
            </parameters>
            <page_groups>
                <page_group type="pages">
                    <layout_handle>cms_index_index</layout_handle>
                    <block>content</block>
                    <for>all</for>
                    <template>widget/static_block/default.phtml</template>
                </page_group>
            </page_groups>
        </widget>
    </widgets>
</config>
XML;
        $document->loadXML($xml);
        $this->document = $document;

        $this->pagesConverter = new Converter\DefaultConverter('pages', '/config/pages/page');
        $this->blocksConverter = new Converter\DefaultConverter('blocks', '/config/blocks/block');
        $this->assetsConverter = new Converter\DefaultConverter('assets', '/config/assets/asset');
        $this->widgetsConverter = new Converter\WidgetConverter();
        $this->converter = new Converter([
            $this->pagesConverter,
            $this->blocksConverter,
            $this->assetsConverter,
            $this->widgetsConverter
        ]);
    }

    /**
     * @covers \Accorin\CmsContent\Model\Config\Converter::convert
     */
    public function testConvert()
    {
        $actual = $this->converter->convert($this->document);

        static::assertArrayHasKey('pages', $actual);
        static::assertArrayHasKey('blocks', $actual);
        static::assertArrayHasKey('assets', $actual);
        static::assertArrayHasKey('widgets', $actual);
        static::assertEquals(1, count($actual['pages']));
        static::assertEquals(3, count($actual['blocks']));
        static::assertEquals(1, count($actual['assets']));
        static::assertEquals(1, count($actual['widgets']));

        $types = [
            'pages' => ['version', 'module', 'identifier', 'title'],
            'blocks' => ['version', 'module', 'identifier', 'title'],
            'assets' => ['version', 'module', 'identifier', 'destination'],
            'widgets' => [
                'version', 'module', 'identifier', 'title', 'theme_id', 'type', 'page_groups', 'parameters'
            ]
        ];

        foreach ($types as $type => $requiredArrayKeys) {
            foreach ($actual[$type] as $item) {
                foreach ($requiredArrayKeys as $requiredArrayKey) {
                    static::assertArrayHasKey($requiredArrayKey, $item);
                }
            }
        }
    }
}
