<?php
/*
 * classe TJoin
 * Esta classe provê uma interface para definição de Join de tabelas
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
**/

namespace quilhasoft\ado;

class TJoin extends TExpression
{
	private $variable; // variável
	private $operator; // operador
	private $value;    // valor
	private $table;    // valor
	private $expressions; // armazena a lista de expressões
	private $operators;     // armazena a lista de operadores
	
	/*
	 * método __construct()
	 * instancia um novo filtro
	 * @param $variable = variável
	 * @param $operator = operador (>,<)
	 * @param $value      = valor a ser comparado
	 */
	public function __construct($table,$variable, $operator, $value)
	{
		// armazena as propriedades
		$this->expressions = array();
		$this->operators = array();
		$this->table	= $table;
		$this->add(new TFilter($variable,$operator,$value));
		// transforma o valor de acordo com certas regras
		// antes de atribuir à propriedade $this->value
	}
		/*
	 * método add()
	 * adiciona uma expressão ao critério
	 * @param $expression = expressão (objeto TExpression)
	 * @param $operator     = operador lógico de comparação
	 */
	public function add(TExpression $expression, $operator = self::AND_OPERATOR)
	{
		// na primeira vez, não precisamos de operador lógico para concatenar
		if (empty($this->expressions))
		{
			$operator = NULL;
		}
		
		// agrega o resultado da expressão à lista de expressões
		$this->expressions[] = $expression;
		$this->operators[]    = $operator;
	}

	/*
	 * método dump()
	 * retorna o filtro em forma de expressão
	 */
	public function dump()
	{
		// concatena a expressão
		$result =  "{$this->table} ON  ";
				// concatena a lista de expressões
		if (is_array($this->expressions))
		{
			if (count($this->expressions) > 0)
			{
				//$result = '';
				foreach ($this->expressions as $i=> $expression)
				{
					$operator = $this->operators[$i];
					// concatena o operador com a respectiva expressão
					$result .= $operator.' '. $expression->dump() . ' ';
				}
				$result = trim($result);
				
			}
		}
		return " JOIN {$result}";
	}
}
?>