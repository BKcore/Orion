<?php
/**
 * Orion exception class.
 * Extends standard Exception but with caller class handler
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionException extends Exception
{
    protected $classname;

    /**
     * Generates OrionException
     * @param string $message
     * @param int $code
     * @param string $caller Caller class' name
     */
    public function  __construct($message, $code, $caller) {
        parent::__construct($message, $code);
        $this->classname = $caller;
    }
    
    public function __toString()
    {
        switch ($this->code)
        {
            case E_USER_ERROR :
                $type = 'Fatal error';
                break;

            case E_WARNING :
            case E_USER_WARNING :
                $type = 'Attention';
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
