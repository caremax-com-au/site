<?php

namespace Olegnax\Athlete2\Model\Config\Settings\Header;

class testImageHeader implements \Magento\Framework\Option\ArrayInterface {

	protected $_assetRepo;

	public function __construct(
	\Magento\Framework\View\Asset\Repository $assetRepo
	) {
		$this->_assetRepo = $assetRepo;
	}

	public function toOptionArray() {
		$optionArray = [ ];
		$array		 = $this->toArray();
		foreach ( $array as $key => $value ) {
			$optionArray[] = [ 'value' => $key, 'label' => $value ];
		}

		return $optionArray;
	}

	public function toArray() {
		return [
			'panel'		 => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/55f081bac2028f763dbc67765bd82099.jpg' ),
			'overlay'	 => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/55f081bac2028f763dbc67765bd82099.jpg' ),
			'slideout'	 => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/55f081bac2028f763dbc67765bd82099.jpg' ),
		];
	}

}
