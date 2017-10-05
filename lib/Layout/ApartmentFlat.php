<?php
/**
 * Centered layout for showing a single form or message on the page.
 */
class Layout_ApartmentFlat extends Layout_Apartment
{

	function init(){
		parent::init();

	}

    public function defaultTemplate()
    {
        return array('layout/apartmentflat');
    }
}
