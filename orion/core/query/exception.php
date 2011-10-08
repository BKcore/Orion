<?php

namespace Orion\Core\Query;

class Exception extends \Orion\Core\Exception
{

    /**
     * Generates a Query Exception
     * @param string $message
     * @param int $code
     * @param string $caller Caller class' name
     */
    public function __construct( $message='An exception occured', $code=256, $line=null )
    {
        parent::__construct( ( string ) $message, ( int ) $code, 'Query' );
        if($line != null) $this->line = $line;
    }

}

?>
