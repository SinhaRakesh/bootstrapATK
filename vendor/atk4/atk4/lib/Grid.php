<?php
/**
 * Undocumented.
 */
class Grid extends Grid_Advanced
{
	public $sno=1;
    public $sno_decending=false;
    public $order=null;

    function init(){
        parent::init();
        $this->order = $this->addOrder();
    }

    function addSno($name = 's no',$descending=false){
        $this->sno_decending = $descending;
        $this->addColumn('sno','s_no',$name);
        $this->order->move('s_no','first')->now();
    }

    function init_sno($field){
        if($this->sno_decending) $this->sno = $this->model->count()->getOne();
    }

    function format_sno($field){
    	
        if($this->model->loaded()){

            if($this->sno_decending){                
                $this->current_row[$field] = (($this->sno--) - ($_GET[@$this->paginator->skip_var.'_skip']?:0));
            }
            else{
                $this->current_row[$field] = (($this->sno++) + ($_GET[@$this->paginator->skip_var.'_skip']?:0));
            }
        }
    }
}
