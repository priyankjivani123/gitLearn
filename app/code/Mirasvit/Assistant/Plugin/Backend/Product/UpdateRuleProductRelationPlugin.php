<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-assistant
 * @version   1.3.11
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Assistant\Plugin\Backend\Product;


use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Assistant\Repository\RuleRepository;
use Mirasvit\Assistant\Service\ApplyRuleService;


class UpdateRuleProductRelationPlugin
{
    private $ruleService;

    public function __construct(ApplyRuleService $ruleService)
    {
        $this->ruleService = $ruleService;
    }

    public function afterSave(ProductResource $subject, ProductResource $result, AbstractModel $product): ProductResource
    {
        $productId = $product->getId();

        if (!$productId) {
            return $result;
        }

        $this->ruleService->updateRuleProductRelation([$productId]);

        return $result;
    }

    public function beforeDelete(ProductResource $subject, AbstractModel $product): array
    {
        $productId = $product->getId();

        if (!$productId) {
            return [$product];
        }

        $this->ruleService->updateRuleProductRelation([$productId], true);

        return [$product];
    }
}
