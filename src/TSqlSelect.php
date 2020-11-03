<?php

/*
 * classe TSqlSelect
 * Esta classe provê meios para manipulação de uma instrução de SELECT no banco de dados
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
 * */
namespace quilhasoft\ado;

final class TSqlSelect extends TSqlInstruction {

    private $columns;     // array de colunas a serem retornadas.

    /*
     * método addColumn
     * adiciona uma coluna a ser retornada pelo SELECT
     * @param $column = coluna da tabela
     */

    public function addColumn($column) {
        // adiciona a coluna no array
        $this->columns[] = $column;
    }

    /*
     * método getInstruction()
     * retorna a instrução de SELECT em forma de string.
     */

    public function getInstruction() {
        // monta a instrução de SELECT
        $this->sql = 'SELECT ';

        // monta string com os nomes de colunas
        $this->sql .= implode(',', $this->columns);

        // adiciona na cláusula FROM o nome da tabela
        $this->sql .= ' FROM ' . $this->entity;
        // obtém a cláusula WHERE do objeto criteria.
        if ($this->criteria) {
            if ($this->criteria->getProperty('join')) {
                if (is_array($this->criteria->getProperty('join'))) {
                    $joins = $this->criteria->getProperty('join');
                    foreach ($joins as $join) {
                        $this->sql .= $join->dump();
                    }
                } else {
                    $this->sql .= $this->criteria->getProperty('join')->dump();
                }
            }
            if ($this->criteria->getProperty('leftOuterJoin')) {
                if (is_array($this->criteria->getProperty('leftOuterJoin'))) {
                    $joins = $this->criteria->getProperty('leftOuterJoin');
                    foreach ($joins as $join) {
                        $this->sql .= " LEFT OUTER " . $join->dump();
                    }
                } else {
                    $this->sql .= " LEFT OUTER " . $this->criteria->getProperty('leftOuterJoin')->dump();
                }
            }
            $expression = $this->criteria->dump();
            if ($expression) {
                $this->sql .= ' WHERE ' . $expression;
            }

            // obtém as propriedades do critério
            $order = $this->criteria->getProperty('order');
            $limit = $this->criteria->getProperty('limit');
            $group = $this->criteria->getProperty('group');
            $offset = $this->criteria->getProperty('offset');

            // obtém a ordenação do SELECT
            if ($group) {
                $this->sql .= ' GROUP BY ' . $group;
            }
            if ($order) {
                $this->sql .= ' ORDER BY ' . $order;
            }

            if ($limit) {
                $this->sql .= ' LIMIT ' . $limit;
            }
            if ($offset) {
                $this->sql .= ' OFFSET ' . $offset;
            }
        }
        return $this->sql;
    }

}

?>