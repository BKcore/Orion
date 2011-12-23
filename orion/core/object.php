<?php
namespace Orion\Core;

/**
 * \Orion\Core\Object
 * 
 * Orion object class.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
class Object extends \stdClass
{
    /**
     * Tests if object is of given instance.
     * @param string $class
     * @return boolean
     */
    public function isA($class)
    {
        return ($this instanceOf $class);
    }
}
?>
