<?php

class OrionModelImage extends OrionModelField
{
	protected $image=null;
    protected $prefix;

    public function __construct($bind='image', $label='Image', $prefix='', $required=false)
    {
        $this->type = 'image';
        $this->multipart = true;
        $this->bind = $bind;
        $this->label = $label;
        $this->prefix = $prefix;
        $this->required = $required;
        $this->allowed = $allowed;
    }

    public function prepare($value)
    {
        if($this->image != null)
            return "'".$this->image->getIdentifier()."'";
		elseif(empty($_FILES[$this->bind]['tmp_name']))
			return null;
		else
			throw new OrionException('Error while uploading image, unable to retreive identifier.', E_USER_ERROR);
    }

	public function onSave($value)
	{
        if(empty($_FILES[$this->bind]['tmp_name']))
            return;

        var_dump($_FILES[$this->bind]);

		try {
            $this->image = new OrionUpload($this->bind, OrionUpload::IMAGE_UPLOAD_DIR);
            $this->image->restrict(OrionUpload::JPEG, OrionUpload::PNG, OrionUpload::GIF);
            $this->image->setPrefix($this->prefix);
            $this->image->upload();
        }
        catch(OrionException $e)
        {
            throw $e;
        }
	}

	public function onDelete($oldvalue)
	{
		if(file_exists($oldvalue))
			@unlink($oldvalue);
	}

	public function onUpdate($oldvalue, $newvalue)
	{
		if(!empty($newvalue))
		{
			$this->onDelete($oldvalue);
			$this->onSave($newvalue);
		}
	}

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<label for="'.$this->bind.'">'.$this->label.'</label>'."\n"
              .'<input type="file" name="'.$this->bind.'" '.$tag.'>';
    }
}

?>
