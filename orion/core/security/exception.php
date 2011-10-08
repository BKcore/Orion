<?php

namespace Orion\Core\Security;

class Exception extends \Orion\Core\Exception
{

    /**
     * Generates a Query Exception
     * @param string $message
     * @param int $code
     * @param string $caller Caller class' name
     */
    public function __construct( $message='An exception occured', $code=1, $forceExit=false )
    {
        if( $forceExit )
        {
            die( 'Security : ' . \Orion\Core\Security::preventInjection( $message ) );
            exit(1);
        }
        parent::__construct( ( string ) $message, $code, 'Security' );
        
    }

}

?>
