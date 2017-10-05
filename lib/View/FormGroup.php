<?php
/**
 * Adds standard box with success message
 */
class View_FormGroup extends View
{
    public function init()
    {
        parent::init();
        
        $this->addClass('title material-form-group');
    }
}
