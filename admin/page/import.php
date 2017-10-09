<?php

class page_import extends Page {

    public $title = 'Import BHAV/ Client Daily Transaction';

    function page_index() {
        // parent::init();


        $form_bhav = $this->add('Form');
        $form_bhav->template->loadTemplateFromString("<form method='POST' action='".$this->api->url('./bhavexecute')."' enctype='multipart/form-data'><input type='file' name='csv_bhav_file'/><input type='submit' value='Upload'/></form>");
        $form_bhav->template->loadTemplateFromString("<form method='POST' action='".$this->api->url('./tranexecute')."' enctype='multipart/form-data'><input type='file' name='csv_tran_file'/><input type='submit' value='Upload'/></form>");
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

                $this->app->redirect($thia->app->url('import'));
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

                $this->add('View_Info')->set('Total Records To be Imported: '.count($data));

                $client = $this->add('Model_Client');
                $client->updateTransaction($data);
                $this->app->redirect($this->app->url('import'));
            }
        }
    }


}
