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



namespace Mirasvit\Assistant\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

abstract class AbstractApi extends Action
{
    protected $context;

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function _processUrlKeys(): bool
    {
        return true;
    }

    protected function successResponse(array $data, string $message = '')
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData(['success' => true, 'message' => $message, 'data' => $data]);

        return $response;
    }

    protected function errorResponse(string $message = '')
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData(['success' => false, 'message' => $message]);

        return $response;
    }

    protected function _isAllowed(): bool
    {
        return true;
    }
}
