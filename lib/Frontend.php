<?php

class Frontend extends ApiFrontend {
    function init() {
        parent::init();

        $this->today = date('Y-m-d');
        $this->now = date('Y-m-d H:i:s');
        
        $this->add('jUI');
        $this->add('Layout_Centered');
        
        // $this->dbConnect();
        $this->addLocation();
        // $auth=$this->add('Auth');
        // $auth->usePasswordEncryption();
        // $auth->setModel('User','username','password');
        // throw new \Exception($auth->model->id);
        $this->makeSEF();
        // $auth->check();
        // throw new \Exception('base_url = '.$this->app->pm->base_url." path = ".$this->app->pm->base_path." page = ".$this->app->pm->page." argument=");
    }

    function makeSEF(){
        $this->add('Controller_PatternRouter')
            ->link('home', ['param1','param2','param3'])
            ->link('addapartment')
            ->route();
    }

    function addLocation(){
        $this->api->pathfinder
            ->addLocation(array(
                'addons' => array('vendor','shared/addons2','shared/addons'),
                'addons' => array('vendor','shared/addons2','shared/addons'),
                'css' => array('vendor','shared/templates/assets','templates/assets','assets/css'),
                'js' => array('vendor','shared/templates/assets/js','templates/assets/js'),
                'php' => array('vendor','shared/lib','shared'),
                ''
            ))
            ->setBasePath($this->pathfinder->base_location->getPath());
    }
}
