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

namespace MagedIn\EnhancedMaintenanceMode\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceEnableStoreCommand extends AbstractMaintenanceCommand
{
    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('maintenance:enable:store')
            ->setDescription('Enables maintenance mode for a specific store');
        parent::configure();
        $this->addArgument(
            $this->getArgumentName(),
            InputArgument::REQUIRED,
            'The code of the website to be put into maintenance mode.'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $store = $input->getArgument($this->getArgumentName());
        $this->scopeProvider->setScopeStore($store);
        return parent::execute($input, $output);
    }

    /**
     * Enable maintenance mode
     *
     * @return bool
     */
    protected function isEnable(): bool
    {
        return true;
    }

    /**
     * Get enabled maintenance mode display string
     *
     * @return string
     */
    protected function getDisplayString(): string
    {
        return "<info>Enabled maintenance mode for store '{$this->scopeProvider->getCode()}'</info>";
    }

    /**
     * @return string
     */
    protected function getArgumentName(): string
    {
        return 'store_code';
    }
}
