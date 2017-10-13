<?php

class View_DashboardCount extends CompleteLister{
  public $heading;
  
  function init(){
    parent::init();
    $this->template->trySet('heading',$this->heading);
  }

  function formatRow(){

    $this->current_row_html['name'] = $this->model['name'];
    if(!$this->model['column_class'])
      $this->current_row_html['column_class'] = "col-xl-3 col-sm-6";
    if(!$this->model['progress_bar_value'])
      $this->current_row_html['progress_bar_value'] = 25;

    parent::formatRow();
  }

  function defaultTemplate(){
    return ['view/dashboardcount'];
  }

}