<?php

namespace Orion\Core\Query;


/**
 * \Orion\Core\Query\Base
 * 
 * Orion Query base interface.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
interface Base
{
    public function __construct( $model );
    
    public function &select( $fields );
    public function &selectAllExcept( $fields );
    public function &join( $link, $fields, $type );
    public function &delete( );
    public function &save( );
    public function &update( );
    
    public function &set( $key, $value );
    
    public function fetch( );
    public function fetchAll( );
    
    public function &limit( $number );
    public function &offset( $number );
    
    public function &order( $field, $mode );
    
    public function &andWhere( $field, $comparator, $value );
    public function &orWhere( $field, $comparator, $value );
    public function &manualWhere( $cond );
    public function &where( $field, $comparator, $value );
    
    public function escape( $data );
    public function tablePrefix( $fields );
    
    public function &setTable( $table );
    public function &unsetTable( );
    
}

?>
