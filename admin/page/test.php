<?php

class page_test extends Page {

    public $title='Test';

    function init() {
        parent::init();

    // $state = [
    //     'Andaman & Nicobar Islands',
    //     'Andhra Pradesh',
    //     'Arunachal Pradesh',
    //     'Assam',
    //     'Bihar',
    //     'Chandigarh',
    //     'Chhattisgarh',
    //     'Dadra & Nagar Haveli',
    //     'Daman & Diu',
    //     'Delhi',
    // 'Goa',
    // 'Gujarat',
    // 'Haryana',
    // 'Himachal Pradesh',
    // 'Jammu & Kashmir',
    // 'Jharkhand',
    // 'Karnataka',
    // 'Kerala',
    // 'Lakshadweep',
    // 'Madhya Pradesh',
    // 'Maharashtra',
    // 'Manipur',
    // 'Meghalaya',
    // 'Mizoram',
    // 'Nagaland',
    // 'Odisha',
    // 'Puducherry',
    // 'Punjab',
    // 'Rajasthan',
    // 'Sikkim',
    // 'Tamil Nadu',
    // 'Telangana',
    // 'Tripura',
    // 'Uttar Pradesh',
    // 'Uttarakhand',
    // 'West Bengal'
    // ];

        foreach ($this->add('Model_FifoSell') as $m) {
            $tra = $this->add('Model_Transaction')->load($m['transaction_id']);
            $m['client_id'] = $tra['client_id'];
            $m['company_id'] = $tra['company_id'];
            $m['buy_price'] = $tra['buy_value'];
            $m->saveAndUnload();
        }
    }
}
