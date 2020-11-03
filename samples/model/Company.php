<?php
/*
* classe Company
* Active Record of table Empresa
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
* @version  2015.03.11
* @access   restrict
* 
* 2020.11.03 -- create
**/
namespace model;
use quilhasoft\ado;


class Company extends ado\TRecord
{
    const TABLENAME = 'empresas';
}
?>