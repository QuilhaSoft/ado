<?php
/*
* classe TRecord
* Esta classe provê os métodos necessários para persistir e
* recuperar objetos da base de dados (Active Record)
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
* @version  2015.03.10
* @access   restrict
* 
* 2015.03.10 -- criação
**/
namespace quilhasoft\ado;

abstract class TRecord
{
    protected $data; // array contendo os dados do objeto
    protected $meta;
    protected $autoIncrement = true;

    /* método __construct()
    * instancia um Active Record. Se passado o $id, já carrega o objeto
    * @param [$id] = ID do objeto
    */
    public function __construct($arrayKeys = NULL)
    {
        if ($arrayKeys) // se o ID for informado
        {
            // carrega o objeto correspondente
            $object = $this->load($arrayKeys);
            if ($object)
            {
                $this->fromArray($object->toArray());
            }
        }
    }


    /*
    * método __set()
    * executado sempre que uma propriedade for atribuída.
    */
    public function __set($prop, $value)
    {
        // verifica se existe método set_<propriedade>
        if (method_exists($this, 'set_'.$prop))
        {
            // executa o método set_<propriedade>
            call_user_func(array($this, 'set_'.$prop), $value);
        }
        else
        {

            if ($value === NULL)
            {
                unset($this->data[$prop]);
            }
            else
            {
                // atribui o valor da propriedade
                $this->data[$prop] = $value;
            }
        }
    }

    /*
    * método __get()
    * executado sempre que uma propriedade for requerida
    */
    public function __get($prop)
    {
        // verifica se existe método get_<propriedade>
        if (method_exists($this, 'get_'.$prop))
        {
            // executa o método get_<propriedade>
            return call_user_func(array($this, 'get_'.$prop));
        }
        else
        {
            // retorna o valor da propriedade
            if (isset($this->data[$prop]))
            {
                $metaData = (isset($this->meta[$prop]))?$this->meta[$prop]:null;
                switch ($metaData['native_type']) {
                    case 'NEWDECIMAL':
                        return ($this->data[$prop])?number_format($this->data[$prop],$metaData['precision'],',','.'):'';
                        break;
                    default:
                        return $this->data[$prop];
                        break;
                }

            }
        }
    }
    /*
    * método getUnformated()
    * busca o valor de dentro do campo dentro do objeto de forma simples sem formatação de valor por exemplo
    */
    public function getUnformated($prop)
    {
        // retorna o valor da propriedade
        if (isset($this->data[$prop]))
        {
            return $this->data[$prop];
        }
    }

    /*
    * método set_meta()
    * executado quando o objeto for clonado.
    * limpa o ID para que seja gerado um novo ID para o clone.
    */
    public function set_meta($meta)
    {
        $this->meta = $meta;
    }
    /*
    * método get_meta()
    * executado quando o objeto for clonado.
    * limpa o ID para que seja gerado um novo ID para o clone.
    */
    public function get_meta()
    {
        if($this->meta === NULL){
            $this->LoadMeta();
        }
        return $this->meta;
    }

    /*
    * método getEntity()
    * retorna o nome da entidade (tabela)
    */
    public function getEntity()
    {
        // obtém o nome da classe
        $class = get_class($this);

        // retorna a constante de classe TABLENAME
        return constant("{$class}::TABLENAME");
    }

    /*
    * método getParentEntity()
    * retorna o nome da entidade pai (tabela)
    */
    public function getParentEntity()
    {
        // obtém o nome da classe
        $class = get_class($this);
        if(defined("{$class}::PARENTTABLENAME")){
            return constant("{$class}::PARENTTABLENAME");
        }else{
            return false;
        }
        // retorna a constante de classe TABLENAME

    }
    /*
    * método getAutoJoin()
    * retorna o nome da entidade pai (tabela)
    */
    public function getAutoJoin()
    {
        // obtém o nome da classe
        $class = get_class($this);
        if(defined("{$class}::AUTOJOIN")){
            return constant("{$class}::AUTOJOIN");
        }else{
            return false;
        }
        // retorna a constante de classe TABLENAME

    }

    /*
    * método fromArray
    * preenche os dados do objeto com um array
    */
    public function fromArray($data)
    {
        $this->data = $data;
    }

    /*
    * método toArray
    * retorna os dados do objeto como array
    */
    public function toArray()
    {
        return $this->data;
    }

