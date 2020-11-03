<?php
/*
 * classe TYearSql(
 * Esta classe provê uma interface para definição de filtros de seleção
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
**/
namespace quilhasoft\ado;

class TYearSql extends TExpression
{
	private $field; // operador
	
	/*
	 * método __construct()
	 * instancia um novo filtro
	 * @param $variable = variável
	 * @param $operator = operador (>,<)
	 * @param $value      = valor a ser comparado
	 */
	public function __construct($field)
	{
		// armazena as propriedades
		$this->field =($field instanceof TTableAndField)?$field->dump():$field;
		
	}
	/*
	 * método dump()
	 * retorna o filtro em forma de expressão
	 */
	public function dump()
	{
		// concatena a expressão
		return "YEAR({$this->field})";
	}
}
?>