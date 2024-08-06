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


use Mirasvit\Assistant\Api\Data\LogInterface;
use Mirasvit\Assistant\Api\Data\RuleInterface;
use Mirasvit\Assistant\Model\ConfigProvider;
use Mirasvit\Assistant\Repository\LogRepository;

class LoggerService
{
    private $configProvider;

    private $logRepository;

    private $identifier;

    public function __construct(
        ConfigProvider $configProvider,
        LogRepository $logRepository
    ) {
        $this->configProvider = $configProvider;
        $this->logRepository  = $logRepository;
        $this->identifier     = microtime(true);
    }

    public function log(
        int $ruleId,
        string $message = '',
        string $type = LogInterface::TYPE_INFO,
        array $additionalData = []
    ) {
        if (!$this->configProvider->isLoggingEnabled()) {
            return;
        }

        $log = $this->logRepository->create();

        $log->setRuleId($ruleId)
            ->setMessage($message)
            ->setType($type)
            ->setAdditionalData($additionalData)
            ->setIdentifier((string)$this->identifier);

        $this->logRepository->save($log);
    }

    public function info(int $ruleId, string $message, array $additionalData = [])
    {
        $this->log(
            $ruleId,
            $message,
            LogInterface::TYPE_INFO,
            $additionalData
        );
    }

    public function error(int $ruleId, string $message, array $additionalData = [])
    {
        $this->log(
            $ruleId,
            $message,
            LogInterface::TYPE_ERROR,
            $additionalData
        );
    }

    public function warning(int $ruleId, string $message, array $additionalData = [])
    {
        $this->log(
            $ruleId,
            $message,
            LogInterface::TYPE_WARNING,
            $additionalData
        );
    }
}
