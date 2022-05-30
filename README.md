# Accorin CMS Content Import
Accorin CMS Content Module for Magento 2

## Creating Static Block - Pages - Widget - Templates
1. Update `etc/accorin_content.xml`
2. Create `*.html` files in `Vendor/Module/Setup/resources/blocks*.html`
3. Create `*.html` files in `Vendor/Module/Setup/resources/pages*.html`
4. Create `*.html` files in `Vendor/Module/Setup/resources/templates*.html`
5. Create `*.html` files in `Vendor/Module/Setup/resources/assets*.html`

`accorin_content.xml`
```XML
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
    <templates>
        <template identifier="sample-quotes-template">
            <preview_image>.template-manager/sample-quotes.jpg</preview_image>
            <type>any</type>
            <version>1.0.0</version>
            <module>Vendor_Module</module>
        </template>
    </templates>
    <assets>
        // template preview image
        <asset identifier="sample-quotes.jpg">
            <destination>.template-manager/sample-quotes.jpg</destination>
            <version>1.0.0</version>
            <module>Vendor_Module</module>
        </asset>
        <asset identifier="sample-quotes-thumb.jpg">
            <destination>.template-manager/sample-quotes-thumb.jpg</destination>
            <version>1.0.0</version>
            <module>Vendor_Module</module>
        </asset>

        // wysiwyg image
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
```
