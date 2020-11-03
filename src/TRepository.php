<?php
/*
* classe TRepository
* esta classe provê os métodos necessários para manipular coleções de objetos.
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
* @version  2015.03.10
* @access   restrict
* 
* 2015.03.10 -- criação
**/
namespace quilhasoft\ado;

final class TRepository
{
    private $class; // nome da classe manipulada pelo repositório
    public $totalRows;
    private $fields;
    public $tableName;
    public $enableCount = false; // alteração para melhorar performance em certas operações

    /* método __construct()
    * instancia um Repositório de objetos
    * @param $class = Classe dos Objetos
    */
    function __construct($class)
    {
        $this->class = $class;
    }

    /*
    * método load()
    * Recuperar um conjunto de objetos (collection) da base de dados
    * através de um critério de seleção, e instanciá-los em memória
    * @param $criteria = objeto do tipo TCriteria
    */
    function setFields($string){
        $this->fields = $string;
    }
    /*
    * Cria os criterios com base nas chaves passadas pelo array
    * @array contendo as chaves = chaves nomeadas 
    */
    function autoCriteria($array){
        $obj = new $this->class();
        $keys = $obj->getKeys();
        $tableName = $obj->getEntity();
        $this->tableName = $tableName;
        $rowKeys = $keys->fetch();
        $criteria = new TCriteria;
        do{
            if(array_key_exists($rowKeys['Column_name'],$array)){
                $criteria->add(new TFilter(new TTableAndField($tableName,$rowKeys['Column_name']), '=', $array[$rowKeys['Column_name']]));
            }
        }while($rowKeys = $keys->fetch());
        if($obj->getAutoJoin()){
            $joins = $obj->getAutoJoin();
            foreach($joins as $j){
                $join[]     = new TJoin($j[0],$j[1],$j[2],$j[3]);
            }
            $criteria->setProperty('join',$join);
        }else if($obj->getParentEntity()){
            $keys = $obj->getKeys();
            $rowKeys = $keys->fetch();
            $join     = new TJoin($obj->getParentEntity(),$obj->getParentEntity().".".$rowKeys['Column_name'],"=",new TTableAndField($obj->getEntity(),$rowKeys['Column_name']));
            $criteria->setProperty('join',$join);
        }


        return $criteria;
    }
    /*
    * método load()
    * Recuperar um conjunto de objetos (collection) da base de dados
    * através de um critério de seleção, e instanciá-los em memória
    * @param $criteria = objeto do tipo TCriteria
    */
    function load(TCriteria $criteria)
    {
        // echo 'load';
        // instancia a instrução de SELECT
        $sql = new TSqlSelect;  
        if($this->fields!=''){
            $sql->addColumn($this->fields);
        }else{
            $sql->addColumn('*');
        }        
        $sql->setEntity(constant($this->class.'::TABLENAME'));

        // atribui o critério passado como parâmetro
        $sql->setCriteria($criteria);


        $sqlALl = new TSqlSelect;
        $sqlALl->addColumn('count(*) as total');
        $sqlALl->setEntity(constant($this->class.'::TABLENAME'));

        $criteriaALL = clone($criteria);
        $criteriaALL->setProperty('limit',null);
        $criteriaALL->setProperty('order','1');
        $sqlALl->setCriteria($criteriaALL);

        //$criteriaALL->
        // obtém transação ativa
        if ($conn = TTransaction::get())
        {
            // registra mensagem de log
            //TTransaction::setLogger( new TLoggerHTML("arquvio.html") );
            //TTransaction::log($sql->getInstruction());
            if($this->enableCount)
            {
                TTransaction::log($sqlALl->getInstruction());
                $countResult = $conn->Query($sqlALl->getInstruction());
                $countRow = $countResult->fetchObject();

                $this->totalRows =($countRow)?$countRow->total:0;
            }else{
                $this->totalRows = 0;
            }
            //echo $this->totalRows;
            // executa a consulta no banco de dados
            TTransaction::log($sql->getInstruction());
            $result  = $conn->Query($sql->getInstruction());
            $results = array();

            if ($result)
            {
                $meta = array();
                // percorre os resultados da consulta, retornando um objeto
                while ($row = $result->fetchObject($this->class))
                {
                    // armazena no array $results;
                    if(count($meta)===0){
                        foreach(range(0, $result->columnCount() - 1) as $columnIndex){
                            $metaData = $result->getColumnMeta($columnIndex) ;
                            $meta[$metaData['name']] = $metaData;
                            $columnIndex++; 
                        }
                    }
                    $row->meta = $meta;
                    $results[] = $row;
                }
            }
            return $results;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }

    /*
    * método delete()
    * Excluir um conjunto de objetos (collection) da base de dados
    * através de um critério de seleção.
    * @param $criteria = objeto do tipo TCriteria
    */
    function delete(TCriteria $criteria)
    {
        // instancia instrução de DELETE
        $sql = new TSqlDelete;
        $sql->setEntity(constant($this->class.'::TABLENAME'));

        // atribui o critério passado como parâmetro
        $sql->setCriteria($criteria);

        // obtém transação ativa
        if ($conn = TTransaction::get())
        {
            // registra mensagem de log
            //TTransaction::setLogger( new TLoggerDB() );
            TTransaction::log($sql->getInstruction(),"DB");
            // executa instrução de DELETE
            $result = $conn->exec($sql->getInstruction());
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');

        }
    }

    /*
    * método delete()
    * Excluir um conjunto de objetos (collection) da base de dados
    * através de um critério de seleção.
    * @param $criteria = objeto do tipo TCriteria
    */
    function situacaoEXC(TCriteria $criteria)
    {
        // instancia instrução de DELETE
        $sql = new TSqlUpdate;
        $sql->setEntity(constant($this->class.'::TABLENAME'));

        // atribui o critério passado como parâmetro
        $sql->setCriteria($criteria);
        $sql->setRowData('situacao', 'EXC');
        // obtém transação ativa
        if ($conn = TTransaction::get())
        {
            // registra mensagem de log
            TTransaction::log($sql->getInstruction(),"DB");
            // executa instrução de DELETE
            $result = $conn->exec($sql->getInstruction());
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');

        }
    }
    
    /*
    * método delete()
    * Excluir um conjunto de objetos (collection) da base de dados
    * através de um critério de seleção.
    * @param $criteria = objeto do tipo TCriteria
    */
    function situacaoNOR(TCriteria $criteria)
    {
        // instancia instrução de DELETE
        $sql = new TSqlUpdate;
        $sql->setEntity(constant($this->class.'::TABLENAME'));

        // atribui o critério passado como parâmetro
        $sql->setCriteria($criteria);
        $sql->setRowData('situacao', 'NOR');
        // obtém transação ativa
        if ($conn = TTransaction::get())
        {
            // registra mensagem de log
            TTransaction::log($sql->getInstruction(),"DB");
            // executa instrução de DELETE
            $result = $conn->exec($sql->getInstruction());
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');

        }
    }

    /*
    * método count()
    * Retorna a quantidade de objetos da base de dados
    * que satisfazem um determinado critério de seleção.
    * @param $criteria = objeto do tipo TCriteria
    */
    function count(TCriteria $criteria)
    {

        // instancia instrução de SELECT
        $sql = new TSqlSelect;
        $sql->addColumn('count(*)');
        $sql->setEntity(constant($this->class.'::TABLENAME'));

        // atribui o critério passado como parâmetro
        $sql->setCriteria($criteria);

        // obtém transação ativa
        if ($conn = TTransaction::get())
        {
            // registra mensagem de log
            //TTransaction::setLogger( new TLoggerHTML("arquvio.html") );
            TTransaction::log($sql->getInstruction());

            // executa instrução de SELECT
            $result= $conn->Query($sql->getInstruction());
            if ($result)
            {
                $row = $result->fetch();
            }
            // retorna o resultado
            return $row[0];
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
}
?>