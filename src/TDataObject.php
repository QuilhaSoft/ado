<?php
/*
 * classe TDataObject
 * simula um active record para tabelas imaginarias
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 * 
 * 2015.03.11 -- criaÃ§Ã£o
**/

namespace quilhasoft\ado;

abstract class TDataObject
{
	protected $data;
	protected $dataItens = array();
	
	public function __construct($arrayKeys = NULL)
	{
		if ($arrayKeys) // se o ID for informado
		{
			// carrega o objeto correspondente
			$array = $this->dataItens[$arrayKeys];
			if ($array)
			{
				$this->data($array);
			}
		}
   }
   public function get_items (){
		return $this->dataItens;
   }
    /*
     * mÃ©todo __get()
     * executado sempre que uma propriedade for requerida
     */
    public function __get($prop)
    {
        // verifica se existe mÃ©todo get_<propriedade>
        if (method_exists($this, 'get_'.$prop))
        {
            // executa o mÃ©todo get_<propriedade>
            return call_user_func(array($this, 'get_'.$prop));
        }
        else
        {
            // retorna o valor da propriedade
            if (isset($this->data[$prop]))
            {
                return $this->data[$prop];
            }
        }
    }
}
?>