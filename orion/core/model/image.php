<?php

namespace Orion\Core\Model;

use \Orion\Core;

class Image extends Field
{

    protected $image = null;
    protected $prefix;

    public function __construct( $bind='image', $label='Image', $prefix='', $required=false )
    {
        $this->type = 'image';
        $this->multipart = true;
        $this->bind = $bind;
        $this->label = $label;
        $this->prefix = $prefix;
        $this->required = $required;
        $this->allowed = $allowed;
    }

    public function prepare( $value )
    {
        if ( $this->image != null )
            return "'" . $this->image->getIdentifier() . "'";
        elseif ( empty( $_FILES[ $this->bind ][ 'tmp_name' ] ) )
            return null;
        else
            throw new Core\Exception( 'Error while uploading image, unable to retreive identifier.', E_USER_ERROR );
    }

    public function onSave( $value )
    {
        if ( empty( $_FILES[ $this->bind ][ 'tmp_name' ] ) )
            return;

        try
        {
            $this->image = new Core\Upload( $this->bind, Upload::IMAGE_UPLOAD_DIR );
            $this->image->restrict( Core\Upload::JPEG, Core\Upload::PNG, Core\Upload::GIF );
            $this->image->setPrefix( $this->prefix );
            $this->image->upload();
        }
        catch ( Core\Exception $e )
        {
            throw $e;
        }
    }

    public function onDelete( $oldvalue )
    {
        if ( file_exists( $oldvalue ) )
            @unlink( $oldvalue );
    }

    public function onUpdate( $oldvalue, $newvalue )
    {
        if ( !empty( $newvalue ) )
        {
            $this->onDelete( $oldvalue );
            $this->onSave( $newvalue );
        }
    }

    public function toHtml( $XHTML=true )
    {
        if ( $XHTML )
            $tag = ' /';
        else
            $tag = '';

        return '<div class="form-row"><label for="' . $this->bind . '">' . $this->label . '</label>' . "\n"
                . '<div class="form-container"><input class="form-element" type="file" name="' . $this->bind . '" ' . $tag . '></div></div>';
    }

}

?>
