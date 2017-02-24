<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sundial\ERP\Model\ResourceModel\Status;

/**
 * ERP Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
	protected $_idFieldName = 'id';
    public function _construct()
    {
        $this->_init('Sundial\ERP\Model\Status', 'Sundial\ERP\Model\ResourceModel\Status');
    }
}
