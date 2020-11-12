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
TTransaction::setLogger(new ado / TLoggerHTML('arquvio.html'));
$repository = new ado / TRepository('Locacao');
$repository->enableCount = true;

$criteria = new ado / TCriteria;

$criteria->setProperty('order', 'locacoes_rescisao_data IS NOT NULL,locacoes.locacoes_codigo DESC,locacoes_rescisao_data DESC');

$criteria->setProperty('limit', "1,2");

$criteria->add(new ado / TFilter($key, 'like', "1%"), 'or');


// Create a subquery
$sqlFiadores = new ado/TSqlSelect;
$sqlFiadores->setEntity('fiadores');
$sqlFiadores->addColumn('locacoes_codigo');
$criteiraFiadores = new ado/TCriteria;
$criteiraFiadores->add(new ado/TFilter('fiadores.locacoes_codigo', '=', 'locacao.locacoes_codigo'));
$joinFiadores = new ado/TJoin('locacoes_fiadores', 'fiadores.fiadores_codigo', '=', new ado/TTableAndField('locacoes_fiadores', 'fiadores_codigo'));
$criteiraFiadores->setProperty('join', $joinFiadores);
$sqlFiadores->setCriteria($criteiraFiadores);

//use a subquery into a filter
$criteria->add(new ado/TFilter('locacoes.locacoes_codigo', 'in', $sqlFiadores));

$join[] = new ado/TJoin("imoveis", "imoveis.imoveis_codigo", "=", new ado/TTableAndField("locacoes", "imoveis_codigo"));
$join2 = new ado/TJoin("imoveis_proprietarios", "imoveis_proprietarios.imoveis_codigo", "=", new ado/TTableAndField("imoveis", "imoveis_codigo"));
$join2->add(new ado/TFilter("imoveis_proprietarios_principal", '=', 'S'));
$join[] = $join2;
$join3 = new ado/TJoin("proprietarios", "proprietarios.proprietarios_codigo", "=", new ado/TTableAndField("imoveis_proprietarios", "proprietarios_codigo"));
$join[] = $join3;
$join4 = new ado/TJoin("locacoes_locatarios", "locacoes_locatarios.locacoes_codigo", "=", new ado/TTableAndField("locacoes", "locacoes_codigo"));
$join4->add(new ado/TFilter("locacoes_locatarios_principal", '=', 'S'));
$join[] = $join4;
$join5 = new ado/TJoin("locatarios", "locatarios.locatarios_codigo", "=", new ado/TTableAndField("locacoes_locatarios", "locatarios_codigo"));
$join[] = $join5;
$criteria->setProperty('join', $join);


$criteria->add(new ado/TFilter('locacoes.situacao', '=', "NOR"), 'and');
// carreta os produtos que satisfazem o critério
$registros = $repository->load($criteria);

// end of transaction
TTransaction::close();