<?php

class Form_Field_DropDownNormal extends \Form_Field_ValueList {
    public $validate_values=true;
    public $select_menu_options = array();

    function getInput($attr=array()){
        // $this->select_menu_options['change']=$this->js()->trigger('change')->_enclose();
        // $this->js(true)->_load('select2.min')->_css('libs/select2')->select2($this->select_menu_options);
        // if($this->get())
        //     $this->js(true)->select2('val',$this->get());
        $multi = isset($this->attr['multiple']);
        $output=$this->getTag('select',array_merge(array(
                        'name'=>$this->name . ($multi?'[]':''),
                        'data-shortname'=>$this->short_name,
                        'id'=>$this->name,
                        ),
                    $attr,
                    $this->attr)
                );

        foreach($this->getValueList() as $value=>$descr){
            // Check if a separator is not needed identified with _separator<
            $output.=
                $this->getOption($value)
                .$this->api->encodeHtmlChars($descr)
                .$this->getTag('/option');
        }
        $output.=$this->getTag('/select');
        return $output;
    }

    function validateValidItem(){
        if($this->validate_values) return parent::validateValidItem();
        return true;
    }

    function getOption($value){
        $selected = false;
        if($this->value===null || $this->value===''){
            $selected = $value==='';
        } else {
            $selected = $value == $this->value;
        }
        return $this->getTag('option',array(
                    'value'=>$value,
                    'selected'=>$selected
        ));
    }
}
