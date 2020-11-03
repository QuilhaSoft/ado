<?php
/*
 * classe TSqlDelete
 * Esta classe provê meios para manipulação de uma instrução de DELETE no banco de dados
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
**/
namespace quilhasoft\ado;

final class TSqlDelete extends TSqlInstruction
{
    /*
     * método getInstruction()
     * retorna a instrução de DELETE em forma de string.
     */
    public function getInstruction()
    {
        // monta a string de DELETE
        $this->sql = "DELETE FROM {$this->entity}";
        
        // retorna a cláusula WHERE do objeto $this->criteria
        if ($this->criteria)
        {
            $expression = $this->criteria->dump();
            if ($expression)
            {
                $this->sql .= ' WHERE ' . $expression;
            }
        }
        return $this->sql;
    }
}
?>