<?php
/**
 * MagedIn Technology
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  MagedIn
 * @copyright Copyright (c) 2022 MagedIn Technology.
 *
 * @author    MagedIn Support <support@magedin.com>
 */

declare(strict_types=1);

namespace MagedIn\EnhancedMaintenanceMode\Model;

use Magento\Framework\App\MaintenanceMode as MaintenanceModeBase;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

class MaintenanceMode extends MaintenanceModeBase
{
    /**
     * @var FlagFilesProvider
     */
    private $flagFileProvider;

    /**
     * @var Manager|null
     */
    private $eventManager;

    /**
     * @param Filesystem $filesystem
     * @param FlagFilesProvider $flagFilesProvider
     * @param Manager|null $eventManager
     */
    public function __construct(
        Filesystem $filesystem,
        FlagFilesProvider $flagFilesProvider,
        Manager $eventManager = null
    ) {
        parent::__construct($filesystem, $eventManager);
        $this->flagFileProvider = $flagFilesProvider;
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(Manager::class);
    }

    /**
     * @param MaintenanceModeBase $subject
     * @param string $remoteAddr
     * @return bool
     */
    public function assertScopedMaintenanceMode(MaintenanceModeBase $subject, string $remoteAddr = ''): bool
    {
        if ($this->isAdmin()) {
            return false;
        }
        if (!$this->checkFlagFilenames()) {
            return false;
        }
        $info = $subject->getAddressInfo();
        return !in_array($remoteAddr, $info);
    }

    /**
     * @param bool $isOn
     * @return bool
     * @throws FileSystemException
     */
    public function set($isOn)
    {
        $this->eventManager->dispatch('maintenance_mode_changed', ['isOn' => $isOn]);
        $flagFile = $this->flagFileProvider->getScopeFlagFile();

        if ($isOn) {
            return $this->flagDir->touch($flagFile);
        }
        if ($this->flagDir->isExist($flagFile)) {
            return $this->flagDir->delete($flagFile);
        }
        return true;
    }

    /**
     * @return bool
     */
    private function checkFlagFilenames(): bool
    {
        foreach ($this->flagFileProvider->getFlagFiles() as $file) {
            if (!$this->flagDir->isExist($file)) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * This may be used for future implementation.
     * @return bool
     */
    private function isAdmin(): bool
    {
        return false;
    }
}
