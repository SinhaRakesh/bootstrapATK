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
        $this->app->addMethod('nextDate',function($app,$date=null){    
            if(!$date) $date = $this->api->today;
            $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 DAY"));    
            return $date;
        });

        $this->api->pathfinder
            ->addLocation(array(
                'addons' => array('vendor','shared/addons2','shared/addons'),
            ))
            ->setBasePath($this->pathfinder->base_location->getPath() . '/..');

        $this->dbConnect();
        $this->add('jUI');

        $active_user = $this->add('Model_User')->addCondition('is_active',true);
        $auth = $this->app->add('BasicAuth');

        $this->app->add('Layout_Centered');
        $auth->usePasswordEncryption();
        $auth->setModel($active_user);
        $auth->check();

        if($auth->isLoggedIn()){
            $this->app->layout->destroy();
            $this->add($this->layout_class);
        }
       
        // $this->add('Layout_Fluid');
        // $this->menu = $this->layout->addMenu('Menu_Vertical');
        // $this->menu->swatch = 'ink';
        // $m = $this->layout->addFooter('Menu_Horizontal');
        // $m->addItem('foobar');
        // $this->initTopMenu();

        $this->layout->template->trySet($this->app->page,'active');
        
    }

    function initTopMenu(){

        // $top_menu=$this->layout->add('Menu_Horizontal',null,'Top_Menu');
        // $top_menu->addItem(['Configuration','icon'=>'ajust'],'/configuration');
        // $top_menu->addItem(['Apartment','icon'=>'ajust'],'/apartment');
    }
}
