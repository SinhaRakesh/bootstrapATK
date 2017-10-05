<?php
/**
 * Centered layout for showing a single form or message on the page.
 */
class Layout_Apartment extends Layout_Fluid
{

	function init(){
		parent::init();

	}

    public function defaultTemplate()
    {
        return array('layout/apartment');
    }
}
