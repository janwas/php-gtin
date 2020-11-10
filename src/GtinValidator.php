<?php

 /* (Tabs = 2)
 *
 * Based on BarcodeValidator.php:
 * link      			http://www.phpclasses.org/package/8560-PHP-Detect-type-and-check-EAN-and-UPC-barcodes.html
 * author       	Ferry Bouwhuis
 * version      	1.0.1
 * lastchange   	2014-04-13
 *
 * Adapted as GtinValidator.php:
 * author       	Jan Wassenaar
 * version      	1.0
 * lastchange   	2020-11-09
 *
 * released under GNU GPL
 *
 */

class GtinValidator {

	//  variables
	private $gtinWrk;

	//  constants
	const TYPE_EAN8 = 'EAN-8';
	const TYPE_EAN13 = 'EAN-13';
	const TYPE_UPC = 'UPC';
	const TYPE_GTIN14 = 'GTIN-14';

	const SUBTYPE_COMPANY = 'RESTRICTED_COMPANY';
	const SUBTYPE_COUPON = 'COUPON';
	const SUBTYPE_GID = 'GID';
	const SUBTYPE_ISBN = 'ISBN';
	const SUBTYPE_ISSN = 'ISSN';
	const SUBTYPE_REGION = 'RESTRICTED_REGION';
	
	const INVALID_CHECKDIGIT = 'ERROR_CHECKDIGIT';
	const INVALID_FUTURE = 'ERROR_FUTURE';
	const INVALID_LENGTH = 'ERROR_LENGTH';
	const INVALID_CHARACTER = 'ERROR_CHARACTER';

	function __construct($gtin) {

		//  initialize
    $this->valid = 0;
    $this->gtin = $gtin;

		//  remove whitespace
		$gtinNozero = trim($gtin);

		if (preg_match('/[^0-9]/', $gtinNozero)) {

			//  only numbers allowed
			$this->subtype = self::INVALID_CHARACTER;
      return;
		}

		//  remove zeroes
		$length = strlen($gtinNozero);
		for ($i = 0; $i < $length; $i++) {
			if (substr($gtinNozero, 0, 1) == '0') {
				$gtinNozero = substr($gtinNozero, 1);
			} else {
				break;
			}
		}
		$length = strlen($gtinNozero);

		if ($length < 5 || $length > 14) {

			//  length not ok
			$this->subtype = self::INVALID_LENGTH;
			return;
		}

		//  make gtin-14 for check digit calculation
		$this->gtin14 = $gtinNozero;
		for ($i = 0; strlen($this->gtin14) < 14; $i++) {
		 	$this->gtin14 = '0' . $this->gtin14;
		}

		//  check the check digit
		if ($this->checkDigitValid()) {
			$this->valid = TRUE;
		} else {
			return;
		}
		
		$this->gtin = $gtinNozero;

		switch ($length) {

			case 5:
			case 6:
			case 7:
			case 8:
				//  EAN-8:
				$this->type = self::TYPE_EAN8;
				for ($i = 0; strlen($this->gtin) < 8; $i++) {
				 	$this->gtin = '0' . $this->gtin;
				}
				if (substr($this->gtin, 0, 3) >= '977' && substr($this->gtin, 0, 3) <= '999') {
					$this->subtype = self::INVALID_FUTURE;
					$this->valid = 0;
				} else if (substr($this->gtin, 0, 1) == '2') {
					$this->subtype = self::SUBTYPE_REGION;
				} else if (substr($this->gtin, 0, 1) == '0') {
						$this->subtype = self::SUBTYPE_COMPANY;
				}
				break;
			
			case 13:
				//  EAN-13:
				$this->type = self::TYPE_EAN13;
				if (substr($this->gtin, 0, 1) == '2') {
					$this->subtype = self::SUBTYPE_REGION;
				} else if (substr($this->gtin, 0, 3) == '951') {
					$this->subtype = self::SUBTYPE_GID;
				} else if (substr($this->gtin, 0, 3) == '977') {
					$this->subtype = self::SUBTYPE_ISSN;
				} else if (substr($this->gtin, 0, 3) == '978' || substr($this->gtin, 0, 3) == '979') {
					$this->subtype = self::SUBTYPE_ISBN;
				} else if (substr($this->gtin, 0, 3) == '981' || substr($this->gtin, 0, 3) == '982' || substr($this->gtin, 0, 3) == '983' || substr($this->gtin, 0, 3) == '984' || substr($this->gtin, 0, 2) == '99') {
					$this->subtype = self::SUBTYPE_COUPON;
				} else if (substr($this->gtin, 0, 3) == '985' || substr($this->gtin, 0, 3) == '986' || substr($this->gtin, 0, 3) == '987' || substr($this->gtin, 0, 3) == '988' || substr($this->gtin, 0, 2) == '989') {
					$this->subtype = self::INVALID_FUTURE;
					$this->valid = 0;
				}
				break;

			case 14:
				//  GTIN-14:
				$this->type = self::TYPE_GTIN14;
				if (substr($this->gtin, 1, 1) == '2') {
					$this->subtype = self::SUBTYPE_REGION;
				}
				break;

			default:
				//  UPC
				$this->type = self::TYPE_UPC;
				for ($i = 0; strlen($this->gtin) < 12; $i++) {
				 	$this->gtin = '0' . $this->gtin;
				}
				if (substr($this->gtin, 0, 1) == '4') {
					$this->subtype = self::SUBTYPE_COMPANY;
				} else if (substr($this->gtin, 0, 1) == '5') {
					$this->subtype = self::INVALID_FUTURE;
					$this->valid = 0;
				} else if (substr($this->gtin, 0, 1) == '2') {
					$this->subtype = self::SUBTYPE_REGION;
				}
		}

		return;

	}

	public function getGtin(){
		return $this->gtin;
	}

	public function getSubType(){
		return $this->subtype;
	}

	public function getType(){
		return $this->type;
	}

	public function isValid(){
		return $this->valid;
	}


	private function checkDigitValid() {
		
		$calculation = 0;
	
		for ($i = 0; $i < (strlen($this->gtin14) - 1); $i++) {
			$calculation += $i % 2 ? $this->gtin14[$i] * 1 : $this->gtin14[$i] * 3;
		}

		if (substr(10 - (substr($calculation, -1)), -1) != substr($this->gtin14, -1)) {
			$this->subtype = self::INVALID_CHECKDIGIT;
			return 0;
		} else {
			return 1;
		}
	}
}
