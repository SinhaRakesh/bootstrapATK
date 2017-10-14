<?php

class page_import extends Page {

    public $title = 'Import BHAV/ Client Daily Transaction';

    function page_index() {
        // parent::init();


        $form_bhav = $this->add('Form');
        $form_bhav->template->loadTemplateFromString("<form method='POST' action='".$this->api->url('./bhavexecute')."' enctype='multipart/form-data'><input type='file' name='csv_bhav_file'/><input type='submit' value='Upload Daily Bhav'/></form>");
        
        $form_tran = $this->add('Form');
        $form_tran->template->loadTemplateFromString("<form method='POST' action='".$this->api->url('./tranexecute')."' enctype='multipart/form-data'><input type='file' name='csv_tran_file'/><input type='submit' value='Upload Client Transaction'/></form>");
        
        $form_client = $this->add('Form');
        $form_client->template->loadTemplateFromString("<form method='POST' action='".$this->api->url('./clientexecute')."' enctype='multipart/form-data'><input type='file' name='csv_client_file'/><input type='submit' value='Upload Client '/></form>");

    }

    function page_bhavexecute(){

        ini_set('max_execution_time', 0);

        if($_FILES['csv_bhav_file']){
            if ( $_FILES["csv_bhav_file"]["error"] > 0 ) {
                $this->add( 'View_Error' )->set( "Error: " . $_FILES["csv_bhav_file"]["error"] );
            }else{
                $mimes = ['text/comma-separated-values', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext'];
                if(!in_array($_FILES['csv_bhav_file']['type'],$mimes)){
                    $this->add('View_Error')->set('Only CSV Files allowed');
                    return;
                }
                
                $importer = new CSVImporter($_FILES['csv_bhav_file']['tmp_name'],true,',');
                $data = $importer->get();

                $this->add('View_Info')->set('Total Records To be Imported: '.count($data));
                $company = $this->add('Model_Company');
                $company->updateDailyBhav($data);

                $this->app->redirect($this->app->url('company'));
            }
        }
    }

    function page_tranexecute(){

        ini_set('max_execution_time', 0);

        if($_FILES['csv_tran_file']){
            if ( $_FILES["csv_tran_file"]["error"] > 0 ) {
                $this->add( 'View_Error' )->set( "Error: " . $_FILES["csv_tran_file"]["error"] );
            }else{
                $mimes = ['text/comma-separated-values', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext'];
                if(!in_array($_FILES['csv_tran_file']['type'],$mimes)){
                    $this->add('View_Error')->set('Only CSV Files allowed');
                    return;
                }
                
                $importer = new CSVImporter($_FILES['csv_tran_file']['tmp_name'],true,',');
                $data = $importer->get();

                $client = $this->add('Model_Client');
                $result = $client->updateTransaction($data);

                $count_result = [
                        0 =>[
                            'name'=>"Data<br/> To Import",
                            'icon_class'=>"fa fa-tasks",
                            'bg_color_class'=>"bg-violet",
                            'count'=>$result['total_data_to_import'],
                            'column_class'=>"col-xl-6 col-sm-12"
                        ],
                        1 =>[
                            'name'=>"Data<br/> Imported",
                            'icon_class'=>"fa fa-plus",
                            'bg_color_class'=>"bg-green",
                            'count'=>$result['total_data_imported'],
                            'column_class'=>"col-xl-6 col-sm-12"
                        ],
                        2 =>[
                            'name'=>"Client<br/> Not Found",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-red",
                            'count'=>$result['total_client_not_found'],
                            'column_class'=>"col-xl-6 col-sm-12"
                        ],
                        3 =>[
                            'name'=>"Company<br/> Not Found",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-red",
                            'count'=>$result['total_company_not_found'],
                            'column_class'=>"col-xl-6 col-sm-12"
                        ],

                    ];

                $dc = $this->add('View_DashboardCount',['heading'=>'Transaction Record Imported']);
                $dc->setStyle('padding-top',0);
                $dc->setSource($count_result);
                // $this->app->redirect($this->app->url('import'));
            }
        }
    }

    function page_clientexecute(){
        ini_set('max_execution_time', 0);

        if($_FILES['csv_client_file']){
            if ( $_FILES["csv_client_file"]["error"] > 0 ) {
                $this->add( 'View_Error' )->set( "Error: " . $_FILES["csv_client_file"]["error"] );
            }else{
                $mimes = ['text/comma-separated-values', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext'];
                if(!in_array($_FILES['csv_client_file']['type'],$mimes)){
                    $this->add('View_Error')->set('Only CSV Files allowed');
                    return;
                }
                
                $importer = new CSVImporter($_FILES['csv_client_file']['tmp_name'],true,',');
                $data = $importer->get();

                $client = $this->add('Model_Client');
                $result = $client->importClient($data);

                $count_result = [
                    0 =>[
                            'name'=>"Client To Import",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-violet",
                            'count'=>$result['total_data_to_import'],
                            'column_class'=>"col-xl-4 col-sm-12"
                        ],
                    1 =>[
                            'name'=>"New Client Imported",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-violet",
                            'count'=>$result['total_data_imported'],
                            'column_class'=>"col-xl-4 col-sm-12"
                        ],
                    2 =>[
                            'name'=>"Old Client Found",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-red",
                            'count'=>$result['total_old_client'],
                            'column_class'=>"col-xl-4 col-sm-12"
                        ]
                    ];
                $dc = $this->add('View_DashboardCount',['heading'=>'Client List Imported']);
                $dc->setStyle('padding-top',0);
                $dc->setSource($count_result);
                $this->add('Button')->set('Redirect to Import Page')->js('click')->univ()->redirect($this->app->url('import'));
                
                unset($_FILES['csv_client_file']);
            }
        }

    }

}