<?php

namespace Olegnax\Athlete2\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized as BackendSerialized;

class Serialized extends BackendSerialized {

	/**
	 * @return $this
	 */
	public function beforeSave() {
		if ( is_array( $this->getValue() ) ) {
			$value = $this->getValue();
			if ( array_key_exists( '__empty', $value ) ) {
				unset( $value[ '__empty' ] );
			}
			$this->setValue( $value );
		}
		parent::beforeSave();
		return $this;
	}

}
