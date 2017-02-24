<?php
namespace Sundial\ERP\Api;
interface GetorderInterface
{
	 /**
     * @param string $orderId of the param.
     * @return mixed|string of the param Value.
     */
    public function getOrderDetail($orderId);
}