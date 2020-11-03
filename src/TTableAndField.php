<?php
/*
 * classe TTableAndField(
 * Esta classe provê uma interface para definição de filtros de seleção
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
**/
namespace quilhasoft\ado;

class TTableAndField extends TExpression
{
	private $table; // variável
	private $field; // operador
	
	/*
	 * método __construct()
	 * instancia um novo filtro
	 * @param $variable = variável
	 * @param $operator = operador (>,<)
	 * @param $value      = valor a ser comparado
	 */
	public function __construct($table, $field)
	{
		// armazena as propriedades
		$this->table = $table;
		$this->field = $field;
		
	}
	/*
	 * método dump()
	 * retorna o filtro em forma de expressão
	 */
	public function dump()
	{
		// concatena a expressão
		return "{$this->table}.{$this->field}";
	}
}
?>