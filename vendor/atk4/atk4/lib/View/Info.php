<?php
/**
 * Adds standard box with information
 */
class View_Info extends View_Box
{
    public function init()
    {
        parent::init();
        $this->addClass('atk-effect-info');
        $this->addIcon('info-circled');
    }
}
