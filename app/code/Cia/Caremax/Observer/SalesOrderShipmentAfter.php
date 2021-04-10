<?php

namespace Cia\Caremax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentAfter implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        // Do some things
			
		try {
			//$shipment = $observer->getShipment();
			$order = $shipment->getOrder();
			$tracks = $shipment->getTracksCollection();
			$firstTrack = $tracks->getFirstItem();
			
			if(!$order->getData('ext_order_id') || $order->getStoreId() != 5 || $tracks->count() == 0) {
				return;
			}

			$lines = [];

			foreach($shipment->getItemsCollection() as $_item) {

				$trackingNumber = $firstTrack->getTrackNumber();

				if(is_object($trackingNumber)) {
					$convertedTracking = json_decode(json_encode($trackingNumber), true);
					if(is_array($convertedTracking)) {
						$trackingNumber = implode(" ", $convertedTracking);
					} else {
						$trackingNumber = $trackingNumber . "";
					}
				} else if(is_array($trackingNumber)) {
					$trackingNumber = implode(" ", $trackingNumber);
				}

			   	if(strlen($trackingNumber) > 15) {
					$code = 'AUP';
				} else {
					$code = 'FW';
				}

			    $lines[] = [
			        'OrderId' => $order->getData('ext_order_id'),
			        'Item' => 'CMX-' . $_item->getSku(),
			        'Quantity' => intval($_item->getQty()),
			        'DispatchDate' => date('Y-m-d'),
			        'Carrier' => $code,
			        'TrackingNumber' => $trackingNumber,
			        'SerialNumbers' => '',
			    ];

			}

			if(!count($lines)) {
				return;
			}

			$koganData = ['Lines' => $lines];
            $koganUrl = "https://dispatch.aws.kgn.io/api/dispatch/submit";
           // $koganUrl = "https://dispatch.aws.kgn.io/api/dispatch/validate";
	 
$ch = curl_init();

$access_token = 'QVVDTVg6ODhyTCUydVQ=';
curl_setopt($ch, CURLOPT_URL, $koganUrl); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($koganData));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $access_token));

$datae = curl_exec($ch);
 
     $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

 

 
			/*
			$curl = new Varien_Http_Adapter_Curl();
			$curl->setConfig(array(
			       'timeout'   => 15
			));
			
			$curl->write(
			    Zend_Http_Client::POST, 
			    $koganUrl, 
			    '1.0', 
			    array(
			        'Content-Type: application/json', 
			        'Authorization: Basic QVVDTVg6ODhyTCUydVQ='
			    ),
			    json_encode($koganData)
			);

			$koganResponse = $curl->read();
			if ($data === false) {
			   throw new Exception("Error Processing Request to Kogan API");
			}

			$data = Zend_Http_Response::extractBody($koganResponse);
	*/		
			$response = json_decode($datae, true);
 	 
			$errors = [];

			if(isset($response['IsValid']) && $response['IsValid'] === false) {
				foreach ($response['ErrorsList'] as $key => $error) {
					$errors[] = $error['message'];
				}
			}

			if(count($errors)) {
			    file_put_contents('kogan.log', "Error: 1\n" . implode("\n", $errors) . "\n", FILE_APPEND);
				//throw new Exception("Error from Kogan API: " . implode("\n", $errors));
			}
		} catch(Exception $e) {
			file_put_contents('kogan.log', "Error 2:\n" . $e->getMessage() . "\n", FILE_APPEND);
		}
		

		return;

    }
}