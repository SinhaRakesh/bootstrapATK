<?php

class Admin extends App_Frontend {
    public $title = 'Apartment Buddy';

    private $controller_install_addon;

    public $layout_class = 'Layout_Admin_Bootstrap4Material';

    public $auth_config = array('admin' => 'admin');

    /** Array with all addon initiators, introduced in 4.3 */
    private $addons = array();

    /**
     * Initialization.
     */
    public function init()
    {
        parent::init();

        date_default_timezone_set("Asia/Calcutta");
        $this->today = date('Y-m-d');
        $this->now = date('Y-m-d H:i:s');

        $this->api->pathfinder
            ->addLocation(array(
                'addons' => array('vendor','shared/addons2','shared/addons'),
            ))
            ->setBasePath($this->pathfinder->base_location->getPath() . '/..');

        $this->dbConnect();
        $this->add('jUI');

        // $auth = $this->add('Auth');
        // try{
        //     $auth->usePasswordEncryption();
        //     $user_model = $this->add('Model_User')->addCondition('type','superadmin');
        //     $auth->setModel($user_model,'email','password');
        //     $auth->check();
        // }catch(Exception $e){
        //     $this->js(true)->univ()->errorMessage('authentication error');
        //     // exit;
        // }

        // if($this->api->auth->model['type'] != "superadmin"){
        //     $this->api->auth->logout();
        //     exit;
        // }

        $this->add($this->layout_class);
        
        // $this->add('Layout_Fluid');
        // $this->menu = $this->layout->addMenu('Menu_Vertical');
        // $this->menu->swatch = 'ink';
        // $m = $this->layout->addFooter('Menu_Horizontal');
        // $m->addItem('foobar');
        // $this->initTopMenu();
    }

    function initTopMenu(){

        // $top_menu=$this->layout->add('Menu_Horizontal',null,'Top_Menu');
        // $top_menu->addItem(['Configuration','icon'=>'ajust'],'/configuration');
        // $top_menu->addItem(['Apartment','icon'=>'ajust'],'/apartment');
    }
}
