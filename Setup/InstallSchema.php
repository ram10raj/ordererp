<?php
/**
 * Copyright © 2015 Sundial. All rights reserved.
 */

namespace Sundial\ERP\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
	
        $installer = $setup;

        $installer->startSetup();

		/**
         * Create table 'erp_order_status'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('erp_order_status')
        )
		->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'erp_order_id'
        )
		->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'order_id'
        )
		->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'status'
        )		
		->addColumn(
            'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'message'
        )		
		/*{{CedAddTableColumn}}}*/
		
		
        ->setComment(
            'Sundial ERP Order Status Sundial_ERP'
        );
		$installer->getConnection()->createTable($table);
        $installer->endSetup();

    }
}
