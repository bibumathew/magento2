<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Converts sales totals (incl. nominal, creditmemo, invoice) from DOMDocument to array
 */
class Magento_Sales_Model_Config_Converter implements Magento_Config_ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws InvalidArgumentException
     */
    public function convert($source)
    {
        $output = array();
        if (!$source instanceof DOMDocument) {
            return $output;
        }

        /** @var DOMNodeList $sections*/
        $sections = $source->getElementsByTagName('section');

        /** @var DOMElement $section */
        foreach ($sections as $section) {
            $sectionArray = array();
            $sectionName = $section->getAttribute('name');

            if (!$sectionName) {
                throw new InvalidArgumentException('Attribute "name" of "section" does not exist');
            }

            /** @var DOMNodeList $groups */
            $groups = $section->getElementsByTagName('group');
            /** @var DOMElement $group */

            foreach ($groups as $group) {
                $groupArray = array();
                $groupName = $group->getAttribute('name');
                if (!$groupName) {
                    throw new InvalidArgumentException('Attribute "name" of "group" does not exist');
                }

                /** @var DOMNodeList $items */
                $items = $group->getElementsByTagName('item');
                /** @var DOMElement $item */

                foreach ($items as $item) {
                    $rendererArray = array();
                    $itemName = $item->getAttribute('name');
                    if (!$itemName) {
                        throw new InvalidArgumentException('Attribute "name" of "item" does not exist');
                    }

                    /** @var DOMNodeList $renderers */
                    $renderers = $item->getElementsByTagName('renderer');
                    /** @var DOMElement $renderer */
                    foreach ($renderers as $renderer) {
                        $rendererName = $renderer->getAttribute('name');
                        if (!$rendererName) {
                            throw new InvalidArgumentException('Attribute "name" of "renderer" does not exist');
                        }
                        $rendererArray[$rendererName] = $renderer->getAttribute('instance');
                    }

                    $itemArray = array(
                        'instance' => $item->getAttribute('instance'),
                        'sort_order' => $item->getAttribute('sort_order'),
                        'renderers' => $rendererArray
                    );
                    $groupArray[$itemName] = $itemArray;
                }
                $sectionArray[$groupName] = $groupArray;
            }
            $output[$sectionName] = $sectionArray;
        }

        $order = $source->getElementsByTagName('order')->item(0);
        $availableProductTypes = array();
        /** @var DOMElement $order */
        if ($order) {
            /** @var DOMNodeList $types */
            $types = $order->getElementsByTagName('available_product_type');

            /** @var DOMElement $type */
            foreach ($types as $type) {
                $availableProductTypes[] = $type->getAttribute('name');
            }
            $output['order']['available_product_types'] = $availableProductTypes;
        }

        return $output;
    }
}
