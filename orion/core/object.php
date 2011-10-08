<?php
/**
 * Orion object class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Core;

class Object extends \stdClass
{
    public function isA($class)
    {
        return ($this instanceOf $class);
    }
}
?>
