<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Model\Config\Converter;

class WidgetConverter extends DefaultConverter
{
    /**
     * @param string $typeName
     * @param string $entryXPath
     */
    public function __construct(string $typeName = 'widgets', string $entryXPath = '/config/widgets/widget')
    {
        parent::__construct($typeName, $entryXPath);
    }

    /**
     * @param \DOMDocument $source
     * @return array|void
     */
    public function convert($source)
    {
        $baseWidgets = parent::convert($source);
        $xpath = new \DOMXPath($source);

        foreach ($baseWidgets['widgets'] as &$widget) {
            $widgetDom = $xpath->query($this->entryXPath . '[@identifier="' . $widget["identifier"] . '"]');

            $widgetDomData = $widgetDom->item(0);
            foreach ($widgetDomData->childNodes as $childNode) {
                switch ($childNode->nodeName) {
                    case "parameters":
                        $this->convertParameters($childNode, $widget);
                        break;
                    case "page_groups":
                        $this->convertPageGroups($childNode, $widget);
                        break;
                }
            }
        }

        return $baseWidgets;
    }

    /**
     * Converts parameters xml to array
     *
     * @param $widgetDom
     * @param $widgetData
     * @return array
     */
    private function convertParameters($widgetDom, &$widgetData): array
    {
        $widgetData['parameters'] = [];

        foreach ($widgetDom->getElementsByTagName('parameter') as $parameter) {
            $widgetData['parameters'][$parameter->getAttribute('name')] = $parameter->getAttribute('value');
        }

        return $widgetData;
    }

    /**
     * Converts page groups xml to array
     *
     * @param $widgetDom
     * @param $widgetData
     * @return array
     */
    private function convertPageGroups($widgetDom, &$widgetData): array
    {
        $widgetData['page_groups'] = [];

        foreach ($widgetDom->getElementsByTagName('page_group') as $pageGroup) {
            $pageGroupType = $pageGroup->getAttribute('type');
            $pageGroupData = [
                'page_group' => $pageGroupType,
                $pageGroupType => []
            ];

            foreach ($pageGroup->childNodes as $childNode) {
                switch ($childNode->nodeName) {
                    case "page_id":
                    case "layout_handle":
                    case "block":
                    case "for":
                    case "template":
                        $pageGroupData[$pageGroupType][$childNode->localName] = $childNode->textContent;
                }
            }

            $widgetData['page_groups'][] = $pageGroupData;
        }

        return $widgetData;
    }
}
