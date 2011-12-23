<?php

namespace Orion\Core;


/**
 * \Orion\Core\Exception
 * 
 * Orion exception class.
 *
 * Extends standard Exception but with caller class handler
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
class Exception extends \Exception
{

    protected $classname;

    /**
     * Generates Exception
     * @param string $message
     * @param int $code
     * @param string $caller Caller class' name
     */
    public function __construct( $message='An exception occured', $code=256, $caller=null )
    {
        parent::__construct( ( string ) $message, ( int ) $code );
        $this->classname = $caller;
    }

    /**
     * Logs error to root error.log file
     */
    public function log()
    {
        $text = "\n" . date( '[d/m/Y@H:i:s]' ) . ' ' . $this->getCode() . ' : ' . $this->getMessage() . ' (line ' . $this->getLine() . ' in ' . $this->getFile() . ')';
        file_put_contents( 'error.log', $text, FILE_APPEND );
    }

    public function traceback()
    {
        debug_print_backtrace();
    }

    public function __toString()
    {
        switch ( $this->code )
        {
            case E_USER_ERROR :
                $type = 'Fatal error';
                break;

            case E_WARNING :
            case E_USER_WARNING :
                $type = 'Warning';
                break;

            case E_NOTICE :
            case E_USER_NOTICE :
                $type = 'Notice';
                break;

            default :
                $type = 'Unknown error';
                break;
        }

        return '<p><strong>' . $type . '</strong> : [' . $this->code . '] ' . $this->classname . ' : ' . $this->message . '</p>';
    }

}

?>