    /*
    * método load()
    * recupera (retorna) um objeto da base de dados
    * através de seu ID e instancia ele na memória
    * @param $id = ID do objeto
    */
    public function load($arraykeys)
    {
        // instancia instrução de SELECT
        $sql = new TSqlSelect;
        $sql->setEntity($this->getEntity());
        $sql->addColumn('*');

        // pega as chaves da tabela para montar a consulta
        $keys = $this->getKeys();
        $rowKeys = $keys->fetch();
        // cria critério de seleção baseado no ID
        $criteria = new TCriteria;
        if($this->getParentEntity()){
            $join     = new TJoin($this->getParentEntity(),new TTableAndField($this->getParentEntity(),$rowKeys['Column_name']),"=",new TTableAndField($this->getEntity(),$rowKeys['Column_name']));
            $criteria->setProperty('join',$join);
        }


        if($keys->rowCount()>0)
            do{
                $criteria->add(new TFilter(new TTableAndField($this->getEntity(),$rowKeys['Column_name']), '=', $arraykeys[$rowKeys['Column_name']]));
            }while($rowKeys = $keys->fetch());
        else{
            $criteria->add(new TFilter(new TTableAndField($this->getEntity(),$rowKeys['Column_name']), '=', $arraykeys));
        }  

        // define o critério de seleção de dados
        $sql->setCriteria($criteria);

        // obtém transação ativa
        if ($conn = TTransaction::get())
        {
            // cria mensagem de log e executa a consulta
            //TTransaction::setLogger( new TLoggerHTML("arquvio.html") );
            TTransaction::log($sql->getInstruction());
            $result= $conn->Query($sql->getInstruction());

            // se retornou algum dado
            if ($result)
            {
                // retorna os dados em forma de objeto
                $object = $result->fetchObject(get_class($this));
                foreach(range(0, $result->columnCount() - 1) as $columnIndex){
                    $metaData = $result->getColumnMeta($columnIndex) ;
                    $this->meta[$metaData['name']] = $metaData;
                    $columnIndex++; 
                }
            }
            return $object;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }

    /*
    * método __clone()
    * executado quando o objeto for clonado.
    * limpa o ID para que seja gerado um novo ID para o clone.
    */
    public function __clone()
    {
        unset($this->id);
    }


    /*
    * método LoadMeta()
    * recupera (retorna) um objeto da base de dados
    * através de seu ID e instancia ele na memória
    * @param $id = ID do objeto
    */
    public function LoadMeta()
    {
        // instancia instrução de SELECT
        $sql = new TSqlSelect;
        $sql->setEntity($this->getEntity());
        $sql->addColumn('*');
        $criteria = new TCriteria;
        // pega as chaves da tabela para montar a consulta
        //$keys = $this->getKeys();
        //$rowKeys = $keys->fetch();
        // cria critério de seleção baseado no ID
        //if($keys->rowCount()>0)
        //    do{
        //      
        //        $criteria->add(new TFilter($this->getEntity().".".$rowKeys['Column_name'], '=', $arraykeys[$rowKeys['Column_name']]));
        //    }while($keys->fetch());
        //else{
        //    $criteria = new TCriteria;
        //    $criteria->add(new TFilter($this->getEntity().".".$rowKeys['Column_name'], '=', $arraykeys));
        //}  

        if($this->getParentEntity()){
            $join     = new TJoin($this->getParentEntity(),$this->getParentEntity().".".$rowKeys['Column_name'],"=",$this->getEntity().".".$rowKeys['Column_name']);
            $criteria->setProperty('join',$join);
        }
        // define o critério de seleção de dados
        $sql->setCriteria($criteria);

        // obtém transação ativa
        if ($conn = TTransaction::get())
        {
            // cria mensagem de log e executa a consulta
            // TTransaction::setLogger( new TLoggerHTML("arquvio.html") );
            //TTransaction::log($sql->getInstruction());
            $result= $conn->Query($sql->getInstruction()." Limit 1 ");

            // se retornou algum dado
            if ($result)
            {
                // retorna os dados em forma de objeto
                $object = $result->fetchObject();
                foreach(range(0, $result->columnCount() - 1) as $columnIndex){
                    $metaData = $result->getColumnMeta($columnIndex) ;
                    $this->meta[$metaData['name']] = $metaData;
                    $columnIndex++; 
                }
            }
            return $object;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }

    /*
    * método insert()
    * armazena o objeto na base de dados e retorna
    * o número de linhas afetadas pela instrução SQL (zero ou um)
    */

    public function insert(){
        $columns = $this->getColumns();
        $rowColumns = $columns->fetch();
        // incrementa o ID
        $sql = new TSqlInsert;
        $sql->setEntity($this->getEntity());

        if($rowColumns['Extra']=='auto_increment' && $this->autoIncrement==true){
            $this->getLast($rowColumns['Field']);
            //@$this->data[$rowColumns['Field']] = $this->$rowColumns['Field'];
        }else{
            if(array_key_exists($rowColumns['Field'],$this->data)){
                $sql->setRowData($rowColumns['Field'], $this->data[$rowColumns['Field']]);
            }        
        }

        // cria uma instrução de insert
        // percorre as colunas da tabela e insere o valor do objeto data que veio do form
        do 
        {
            // passa os dados do objeto para o SQL
            if(array_key_exists($rowColumns['Field'],$this->data)){
                $sql->setRowData($rowColumns['Field'], $this->data[$rowColumns['Field']]);
            }
        }while($rowColumns = $columns->fetch());
        // obtém transação ativa
        if ($conn = TTransaction::get())

        {
            // faz o log e executa o SQL
            TTransaction::log($sql->getInstruction(),"DB");
            $result = $conn->exec($sql->getInstruction()); 
            // retorna o resultado
            return array('affectedRows'=>$result,'lastInsertId'=>$conn->lastInsertId());
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    /*
    * método insertNoLog()
    * armazena o objeto na base de dados e retorna
    * o número de linhas afetadas pela instrução SQL (zero ou um)
    */

    public function insertNoLog(){
        $columns = $this->getColumns();
        $rowColumns = $columns->fetch();
        // incrementa o ID
        $sql = new TSqlInsert;
        $sql->setEntity($this->getEntity());

        if($rowColumns['Extra']=='auto_increment' && $this->autoIncrement==true){

           // @$this->data[$rowColumns['Field']] =  $this->getLast($rowColumns['Field']);
        }else{
            if(array_key_exists($rowColumns['Field'],$this->data)){
                $sql->setRowData($rowColumns['Field'], $this->data[$rowColumns['Field']]);
            }else{
                $sql->setRowData($rowColumns['Field'], NULL);
            }		
        }

        // cria uma instrução de insert
        // percorre as colunas da tabela e insere o valor do objeto data que veio do form
        do 
        {
            // passa os dados do objeto para o SQL
            if(array_key_exists($rowColumns['Field'],$this->data)){
                $sql->setRowData($rowColumns['Field'], $this->data[$rowColumns['Field']]);
            }else{
                $sql->setRowData($rowColumns['Field'], NULL);
            }
        }while($rowColumns = $columns->fetch());
        // obtém transação ativa
        if ($conn = TTransaction::get())

        {
            //TTransaction::setLogger( new TLoggerHTML('arquvio.html') );
            // faz o log e executa o SQL
            //TTransaction::log($sql->getInstruction());
            $result = $conn->exec($sql->getInstruction()); 
            // retorna o resultado
            return array('affectedRows'=>$result,'lastInsertId'=>$conn->lastInsertId());
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }

    /*
    * método update()
    * armazena o objeto na base de dados e retorna
    * o número de linhas afetadas pela instrução SQL (zero ou um)
    */

    public function update(){

        $columns = $this->getColumns();
        $rowColumns = $columns->fetch();
        $sql = new TSqlUpdate;
        $sql->setEntity($this->getEntity());
        // pega as chaves da tabela para montar a consulta
        $keys = $this->getKeys();
        $rowKeys = $keys->fetch();
        // cria critério de seleção baseado no ID
        $criteria = new TCriteria;
        do{  
            $criteria->add(new TFilter($rowKeys['Column_name'], '=', $this->data[$rowKeys['Column_name']]));
        }while($rowKeys = $keys->fetch());
        $rowKeys = $keys->fetch(0);

        // define o critério de seleção de dados
        $sql->setCriteria($criteria);
        // percorre os dados do objeto
        do 
        {
            // passa os dados do objeto para o SQL
            if(array_key_exists($rowColumns['Field'],$this->data)){
                @$sql->setRowData($rowColumns['Field'], $this->data[$rowColumns['Field']]);
            }else{
                @$sql->setRowData($rowColumns['Field'],NULL);
            }
        }while($rowColumns = $columns->fetch());
        // obtém transação ativa
        if ($conn = TTransaction::get())

        {
            // faz o log e executa o SQL
            TTransaction::log($sql->getInstruction(),"DB");
            $result = $conn->exec($sql->getInstruction());
            // retorna o resultado
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }

    /*
    * método insertOrUpdate()
    * armazena o objeto na base de dados e retorna
    * o número de linhas afetadas pela instrução SQL (zero ou um)
    */
    public function insertOrUpdate()
    {
        // verifica se tem ID ou se existe na base de dados
        // pega as chaves da tabela para montar a consulta
        $columns = $this->getColumns();
        $rowColumns = $columns->fetch();
        if (empty($this->data[$rowColumns['Field']]) or (!$this->load($this->data)))
        {
            // incrementa o ID
            if (empty($this->data[$rowColumns['Field']]))
            {
                if($rowColumns['Extra']=='auto_increment' && $this->autoIncrement==true){
                    $this->getLast($rowColumns['Field']);
                    $this->data[$rowColumns['Field']] = $this->$rowColumns['Field'];
                }else{
                    $this->data[$rowColumns['Field']] = $this->data[$rowColumns['Field']];
                }
            }
            // cria uma instrução de insert
            $sql = new TSqlInsert;
            $sql->setEntity($this->getEntity());
            // percorre os dados do objeto
            do 
            {
                // passa os dados do objeto para o SQL
                if(array_key_exists($rowColumns['Field'],$this->data)){
                    $sql->setRowData($rowColumns['Field'], $this->data[$rowColumns['Field']]);
                }else{
                    $sql->setRowData($rowColumns['Field'],NULL);
                }
            }while($rowColumns = $columns->fetch());
        }
        else
        {
            // instancia instrução de update
            $sql = new TSqlUpdate;
            $sql->setEntity($this->getEntity());
            // pega as chaves da tabela para montar a consulta
            $keys = $this->getKeys();
            $rowKeys = $keys->fetch();
            // cria critério de seleção baseado no ID
            $criteria = new TCriteria;
            do{
                $criteria->add(new TFilter($rowKeys['Column_name'], '=', $this->data[$rowKeys['Column_name']]));
            }while($rowKeys = $keys->fetch());
            $rowKeys = $keys->fetch(0);

            // define o critério de seleção de dados
            $sql->setCriteria($criteria);
            // percorre os dados do objeto
            do 
            {
                // passa os dados do objeto para o SQL
                if(array_key_exists($rowColumns['Field'],$this->data)){
                    $sql->setRowData($rowColumns['Field'], $this->data[$rowColumns['Field']]);
                }else{
                    $sql->setRowData($rowColumns['Field'], NULL);
                }
            }while($rowColumns = $columns->fetch());
        }
        // obtém transação ativa
        if ($conn = TTransaction::get())

        {
            // faz o log e executa o SQL
            TTransaction::log($sql->getInstruction(),"DB");
            $result = $conn->exec($sql->getInstruction());
            // retorna o resultado
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
    * exclui um objeto da base de dados através de seu ID.
    * @param $id = ID do objeto
    */
    public function delete($arraykeys = NULL)
    {
        // instancia uma instrução de DELETE
        $sql = new TSqlDelete;
        $sql->setEntity($this->getEntity());
        // pega as chaves da tabela para montar a consulta
        $keys = $this->getKeys();
        $rowKeys = $keys->fetch();
        // cria critério de seleção baseado no ID
        $criteria = new TCriteria;
        do{
            // o ID é o parâmetro ou a propriedade ID
            //var_dump($this->data);
            $Column_value = ($arraykeys) ? $arraykeys[$rowKeys['Column_name']] : $this->data[$rowKeys['Column_name']];


            $criteria->add(new TFilter($rowKeys['Column_name'], '=', $Column_value));
        }while($rowKeys = $keys->fetch());
        // define o critério de seleção de dados
        $sql->setCriteria($criteria);

        // obtém transação ativa
        if ($conn = TTransaction::get())
        {
            // faz o log e executa o SQL
            TTransaction::log($sql->getInstruction(),"DB");
            $result = $conn->exec($sql->getInstruction());
            // retorna o resultado
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
    * exclui um objeto da base de dados através de seu ID.
    * @param $id = ID do objeto
    */
    public function situacaoEXC($arraykeys = NULL)
    {
        $sql = new TSqlUpdate;
        $sql->setEntity($this->getEntity());
        // pega as chaves da tabela para montar a consulta
        $keys = $this->getKeys();
        $rowKeys = $keys->fetch();
        // cria critério de seleção baseado no ID
        $criteria = new TCriteria;
        do{
            $criteria->add(new TFilter($rowKeys['Column_name'], '=', $this->data[$rowKeys['Column_name']]));
        }while($rowKeys = $keys->fetch());
        $rowKeys = $keys->fetch(0);

        // define o critério de seleção de dados
        $sql->setCriteria($criteria);
        // percorre os dados do objeto
        $sql->setRowData('situacao', 'EXC');
        // obtém transação ativa
        if ($conn = TTransaction::get())

        {
            // faz o log e executa o SQL
            TTransaction::log($sql->getInstruction(),"DB");
            $result = $conn->exec($sql->getInstruction());
            // retorna o resultado
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    /*
    * método situacaoNOR()
    * devolve o status de registro normal a um objeto qualquer.
    * @param $id = ID do objeto
    */
    public function situacaoNOR($arraykeys = NULL)
    {
        $sql = new TSqlUpdate;
        $sql->setEntity($this->getEntity());
        // pega as chaves da tabela para montar a consulta
        $keys = $this->getKeys();
        $rowKeys = $keys->fetch();
        // cria critério de seleção baseado no ID
        $criteria = new TCriteria;
        do{
            $criteria->add(new TFilter($rowKeys['Column_name'], '=', $this->data[$rowKeys['Column_name']]));
        }while($rowKeys = $keys->fetch());
        $rowKeys = $keys->fetch(0);

        // define o critério de seleção de dados
        $sql->setCriteria($criteria);
        // percorre os dados do objeto
        $sql->setRowData('situacao', 'NOR');
        // obtém transação ativa
        if ($conn = TTransaction::get())

        {
            // faz o log e executa o SQL
            // TTransaction::setLogger( new TLoggerDB() );
            TTransaction::log($sql->getInstruction(),"DB");
            $result = $conn->exec($sql->getInstruction());
            // retorna o resultado
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }

    /*
    * método getLast()
    * retorna o último ID
    */
    public function getLast($id_collumn = null)
    {
        // inicia transação
        if ($conn = TTransaction::get())
        {
            $sql = new TSqlShow;
            $sql->addColumn('COLUMNS');
            $sql->setEntity($this->getEntity());

            // cria critério de seleção de dados
            $criteria = new TCriteria;
            $criteria->add(new TFilter('Extra' ,'=' ,'auto_increment'));
            $sql->setCriteria($criteria);
            //echo $sql->getInstruction();
            TTransaction::setLogger( new TLoggerHTML('arquvio.html') );
            // cria log e executa instrução SQL
            //TTransaction::log($sql->getInstruction());

            $result= $conn->Query($sql->getInstruction());
            $rowkeys = $result->fetch();

            $maxField = ($id_collumn)?$id_collumn:$rowkeys['Field'];
            // instancia instrução de SELECT
            $sql = new TSqlSelect;
            $sql->addColumn('max('.$maxField.')+1 as '.$maxField);
            $sql->setEntity($this->getEntity());

            // cria log e executa instrução SQL
            //TTransaction::log($sql->getInstruction());
            $result= $conn->Query($sql->getInstruction());
            // retorna os dados do banco
            if ($result)
            {
                // retorna os dados em forma de objeto
                $object = $result->fetchObject(get_class($this));
                $this->$maxField =($object)?$object->$maxField:1;
            } else{
                $this->$maxField = 1;
            }
            return $this->$maxField;

        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    public function getKeys(){

        // inicia transação
        if ($conn = TTransaction::get())
        {
            // instancia instrução de SELECT
            $sql = new TSqlShow;
            $sql->addColumn('KEYS');
            $sql->setEntity($this->getEntity());

            // cria critério de seleção de dados
            $criteria = new TCriteria;
            $criteria->add(new TFilter('Key_name' ,'=' ,'PRIMARY'));
            $sql->setCriteria($criteria);

            // cria log e executa instrução SQL
            //TTransaction::log($sql->getInstruction());

            $result= $conn->Query($sql->getInstruction());

            // retorna os dados do banco
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    protected function getColumns(){

        // inicia transação
        if ($conn = TTransaction::get())
        {
            // instancia instrução de SELECT
            $sql = new TSqlShow;
            $sql->addColumn('COLUMNS');
            $sql->setEntity($this->getEntity());

            // cria critério de seleção de dados
            //$criteria = new TCriteria;
            //$criteria->add(new TFilter('Key_name' ,'=' ,'PRIMARY'));
            //$sql->setCriteria($criteria);
            // TTransaction::setLogger( new TLoggerHTML('arquvio.html') );
            // cria log e executa instrução SQL
            // TTransaction::log($sql->getInstruction());

            $result= $conn->Query($sql->getInstruction());

            // retorna os dados do banco
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
}
?>