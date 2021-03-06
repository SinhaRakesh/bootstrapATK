<?php
/**
 * Undocumented
 */
class Menu_Advanced_Item extends View
{
    public function init()
    {
        parent::init();

        if ($this->owner->swatch) {
            $this->addComponents(array('swatch' => $this->owner->swatch));
        }
    }

    public function set($data)
    {
        if (is_array($data)) {
            if ($data['icon2']) {
                $this->add('Icon', null, 'Badge')->set($data['icon2']);
            }
            unset($data['icon2']);
        }

        return parent::set($data);
    }

    public function addItem()
    {
        throw $this->exception('Do not chain addItem calls, the return individual objects');
    }
}
