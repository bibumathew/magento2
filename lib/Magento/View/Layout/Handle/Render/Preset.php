<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */

namespace Magento\View\Layout\Handle\Render;

use Magento\View\Layout\Handle\AbstractHandle;
use Magento\View\Layout\Handle\RenderInterface;

use Magento\View\LayoutInterface;
use Magento\View\Layout\Element;
use Magento\View\Layout\HandleFactory;
use Magento\View\Render\RenderFactory;
use Magento\View\Layout\ProcessorFactory;
use Magento\View\Layout\ProcessorInterface;
use Magento\View\LayoutFactory;
use Magento\View\Render\Html;
use Magento\Core\Model\Layout\Argument\Processor;

/**
 * @package Magento\View
 */
class Preset extends AbstractHandle implements RenderInterface
{
    /**
     * Preset type
     */
    const TYPE = 'preset';

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ProcessorFactory
     */
    protected $processorFactory;

    /**
     * @var LayoutInterface[]
     */
    protected $layouts = array();

    /**
     * @var int
     */
    protected $inc = 0;

    /**
     * @param HandleFactory $handleFactory
     * @param RenderFactory $renderFactory
     * @param LayoutFactory $layoutFactory
     * @param Processor $argumentProcessor
     * @param ProcessorFactory $processorFactory
     */
    public function __construct(
        HandleFactory $handleFactory,
        RenderFactory $renderFactory,
        LayoutFactory $layoutFactory,
        Processor $argumentProcessor,
        ProcessorFactory $processorFactory
    )
    {
        parent::__construct($handleFactory, $renderFactory, $argumentProcessor);

        $this->layoutFactory = $layoutFactory;
        $this->processorFactory = $processorFactory;
    }

    /**
     * @inheritdoc
     */
    public function parse(Element $layoutElement, LayoutInterface $layout, $parentName)
    {
        $elementName = $layoutElement->getAttribute('name');
        if (!isset($elementName)) {
            return $this;
        }

        $element = $this->parseAttributes($layoutElement);
        $element['type'] = self::TYPE;

        if (isset($element['handle'])) {
            $layoutInstanceId = $this->createLayoutInstance();
            $personalLayout = $this->getLayoutInstance($layoutInstanceId);

            $element['__layout'] = $layoutInstanceId;

            $layout->addElement($elementName, $element);

            // assign to parent element
            $this->assignToParentElement($element, $layout, $parentName);

            // load layout handle
            $xml = $this->loadLayoutHandle($element['handle']);

            // parse layout elements as children elements
            $this->parseChildren($xml, $personalLayout, $elementName);

            // parse regular children elements
            $this->parseChildren($layoutElement, $personalLayout, $elementName);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function register(array $element, LayoutInterface $layout)
    {
        if (isset($element['name']) && !isset($element['is_registered'])) {
            $elementName = $element['name'];

            $layout->updateElement($elementName, array('is_registered' => true));

            $personalLayout = $this->getLayoutInstance($element['__layout']);

            // register children
            $this->registerChildren($elementName, $personalLayout);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function render($elementName, LayoutInterface $layout)
    {
        $personalLayoutId = $layout->getElementProperty($elementName, '__layout');
        $personalLayout = $this->getLayoutInstance($personalLayoutId);
        $result = $this->renderChildren($elementName, $personalLayout);
        $render = $this->renderFactory->get(Html::TYPE_HTML);
        $containerInfo = $this->getContainerInfo($elementName, $layout);
        $result = $render->renderContainer($result, $containerInfo);

        return $result;
    }

    /**
     * @return int
     */
    protected function createLayoutInstance()
    {
        $layout = $this->layoutFactory->create();
        $this->inc++;
        $this->layouts[$this->inc] = $layout;
        return $this->inc;
    }

    /**
     * @param $id
     * @return LayoutInterface|null
     */
    protected function getLayoutInstance($id)
    {
        return isset($this->layouts[$id]) ? $this->layouts[$id] : null;
    }

    /**
     * @param $handle
     * @return Element
     */
    protected function loadLayoutHandle($handle)
    {
        /** @var $layoutProcessor ProcessorInterface */
        $layoutProcessor = $this->processorFactory->create();
        $layoutProcessor->load($handle);
        $xml = $layoutProcessor->asSimplexml();

        return $xml;
    }
}
