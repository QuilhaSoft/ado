<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace quilhasoft;

include '../../../autoload.php'; //load a composer default autoload
include 'autoloader.php';

use quilhasoft\ado;
use model;

ado\TTransaction::open('dev');

try {
    
    $company = new model/Company(array('companyID' => '1'));

    $company->name = 'new company name';

    $company->delete();
       
} catch (Exception $e) {            
    
    echo '<b>Erro</b>' . $e->getMessage();
    
    ado/TTransaction::rollback();
}

ado/TTransaction::close();