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

use MagedIn\EnhancedMaintenanceMode\Console\Command\ScopeProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class FlagFilesProvider
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeProvider
     */
    private $scopeProvider;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeProvider $scopeProvider
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeProvider $scopeProvider
    ) {
        $this->storeManager = $storeManager;
        $this->scopeProvider = $scopeProvider;
    }

    /**
     * @return array
     */
    public function getFlagFiles(): array
    {
        return array_filter([
            $this->getStoreFlagFile(),
            $this->getWebsiteFlagFile(),
        ]);
    }

    /**
     * @param string|null $code
     * @return string
     */
    private function buildFileName(string $code = null): ?string
    {
        if (empty($code)) {
            return null;
        }
        return ".maintenance.$code.flag";
    }

    /**
     * @return string
     */
    public function getWebsiteFlagFile(): string
    {
        return $this->buildFileName($this->getWebsiteFlagCode());
    }

    /**
     * @return string
     */
    private function getWebsiteFlagCode(): string
    {
        return 'website_' . $this->normalizeScopeCode($this->getWebsiteCode());
    }

    /**
     * @return string
     */
    public function getStoreFlagFile(): string
    {
        return $this->buildFileName($this->getStoreFlagCode());
    }

    /**
     * @return string
     */
    private function getStoreFlagCode(): string
    {
        return 'store_' . $this->normalizeScopeCode($this->getStoreCode());
    }

    /**
     * @return string
     */
    public function getScopeFlagFile(): string
    {
        if ($this->scopeProvider->isScopeStore()) {
            return $this->getStoreFlagFile();
        } else {
            return $this->getWebsiteFlagFile();
        }
    }

    /**
     * @param string $scopeCode
     * @return string
     */
    private function normalizeScopeCode(string $scopeCode): string
    {
        return strtolower(str_replace(' ', '_', $scopeCode));
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getWebsiteCode(): ?string
    {
        $websiteCode = $this->scopeProvider->getCode();
        if ($websiteCode) {
            return $websiteCode;
        }
        try {
            return $this->storeManager->getWebsite()->getCode();
        } catch (\DomainException $e) {
            return null;
        }
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getStoreCode(): ?string
    {
        $storeCode = $this->scopeProvider->getCode();
        if ($storeCode) {
            return $storeCode;
        }
        try {
            return $this->storeManager->getStore()->getCode();
        } catch (\DomainException $e) {
            return null;
        }
    }
}
