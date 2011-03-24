<?php
define("NEWLINE", "\n");
/**
 * Orion form class.
 *
 * <p>Form creation helper</p>
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionForm
{
    /**
     * Class name
     */
    const CLASS_NAME = 'OrionForm';

    /**
     * Hidden input type
     */
    const HIDDEN = 0;
    /**
     * Text input type
     */
    const TEXT = 1;
    /**
     * Passwod input type
     */
    const PASSWORD = 2;
    /**
     * Textarea type
     */
    const TEXTAREA = 3;
    /**
     * Checkbox input type
     */
    const CHECKBOX = 4;
    /**
     * Checkbox list input type
     */
    const CHECKLIST = 5;
    /**
     * Radio list input type
     */
    const RADIOLIST = 6;
    /**
     * Droplist input type
     */
    const DROPLIST = 7;
    /**
     * Image browse type
     */
    const IMAGE = 8;
    /**
     * File browse type
     */
    const FILE = 9;
    /**
     * Valued list input type
     */
    const VALUEDLIST = 10;
    /**
     * Submit input type
     */
    const SUBMIT = 11;

    /**
     * Model handler
     * @var OrionModel
     */
    private $model=null;
    /**
     * Array of OrionFormFields, used to store form fields as Data Object
     * @var OrionFormField array
     */
    private $fields=array();
    /**
     * Array of required fields names
     * @var string array
     */
    private $required=array();
    /**
     * Form name
     * @var string
     */
    private $name=null;
    /**
     * Action url
     * @var string
     */
    private $action=null;

    /**
     * Creates a new form
     */
    public function __construct($_name, $_action='#')
    {
        $this->fields = array();
        $this->required = array();
        $this->name = $_name;
        $this->action = $_action;
    }

    /**
     * Add a new field to the form.
     * These functions can be chained like $form->add(...)->add(...) etc.
     * @param int $type Must be a valid OrionForm::TYPE constant
     * @param string $name The field name
     * @param mixed $value
     * @param string $legend
     * @param mixed $param See OrionModelField for params
     * @param boolean $required
     * @return OrionForm instance
     */
    public function &add($type, $name, $value=null, $legend='', $param=null, $required=null)
    {
        if(array_key_exists($name, $this->fields))
            throw new OrionException('Duplicate field ['.$name.'] during form creation.', E_WARNING, self::CLASS_NAME);

        $this->fields[$name] = new OrionFormField($name, $type, $legend, $param, $required);
        $this->fields[$name]->value = $value;

        return $this;
    }

    /**
     * Add a new field from model to current form
     * @param string $name
     * @param string $legend
     * @param boolean $required
     */
    public function addField($name, $legend, $required=null)
    {
        if(is_null($this->model))
            throw new OrionException('You need to provide a correct OrionModel before using addField()', E_WARNING, self::CLASS_NAME);

        if(array_key_exists($name, $this->fields))
            throw new OrionException('Duplicate field ['.$name.'] during form creation.', E_WARNING, self::CLASS_NAME);

        $field = $this->model->getField($name);

        if(is_null($field))
            throw new OrionException('The field ['.$name.'] does not exist in provided model.', E_WARNING, self::CLASS_NAME);

        if($this->model->isPrimary($name))
            $this->fields[$name] = new OrionFormField($name, OrionForm::HIDDEN);
        elseif($field->type == OrionModel::PARAM_DATE && $field->param == true)
            return;
        else
        {
            if($this->model->isLinked($name))
            {
                $linkedfield = $this->model->getLink($name);
                $linkedmodel = $linkedfield->model;
                $ch = new $linkedmodel();
                $data = $ch->select($linkedfield->rightfield, $linkedfield->rightfield_label)->order($linkedfield->rightfield_label, OrionModel::ASCENDING)->fetchAll();
                $param = array();
                foreach($data as $item)
                    $param[$item->{$linkedfield->rightfield}] = $item->{$linkedfield->rightfield_label};
                $this->fields[$name] = new OrionFormField($name, OrionForm::VALUEDLIST, $legend, $param, $required);
                $ch->flush();
            }
            else
            {
                $this->fields[$name] = new OrionFormField($name, $this->convertModelType($field->type), $legend, $field->param, $required);
            }
        }
        if(!is_null($required))
            $this->required[] = $name;
    }

    /**
     * Hydrate form fields with values stored in provided object (usually for editing purpose)
     * @param Object $object Object to retrives each field values from.
     */
    public function hydrate($object)
    {
        foreach(array_keys($this->fields) as $key)
        {
            if(!is_null($object->{$key}))
                $this->fields[$key]->value = $object->{$key};
        }
    }

    /**
     * Prepare this form using provided OrionModel
     * @param OrionModel $model
     */
    public function prepare($model=null)
    {
        if(is_null($model) && is_null($this->model))
            throw new OrionException('You need to provide a model to prepare this form.', E_WARNING, self::CLASS_NAME);

        if(!is_null($model))
            $this->model = $model;

        foreach($this->model->getFields() as $name => $field)
            $this->addField($name, $field->legend, $field->required);
    }

    /**
     * Retreive the HTML version of the form
     * @param boolean $XHTML Output (x)HTML if set to TRUE instead of pure HTML
     */
    public function toHtml($XHTML=false)
    {
        $html = '<form name="'.$this->name.'" method="post" action="'.$this->action.'"';
        if($this->isMultipart)
             $html.= ' enctype="multipart/form-data"';
        $html .= '>'.NEWLINE;

        foreach($this->fields as $field)
        {
            $html .= $field->toHtml($XHTML).NEWLINE;
        }

        $html .= '</form>'.NEWLINE;

        return $html;
    }

    /**
     * Bind an OrionModel to the form
     * @param OrionModel $_model
     */
    public function setModel($_model)
    {
        $this->model = $_model;
    }

    /**
     * Get bound model
     * @return OrionModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get a field
     * @param string $name
     * @return OrionFormField
     */
    public function &getField($name)
    {
        return $this->fields[$name];
    }

    /**
     * Converts an OrionModel Type into an OrionForm Type
     * @param int $type OrionModel::TYPE
     * @return int OrionForm::TYPE
     */
    private function convertModelType($type)
    {
        switch($type)
        {
            case OrionModel::PARAM_BOOL:
                return self::CHECKBOX;
            break;

            case OrionModel::PARAM_DATE:
            case OrionModel::PARAM_GENERIC:
            case OrionModel::PARAM_TAGS:
            case OrionModel::PARAM_INT:
            case OrionModel::PARAM_NUMERIC:
            case OrionModel::PARAM_STR:
                return self::TEXT;
            break;

            case OrionModel::PARAM_TEXT:
                return self::TEXTAREA;
            break;

            case OrionModel::PARAM_IMAGE:
                return self::IMAGE;
            break;

            case OrionModel::PARAM_LIST:
                return self::DROPLIST;
            break;

            default:
                return self::TEXT;
            break;
        }
    }
}

