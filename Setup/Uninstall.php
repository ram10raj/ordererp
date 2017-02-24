<?php
namespace Sundial\ERP\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Uninstall Class
 */
class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{
    
    protected $logger;
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {       
        /**
         * Remove tables
         */
        try {
            $setup->getConnection()->dropTable(
                $setup->getTable('erp_order_status')
            );
        } catch (\Exception $e) {
            $this->logger->info((string)$e);
        }
		$setup->endSetup();
    }
}
