<?php
/**
 * Copyright © 2015 Sundial. All rights reserved.
 */
namespace Sundial\ERP\Model\ResourceModel;

/**
 * ERP resource
 */
class Status extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('erp_order_status', 'id');
    }

  
}
