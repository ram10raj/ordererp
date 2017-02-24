<?php
namespace Sundial\ERP\Cron;
class OrderUpdate
{	
	protected $scopeConfig;
	const XML_PATH_ERP_URL = 'erpurl/erpconfiguration/url';
    public function __construct(
        \Sundial\ERP\Model\Getorder $getOrder,
		\Sundial\ERP\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
		
    ) {
        $this->logger = $logger;
		$this->getOrder = $getOrder;
		$this->statusFactory = $statusCollectionFactory;
		$this->scopeConfig = $scopeConfig;
    }
    public function execute()
    {	
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$statusCollection = $this->statusFactory->create()
				->addFieldToFilter('status', array('neq' => 'completed' ));
		
		foreach($statusCollection as $status){
			if($status->getOrderId() !=''){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($status->getOrderId());
				$label = $order->getStatusLabel();
				if($label == 'Complete'){
					$statusCollection = $this->statusFactory->create()
							->addFieldToFilter('order_id', $status->getOrderId())
							->getFirstItem();
					
					$model = $objectManager->create('Sundial\ERP\Model\Status');	
						$orderXml = $this->getOrder->process($status->getOrderId());
						$this->logger->info($orderXml);
						$sysProUrl = $this->scopeConfig->getValue(self::XML_PATH_ERP_URL, $storeScope);						
						$xmlObjectForOrderData = simplexml_load_string($orderXml) or die("Error: Cannot create object");
						$options = array(
						'soap_version'=>SOAP_1_2,
						'exceptions'=>true,
						'trace'=>1,
						'cache_wsdl'=>WSDL_CACHE_NONE
						);
						$client = new \SoapClient($sysProUrl,$options);
						$data = $client->getOrder($xmlObjectForOrderData);
						$erpResponse = json_decode(json_encode($data), true);
						if(is_numeric($erpResponse['getOrderResult'])){
								$model->load($statusCollection->getId());
								$model->setStatus('completed');
								$model->setOrderId($status->getOrderId());
								$model->setMessage($erpResponse['getOrderResult']);
								$model->save();
						}else{
							$model->load($statusCollection->getId());
							$model->setStatus('failure');
							$model->setOrderId($status->getOrderId());
							$model->setMessage($erpResponse['getOrderResult']);
							$model->save();
						}
					}
				 }
			}
		
	    }
}