/**
 * Orion form field sub class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionFormField
{
    public $name;
    public $type;
    public $legend;
    public $param;
    public $required;
    public $value;

    public function  __construct($_name, $_type, $_legend=null, $_param=null, $_required=false)
    {
        $this->name = $_name;
        $this->type = $_type;
        $this->legend = $_legend;
        $this->param = $_param;
        $this->required = $_required;
    }

    /**
     * Retreive the HTML version of a field
     * @param boolean $XHTML Output (x)HTML if set to TRUE instead of pure HTML
     */
    public function toHtml($XHTML=false)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        switch($this->type)
        {
            case OrionForm::HIDDEN:
                return '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"'.$tag.'>';
            break;
        
            case OrionForm::TEXT:
                return '<label for="'.$this->name.'">'.$this->legend.'</label><input name="'.$this->name.'" type="text" class="form-text" value="'.$this->value.'"'.$tag.'>';
            break;

            case OrionForm::PASSWORD:
                return '<label for="'.$this->name.'">'.$this->legend.'</label><input name="'.$this->name.'" type="password" class="form-password"'.$tag.'>';
            break;

            case OrionForm::TEXTAREA:
                return '<label for="'.$this->name.'">'.$this->legend.'</label><textarea name="'.$this->name.'" class="form-textarea">'.$this->value.'</textarea>';
            break;

            case OrionForm::CHECKBOX:
                return '<label for="'.$this->name.'">'.$this->legend.'</label><input name="'.$this->name.'" type="checkbox" class="form-checkbox" value="1"'. ($this->value ? ' checked="checked"' : '' ) .$tag.'>';
            break;

            case OrionForm::CHECKLIST:
                $tmp = '<label for="'.$this->name.'">'.$this->legend.'</label>'.NEWLINE;
                foreach($this->param as $item)
                    $tmp .= '<input name="'.$this->name.'[]" type="checkbox" class="form-checkbox" value="1"'. (in_array($item, $this->value) ? ' checked="checked"' : '' ) .$tag.'>'.NEWLINE;
                return $tmp;
            break;

            case OrionForm::DROPLIST:
                $tmp = '<label for="'.$this->name.'">'.$this->legend.'</label><select name="'.$this->name.'">'.NEWLINE;
                foreach($this->param as $item)
                    $tmp .= '<option value="'.$item.'"'. ($this->value == $item ? ' selected="selected"' : '') .'>'.$item.'</option>'.NEWLINE;
                $tmp .= '</select>';
                return $tmp;
            break;

            case OrionForm::FILE:
            case OrionForm::IMAGE:
                return '<label for="'.$this->name.'">'.$this->legend.'</label>'.NEWLINE
                      .'<input type="file" name="'.$this->name.'" value="'.$this->value.'"'.$tag.'>';
            break;

            case OrionForm::VALUEDLIST:
                $tmp = '<label for="'.$this->name.'">'.$this->legend.'</label><select name="'.$this->name.'">'.NEWLINE;
                foreach($this->param as $val => $item)
                    $tmp .= '<option value="'.$val.'"'. ($this->value == $val ? ' selected="selected"' : '') .'>'.$item.'</option>'.NEWLINE;
                $tmp .= '</select>';
                return $tmp;
            break;

            case OrionForm::SUBMIT:
                return '<input type="submit" class="form-submit" name="'.$this->name.'" value="'.$this->value.'"'.$tag.'>';
            break;

            default:
                return '';
            break;
        }
    }
}
?>
