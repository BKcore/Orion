<?php
/**
 * Orion security class.
 * Prevents illegal characters usage
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionSecurity
{
    public function saltedHash($data, $extrasalt)
    {
        $password = str_split($data,(strlen($data)/2)+1);
        $hash = hash('sha1', $extrasalt.$password[0].'a6Re1M2d'.$password[1]);
        return $hash;
    }
    
}
?>
