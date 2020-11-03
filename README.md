# QuilhaSoft/ADO
Library to manage database in OOP format, using active record techniques<br>

Please, consider donating funds to support us
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EE7CD4UZEL3A4&source=url)

## Using composer

Add "quilhasoft/ado":"dev-master" into your composer config file and update/install

## basic usage

```php
<?php
namespace quilhasoft;

include '../../../autoload.php'; //load a composer default autoload
include 'autoloader.php';

use quilhasoft\ado;
use model;

ado\TTransaction::open('dev');
$repository = new ado\TRepository('model\Company');
// Company is class name of table, locate on samples/model folder; change tablename into Company.php file to use this sample
// Create a criteria and set filter by company active
$criteria = new ado\TCriteria;

$criteria->add(new ado\TFilter('companyActive', '=', 'Y'));

$criteria->setProperty('order', 'companyName');

// load data into database and charge into array of objects

$companies = $repository->load($criteria);
$items = array();
foreach ($companies as $object) {
    $items[$object->companyID] = $object->companyName;
}
var_dump($companies);

//or a direct load whit a array of  primary keys

$company = new Company(array('companyID' => '1'));
var_dump($company);
?>

```

## License

* MIT License
