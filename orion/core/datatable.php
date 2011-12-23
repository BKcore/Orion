<?php
namespace Orion\Core;

/**
 * \Orion\Core\Datatable
 * 
 * Orion Datatable class.
 * Ease handling of long object lists for display
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
class Datatable
{
    /**
     * The datatable identifier (html id attribute)
     * @var String
     */
    protected $id=null;
    /**
     * The displayed header title
     * @var String
     */
    protected $header=null;
    /**
     * The CSS class(es) assigned to the datatable element
     * @var String
     */
    protected $class=null;
    /**
     * An array of data objects
     * @var Object[]
     */
    protected $data=array();
    /**
     * An associative array describing each object field's head legend.
     * Each entry is like this : object_flied_key => object_field_legend
     * @var String[String]
     */
    protected $head=array();
    /**
     * The list of object keys to display
     * @var String[]
     */
    protected $structure=array();
    /**
     * A list of links to display at
     * @var MenuEntry[]
     */
    protected $links=array();
    /**
     * An associative array of closures or functions that are used to parse objects variables before display.
     * These functions get one argument, which is the data to parse.
     * @var Closure[String](String)
     */
    protected $parsers=array();
    /**
     * A list of links that represents an action icon for each entry.
     * @var MenuEntry[]
     */
    protected $itemActions=array();
    
    protected $size=0;

    public function __construct($_id, $_header=null, $_class=null, $_data=null, $_head=null, $_structure=null, $_links=null, $_parsers=null)
    {
        $this->setId($_id);
        if($_header != null) $this->setHeader($_header);
        if($_class != null) $this->setClass($_class);
        if($_data != null) $this->setData($_data);
        if($_head != null) $this->setHead($_head);
        if($_structure != null) $this->setStructure($_structure);
        if($_links != null) $this->setLinks($_links);
        if($_parsers != null) $this->setParsers($_parsers);
    }
    
    public function parse($object, $key)
    {
        $var = $object->{$key};
        if(array_key_exists($key, $this->parsers))
            return $this->parsers[$key]($var);
        else
            return $var instanceof Object ? '[Object]' : $var;
    }
    
    public function size()
    {
        return $this->size;
    }
    
    public function enableNavLinks($offset, $limit, $linkPrev, $linkNext, $popArray=true)
    {
        // Pagination
        if ( $offset > 0 )
        {
            if ( $offset > $limit )
                $prev = $offset - $limit;
            else
                $prev = 0;
            $linkPrev->route = str_replace('@', $prev, $linkPrev->route);
            $this->addLink($linkPrev);
        }
        if ( $this->size > $limit )
        {
            if($popArray) array_pop($this->data);
            $linkNext->route = str_replace('@', ($offset + $limit), $linkNext->route);
            $this->addLink($linkNext);
        }
    }
    
    public function hasItemActions()
    {
        return !empty($this->itemActions);
    }
    
    //------------
    // Getters
    //------------
    public function getId()
    {
        return $this->id;
    }
    
    public function getHeader()
    {
        return $this->header;
    }
    
    public function getClass()
    {
        return $this->class;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function getHead($key=null)
    {
        if($key == null)
            return $this->head;
        elseif(array_key_exists($key, $this->head))
            return $this->head[$key];
        else
            return '-';
    }
    
    public function getStructure()
    {
        return $this->structure;
    }
    
    public function getLinks()
    {
        return $this->links;
    }
    
    public function getItemActions($obj, $before, $after)
    {
        if(empty($this->itemActions))
            return '';
        
        $actions = '';
        foreach($this->itemActions as $link)
            $actions .= '<a href="'.str_replace('$1', $obj->{$link->extra}, $link->getURL()).'" class="datatable-itemaction icon-'.$link->icon.'">'.$link->text.'</a>';
        return $before.$actions.$after;
    }
    
    public function addItemAction(MenuEntry $link, $field='id')
    {
        $link->extra = $field;
        $this->itemActions[] = $link;
    }
    
    
    //------------
    // Setters
    //------------
    
    public function setId($string)
    {
        if(!is_string($string))
            throw new Exception('Datatable id must be a string.');
        $this->id = $string;
    }
    
    public function setHeader($string)
    {
        if(!is_string($string))
            throw new Exception('Datatable header must be a string.');
        $this->header = $string;
    }
    
    public function setClass($string)
    {
        if(!is_string($string))
            throw new Exception('Datatable class must be a string.');
        $this->class = $string;
    }
    
    public function setData($array)
    {
        if(!is_array($array) || empty($array) || !($array[0] instanceof \stdClass))
            throw new Exception('Datatable data must be a non-empty array of Objects.');
        $this->data = $array;
        $this->size = count($array);
    }
    
    public function setHead($array, $var=null)
    {
        if(is_string($array) && $var != null && is_string($var))
        { // Key / val definition
            $this->head[$array] = $var;
            return;
        }
        
        if(!is_array($array) || empty($array) || !is_string(reset($array)))
            throw new Exception('Datatable head must be a non-empty associative array of strings.');
        $this->head = $array;
    }
    
    public function setStructure($array)
    {
        if(!is_array($array) || empty($array) || !is_string($array[0]))
            throw new Exception('Datatable structure must be a non-empty array of keys.');
        $this->structure = $array;
    }
    
    public function setLinks($array)
    {
        if(!is_array($array) || empty($array) || !($array[0] instanceof MenuEntry))
            throw new Exception('Datatable links must be a non-empty array of MenuEntry objects.');
        $this->links = $array;
    }
    
    public function addLink(MenuEntry $link)
    {
        $this->links[] = $link;
    }
    
    public function setParsers($array)
    {
        if(!is_array($array) || empty($array) || !is_callable($array[0]))
            throw new Exception('Datatable parsers must be a non-empty array of functions.');
        $this->parsers = $array;
    }
    
    public function setParser($key, $function)
    {
        if(!is_string($key))
            throw new Exception('Datatable parser key must be a string.');
        if(!is_callable($function))
            throw new Exception('Datatable parser must be a callable function.');
        $this->parsers[$key] = $function;
    }
}
?>
