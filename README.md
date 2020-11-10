# php-gtin
Validate and categorize a **GTIN** = Global Trade Item Number

A PHP class for:
- validating if the GTIN is valid 

- retrieving the type of the GTIN:
  - EAN-8
  - UPC
  - EAN-13
  - EAN-14

- retrieving the subtype of the GTIN:
  - COMPANY_RESTRICTED
  - COUPON
  - FUTURE (not valid)
  - GID
  - ISBN
  - ISSN
  - REGION_RESTRICTED
  
- returning the GTIN in the right format

The requirements are based on version 20.0 of the [GS1 General Specifications](https://www.gs1.org/docs/barcodes/GS1_General_Specifications.pdf), see paragraph 1.4.

# usage
~~~
<?php
  include_once('GtinValidator.php');
  
  echo('<html>');
  $code = '0417953007527';
  
  $gtin = new gtinValidator($code);
  echo 'valid:' . $gtin->isValid() . ', gtin:' . $gtin->getGtin() . ', type:' . $gtin->getType() . ', subtype:' . $gtin->getSubType();
  
  echo('</html>');
?>
~~~

# credits
- thanks to [Ferry Bouwhuis](http://www.phpclasses.org/package/8560-PHP-Detect-type-and-check-EAN-and-UPC-barcodes.html)
- thanks to [Luke Cousins](https://github.com/violuke/php-barcodes/blob/master/README.md)
