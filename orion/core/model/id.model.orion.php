<?php

class OrionModelId extends OrionModelField
{
    /**
     * ID model field
     * @param string $bind
     * @param string $label
     * @param boolean $primary
     */
    public function __construct($bind='id', $label='Id', $primary=true)
    {
        $this->visible = false;
        $this->type = 'id';
        $this->bind = $bind;
        $this->label = $label;
        $this->primary = $primary;
    }
}

?>
