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

namespace Mirasvit\Assistant\Service;

use Mirasvit\Assistant\Service\ContextMaker\AbstractContext;

class ContextMakerService
{
    /** @var AbstractContext[]  */
    private $makers;

    public function __construct(
        array $makers
    ) {
        $this->makers = $makers;
    }

    public function context(): array
    {
        $data = [];

        foreach ($this->makers as $maker) {
            $makerContext = $maker->context();
            if ($makerContext) {
                foreach ($makerContext as $value) {
                    $data[] = $value;
                }
            }
        }

        $data[] = [
            'id'    => 'global.input',
            'label' => 'Input',
            'value' => '',
        ];

        return $data;
    }
}
