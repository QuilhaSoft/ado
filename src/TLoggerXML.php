<?php
/*
 * classe TLoggerXML
 * implementa o algoritmo de LOG em XML
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
**/
namespace quilhasoft\ado;

class TLoggerXML extends TLogger
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
        
        // monta a string
        $text = "<log>\n";
        $text.= "   <time>$time</time>\n";
        $text.= "   <message>$message</message>\n";
        $text.= "</log>\n";
        
        // adiciona ao final do arquivo
        $handler = fopen($this->filename, 'a');
        fwrite($handler, $text);
        fclose($handler);
    }
}
?>