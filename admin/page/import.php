<?php

class page_import extends Page {

    public $title = 'Import';

    function page_index() {
        // parent::init();

        // bhav
        $form_bhav = $this->add('Form');
        $form_bhav->template->loadTemplateFromString('<form method="POST" action="'.$this->api->url('./bhavexecute').'" enctype="multipart/form-data"><div class="project"><div class="row bg-white has-shadow"><div class="project-title d-flex align-items-center col-md-4">  <div class="text"><h3 class="h4">Upload Daily BHAV</h3></div></div><div class="col-md-5"><input name="csv_bhav_file" type="file"><br/><p style="color:gray;">Hint: Select CSV File Only</p> </div><div class="col-md-3"><input value="Upload" class="btn btn-primary" type="submit"><hr/> <a download href="templates/csv/sample_of_daily_bhav.csv">Download Sample CSV File</a></div></div></div></form>');
        
        // transaction
        $form_tran = $this->add('Form');
        $form_tran->template->loadTemplateFromString('<form method="POST" action="'.$this->api->url('./tranexecute').'" enctype="multipart/form-data"><div class="project"><div class="row bg-white has-shadow"><div class="project-title d-flex align-items-center col-md-4">  <div class="text"><h3 class="h4">Upload Client Daily Transaction</h3></div></div><div class="col-md-5"><input type="file" name="csv_tran_file"/><br/><p style="color:gray;">Hint: Select CSV File Only</p> </div><div class="col-md-3"><input type="submit" class="btn btn-primary" value="Upload"/><hr/> <a download href="templates/csv/sample_of_daily_transaction.csv">Download Sample CSV File</a></div></div></div></form>');
        
        $this->add('View')->setElement('hr');
        $this->add('View')->setElement('h4')->set('Bulk Data Upload');
        $this->add('View')->setElement('hr');
        // client
        $form_client = $this->add('Form');
        $form_client->template->loadTemplateFromString('<form method="POST" action="'.$this->api->url('./clientexecute').'" enctype="multipart/form-data"><div class="project"><div class="row bg-white has-shadow"><div class="project-title d-flex align-items-center col-md-4"><div class="text"><h3 class="h4">Upload Client</h3></div></div><div class="col-md-5"><input type="file" name="csv_client_file" /><br/><p style="color:gray;">Hint: Select CSV File Only</p> </div><div class="col-md-3"><input type="submit" class="btn btn-primary" value="Upload" /><hr/> <a download href="templates/csv/sample_of_client.csv">Download Sample CSV File</a></div></div></div></form>');

        // buy data
        $form_client_buy = $this->add('Form');
        $form_client_buy->template->loadTemplateFromString('<form method="POST" action="'.$this->api->url('./clientbuyexecute').'" enctype="multipart/form-data"><div class="project"><div class="row bg-white has-shadow"><div class="project-title d-flex align-items-center col-md-4"><div class="text"><h3 class="h4">Upload Client Wise Buy Data</h3></div></div><div class="col-md-5"><input type="file" name="csv_client_buy_file" /><br/><p style="color:gray;">Hint: Select CSV File Only</p> </div><div class="col-md-3"><input type="submit" class="btn btn-primary" value="Upload" /><hr/> <a download href="templates/csv/sample_of_client_wise_buy_data.csv">Download Sample CSV File</a></div></div></div></form>');
        
        // sell data
        $form_client_sell = $this->add('Form');
        $form_client_sell->template->loadTemplateFromString('<form method="POST" action="'.$this->api->url('./clientsellexecute').'" enctype="multipart/form-data"><div class="project"><div class="row bg-white has-shadow"><div class="project-title d-flex align-items-center col-md-4"><div class="text"><h3 class="h4">Upload Client Wise Sell Data</h3></div></div><div class="col-md-5"><input type="file" name="csv_client_sell_file" /><br/><p style="color:gray;">Hint: Select CSV File Only</p> </div><div class="col-md-3"><input type="submit" class="btn btn-primary" value="Upload" /><hr/> <a download href="templates/csv/sample_of_client_wise_sell_data.csv">Download Sample CSV File</a></div></div></div></form>');
        
        // $this->on('click','.download-csv',function($js,$data){
        //     return $js->alert('');
        //     $header = ['id','name','department','post','working_type_unit','unit_count'];
        //     $fp = fopen("php://output", "w");
        //     fputcsv ($fp, $header, "\t");
        //     fclose($fp);
        //     header("Content-type: text/csv");
        //     header("Content-disposition: attachment; filename=\"sample_xepan_attandance_import.csv\"");
        //     exit;
        // });
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

    function page_clientbuyexecute(){
        ini_set('max_execution_time', 0);

        if($_FILES['csv_client_buy_file']){
            if ( $_FILES["csv_client_buy_file"]["error"] > 0 ) {
                $this->add( 'View_Error' )->set( "Error: " . $_FILES["csv_client_buy_file"]["error"] );
            }else{
                $mimes = ['text/comma-separated-values', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext'];
                if(!in_array($_FILES['csv_client_buy_file']['type'],$mimes)){
                    $this->add('View_Error')->set('Only CSV Files allowed');
                    return;
                }
                
                $importer = new CSVImporter($_FILES['csv_client_buy_file']['tmp_name'],true,',');
                $data = $importer->get();

                $client = $this->add('Model_Client');
                $result = $client->updateClientWiseData($data,"Buy");

                 $count_result = [
                    0 =>[
                            'name'=>"Total Client Buy List",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-violet",
                            'count'=>$result['total_data_to_import'],
                            'column_class'=>"col-xl-4 col-sm-12"
                        ],
                    1 =>[
                            'name'=>"Total Client Buy List Imported",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-violet",
                            'count'=>$result['total_data_imported'],
                            'column_class'=>"col-xl-4 col-sm-12"
                        ]
                    ];
                
                unset($_FILES['csv_client_buy_file']);
                $dc = $this->add('View_DashboardCount',['heading'=>'Client wise buy list']);
                $dc->setStyle('padding-top',0);
                $dc->setSource($count_result);

                
            }
        }
    }

    function page_clientsellexecute(){
        ini_set('max_execution_time', 0);

        if($_FILES['csv_client_sell_file']){
            if ( $_FILES["csv_client_sell_file"]["error"] > 0 ) {
                $this->add( 'View_Error' )->set( "Error: " . $_FILES["csv_client_sell_file"]["error"] );
            }else{
                $mimes = ['text/comma-separated-values', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext'];
                if(!in_array($_FILES['csv_client_sell_file']['type'],$mimes)){
                    $this->add('View_Error')->set('Only CSV Files allowed');
                    return;
                }
                
                $importer = new CSVImporter($_FILES['csv_client_sell_file']['tmp_name'],true,',');
                $data = $importer->get();

                $client = $this->add('Model_Client');
                $result = $client->updateClientWiseData($data,"Sell");

                 $count_result = [
                    0 =>[
                            'name'=>"Total Client Sell List",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-violet",
                            'count'=>$result['total_data_to_import'],
                            'column_class'=>"col-xl-4 col-sm-12"
                        ],
                    1 =>[
                            'name'=>"Total Client Sell List Imported",
                            'icon_class'=>"fa fa-users",
                            'bg_color_class'=>"bg-violet",
                            'count'=>$result['total_data_imported'],
                            'column_class'=>"col-xl-4 col-sm-12"
                        ]
                    ];
                $dc = $this->add('View_DashboardCount',['heading'=>'Client wise buy list']);
                $dc->setStyle('padding-top',0);
                $dc->setSource($count_result);

                unset($_FILES['csv_client_buy_file']);
                
            }
        }
    }


}
