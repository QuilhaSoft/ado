<?php
/*
 * classe TLoggerHTML
 * implementa o algoritmo de LOG em HTML
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
**/
namespace quilhasoft\ado;

class TLoggerDB extends TLogger
{
    /*
     * método write()
     * escreve uma mensagem no arquivo de LOG
     * @param $message = mensagem a ser escrita
     */
    public function write($message)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $time = date("Y-m-d H:i:s");
        
        $log  =  new Log_sistema();
        $log->log_data = date("Y-m-d");
        $log->log_hora = date("H:i:s");
        $log->log_usuario = TSession::getValue("usu_id");
        $log->log_message = $message;
        
        $log->insertNoLog();
    }
}
?>