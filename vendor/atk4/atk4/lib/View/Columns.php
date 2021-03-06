<?php
/**
 * Implementation of columns.
 *
 * Use:
 *
 * 12-GS:
 *  $cols=$this->add('Columns');
 *  $cols->addColumn(4)->add('LoremIpsum');
 *  $cols->addColumn(6)->add('LoremIpsum');
 *  $cols->addColumn(2)->add('LoremIpsum');
 *
 * Flexible
 *  $cols=$this->add('Columns');
 *  $cols->addColumn('10%')->add('LoremIpsum');
 *  $cols->addColumn('90%')->add('LoremIpsum');
 *
 * Auto
 *  $cols=$this->add('Columns');
 *  $cols->addColumn()->add('LoremIpsum');
 *  $cols->addColumn()->add('LoremIpsum');
 *
 *  $cols->addClass('atk-cells-gutter-large')
 *
 * @license See https://github.com/atk4/atk4/blob/master/LICENSE
 **/
class View_Columns extends View
{
    public $mode = 'auto';      // 'auto', 'grid' or 'pct'
    /**
     * Adds new column to the set.
     * Argument can be numeric for 12GS, percent for flexi design or omitted for equal columns.
     */
    public function addColumn($width = 'auto')
    {
        $c = $this->add('View');
        if (is_numeric($width)) {
            $this->mode = 'grid';
            $c->addClass('atk-col-'.$width);

            return $c;
        } elseif (substr($width, -1) == '%') {
            $this->mode = 'pct';
            $c->addStyle('width', $width);
        }
        $this->template->trySet('row_class', 'atk-cells');
        $c->addClass('atk-cell');

        return $c;
    }
    public function setGutter($size = '')
    {
        if ($size) {
            $size = '-'.$size;
        }
        $this->addClass('atk-cells-gutter'.$size);

        return $this;
    }
    public function defaultTemplate()
    {
        return array('view/columns');
    }
}
