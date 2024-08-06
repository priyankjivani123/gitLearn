<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ExtraContentInFotorama
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
declare(strict_types = 1);

namespace Priyank\BundleField\Block\Product\Renderer;

/**
 * Swatch renderer block
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    /**
     * Path to default template file with standard Configurable renderer.
     */
    public const CONFIGURABLE_RENDERER_TEMPLATE = 'Priyank_BundleField::product/view/type/options/configurable.phtml';
    public const SWATCH_RENDERER_TEMPLATE = 'Priyank_BundleField::product/view/renderer.phtml';


    /**
     * Return renderer template
     *
     * Template for product with swatches is different from product without swatches
     *
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute() ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }
}