<?php

class OrionModelTags extends OrionModelField
{
    protected $separator;
    protected $model;
    protected $namefield;
    protected $counterfield;

    //' ', 'TagHandler', 'name', 'counter'

    public function __construct($bind='tags', $label='Tags', $separator=' ', $model='TagsHandler', $namefield='name', $counterfield='counter', $required=false)
    {
        $this->type = 'string';
        $this->bind = $bind;
        $this->label = $label;
        $this->separator = $separator;
        $this->model = $model;
        $this->namefield = $namefield;
        $this->counterfield = $counterfield;
        $this->required = $required;
    }

    public function onDelete($value)
    {
        $this->removeTags($value);
    }

    public function onSave($value)
    {
        $this->saveTags($value);
    }

    public function onUpdate($newvalue, $oldvalue)
    {
        $this->removeTags($oldvalue);
        $this->saveTags($newvalue);
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<label for="'.$this->bind.'">'.$this->label.'</label><input name="'.$this->bind.'" type="text" class="form-tags" value="'.$this->value.'"'.$tag.'>';
    }

    /**
	 * Parse and save/update tags of model into their respective table (defined with PARAM_TAGS)
	 * @param $data the tag list as a string
	 * @return boolean success
	 */
	protected function saveTags($data)
	{
        $pdo = OrionSql::getConnection();

        $tags = explode($this->separator, $data);
        if(empty($tags)) return false;
        $thClass = $this->model;
        $th = new $thClass();
        $values = "(".implode('),(', $th->formatArray($th->escapeArray($tags))).")";
        $query = "INSERT INTO ".$th->escape($th->getTable())." (".$th->escape($this->namefield).") VALUES ".$values." ON DUPLICATE KEY UPDATE ".$th->escape($this->counterfield)."=".$th->escape($this->counterfield)."+1;";

        try {
            $result = $pdo->exec($query);
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), $this->CLASS_NAME);
        }
	}

    /**
     * Decrease tags counters and delete tag when deleted entry was the latest one using it
     * @param $data the tag list as a string
     */
    protected function removeTags($data)
    {
		$pdo = OrionSql::getConnection();

        $tags = explode($this->separator, $data);
        if(empty($tags)) return false;
        $thClass = $this->model;
        $th = new $thClass();
        $wstart = $th->escape($this->namefield)."=";
        $values = $wstart.implode(' OR '.$wstart, $th->formatArray($th->escapeArray($tags)));
        $queryUpd = "UPDATE ".$th->escape($th->getTable())." SET ".$th->escape($this->counterfield)."=".$th->escape($this->counterfield)."-1 WHERE ".$values." ;";
        $queryDel = "DELETE FROM ".$th->escape($th->getTable())." WHERE ".$th->escape($this->counterfield)."<1;";

        try {
            $resultUpd = $pdo->exec($queryUpd);
            $resultDel = $pdo->exec($queryDel);
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), $this->CLASS_NAME);
        }
    }
}

?>
