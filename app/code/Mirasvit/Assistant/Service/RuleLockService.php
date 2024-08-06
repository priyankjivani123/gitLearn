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


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class RuleLockService
{
    const FILENAME_TEMPLATE = '/mst_assistant_rule_%s.lock';

    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function lock(int $ruleId): string
    {
        $pointer = date('c');

        if ($this->isLocked($ruleId, $pointer)) {
            throw new \RuntimeException('This rule is currently processing or terminated incorrectly');
        }

        $file = fopen($this->getLockFile($ruleId), 'w');
        fwrite($file, $pointer);
        fclose($file);

        return $pointer;
    }

    public function unlock(int $ruleId)
    {
        $lockFile = $this->getLockFile($ruleId);

        if (is_file($lockFile)) {
            unlink($lockFile);
        }
    }

    public function isLocked(int $ruleId, string $pointer): bool
    {
        $lockFile = $this->getLockFile($ruleId);

        if (!is_file($lockFile)) {
            return false;
        }

        $lockPointer = file_get_contents($lockFile);

        if (trim($lockPointer) == $pointer) {
            return false; // lock created by current process
        }

        return true;
    }

    private function getLockFile(int $ruleId): string
    {
        $varDir = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();

        return $varDir . sprintf(self::FILENAME_TEMPLATE, $ruleId);
    }
}
