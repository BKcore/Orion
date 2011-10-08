<?php

/**
 * Orion authentification sub user class object.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */

namespace Orion\Models\Auth;

use \Orion\Core;

class User extends Core\Model
{

    public $id;
    public $login;
    public $password;
    public $name;
    public $level;
    
    protected static $table;
    protected static $fields, $events, $primaryKeys;

    protected static function describe()
    {
        self::$table = \Orion::config()->get('AUTH_TABLE_USER');
        self::has( new Core\Model\Id( 'id', 'ID', true ) );
        self::has( new Core\Model\String( 'login', 'Login', 60, '[a-zA-Z0-9_\.]+', true ) );
        self::has( new Core\Model\Password( 'password', 'Mot de passe', true ) );
        self::has( new Core\Model\String( 'name', 'Nom complet', 255, null, true ) );
        self::has( new Core\Model\Select( 'level', 'Niveau', \Orion::config()->get( 'AUTH_ROLES' ), true ) );
    }
    
    public function &encrypt()
    {
        $this->password = Core\Security::saltedHash( $this->password, $this->login );
        return $this;
    }

    /**
     * Check if user level is ($atleast) $slug
     * @param string $slug The level's slug
     * @param boolean $atleast Is exactly $slug (FALSE), or at least $slug (TRUE)
     */
    public function is( $slug, $atleast=false )
    {
        if ( empty( $this->level ) )
            throw new Core\Exception( "Le niveau de l'utilisateur n'est pas défini." );

        $roles = \Orion::config()->get( 'AUTH_ROLES' );

        if ( !array_key_exists( $slug, $roles ) )
            throw new Core\Exception( "Le rôle demandé n'existe pas." );

        $roleval = $roles[ $slug ];

        if ( ($this->level == $roleval) || ($atleast && $this->level <= $roleval) )
            return true;
        else
            return false;
    }

    public function toArray()
    {
        return array(
            'login' => $this->login,
            'name' => $this->name,
            'level' => $this->level,
            'id' => $this->id
        );
    }

    public function fromArray( $array )
    {
        $this->login = $array[ 'login' ];
        $this->name = $array[ 'name' ];
        $this->level = $array[ 'level' ];
        $this->id = $array[ 'id' ];
    }

}

?>
