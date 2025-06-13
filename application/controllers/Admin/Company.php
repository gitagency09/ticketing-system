<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends My_Controller 
{
    public function __construct()
    {
        parent::__construct();

        if(!$this->is_admin()){
            redirect($this->ADMIN_LOGIN);
        }

        $this->load->model('Company_model');
    }

    public function index() {
        $data = [];
        $data['template']   = 'company/company_list';
        $data['title']      = "Company List";
        $this->load->view('default', $data);
    }

    public function list() {        
        $params = $this->searchParam(['status'],['name','location']);

        $draw       = $params['draw'];
        $startrow   = $params['startrow'];
        $rowperpage = $params['rowperpage'];
        $whereArr   = $params['where'];
        $likeArr    = $params['like'];

        $wherein = [];

        ## Total number of records without filtering
        $allres  = $this->Company_model->count();
        $totalRecords = $allres;


        ## Total number of records with filtering
        $allres  = $this->Company_model->count($whereArr,$likeArr,$wherein);
        $totalRecordwithFilter = $allres;

        $columns = '*';
        $list = $this->Company_model->get_all_company($whereArr,$columns,$startrow,$rowperpage , $likeArr,$wherein);

        foreach ($list as $key => $value) {
            $list[$key]['name']       = cap($value['name']);
            $list[$key]['created_at'] = custDate($value['created_at']);
        }

        // dd($list);

        $response = array(
            "draw"                  => intval($draw),
            "totalRecords"          => $totalRecords,
            "totalRecordwithFilter" => $totalRecordwithFilter,
            "aaData"                => $list
        );

        sendResponse(1, 'success', $response);
    }

    public function create() {
        $data = [];
        $roles = ['admin', 'employee']; // Add the roles you want to retrieve
        $admin = $this->User_model->get_users('', '*', '', '', '', '', $roles);
        // echo "<pre>";
        // print_r($admin);
        // exit;
        $data['data']       = $admin;
        $data['template']   = 'company/company_add';
        $data['title']      = "Add Company";
        $this->load->view('default', $data);
    }

    public function store(){

        $this->form_validation->set_rules('name', 'Name', 'required|is_unique[company.name]');

        // $this->form_validation->set_rules('domain', 'Domain', array(
        //     'required',
        //     array(
        //         'domain_callable',
        //         function($str)
        //         {
        //             return true;
        //         }
        //     ),
        // ));

        $this->form_validation->set_rules('location', 'Location', array('required'));
        //$this->form_validation->set_message('domain_callable', 'Invalid domain');

        if ($this->form_validation->run() == FALSE)
        {   
            sendResponse(0, validation_errors());
        }

        $name                   =  trim($this->input->post('name',TRUE));
        //$domain                 =  trim($this->input->post('domain',TRUE));
        $location               =  trim($this->input->post('location',TRUE));
        $employee               =  $this->input->post('Employee_add');
        // echo "<pre>";
        // print_r($employee_comma);
        // exit;
        $employee_comma = implode(",", $employee);
        // $isInvalid = $this->validateDomain($domain);
        // if($isInvalid){
        //     sendResponse(0, $isInvalid);
        // }

        //Store
        $data = [];
        $data['name']       = $name;
        //$data['domain']     = $domain;
        $data['location']   = $location;
        // $data['employees']   = $employee_comma;
        $data['status']     = 1;
        $data['created_by'] = $this->userid;
        $data['created_at'] = getDt();

        $insert = $this->Company_model->add_company($data);

        if ($insert) {
            // Insert mappings into company_manager_mapping table
            if (!empty($employee)) {
                foreach ($employee as $user_id) {
                    $mapping = [
                        'company_id' => $insert,  // Assuming add_company returns the inserted company_id
                        'user_id' => $user_id
                    ];
                    $this->db->insert('company_manager_mapping', $mapping);
                }
            }

            $this->session->set_flashdata('message', array('status' => 1, 'message' => 'Company created successfully'));

            sendResponse(1, 'Success');
        } else {
            sendResponse(0, 'Failed to create company');
        }
        
    }//end store dept


    public function view($companyId) {
        // Fetch the company
        $company = $this->Company_model->get_company(['id' => $companyId]);
        if (!$company) {
            $this->sendFlashMsg(0, 'Company data not found', 'company');
        }

        // Fetch employees from the mapping table
        $this->db->select('u.*');
        $this->db->from('company_manager_mapping cmm');
        $this->db->join('users u', 'cmm.user_id = u.id');
        $this->db->where('cmm.company_id', $companyId);
        $employees = $this->db->get()->result_array();

        // Prepare employee names string
        $first_names = '';
        foreach ($employees as $user) {
            $first_names .= $user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['role'] . '), ';
        }

        $first_names = rtrim($first_names, ', ');

        // Prepare view data
        $data = [];
        $data['template'] = 'company/company_view';
        $data['title'] = "View Company";
        $data['data'] = $company;
        $data['employees_array'] = $first_names;

        // Load the view
        $this->load->view('default', $data);
    }

    public function edit($companyId) {
        $company = $this->Company_model->get_company(['id' => $companyId]);
        if(!$company){
            $this->sendFlashMsg(0,'Company data not found', 'company');
        }
        
        // Step 1: Get employee IDs from mapping table
        $this->db->select('user_id');
        $this->db->from('company_manager_mapping');
        $this->db->where('company_id', $companyId);
        $query = $this->db->get();
        $employeeIds = array_column($query->result_array(), 'user_id');

        // Step 2: Get employee details
        $admin = [];
        if (!empty($employeeIds)) {
            $admin = $this->User_model->get_users('', '*', '', '', '', '', '', $employeeIds);
        }

        // Step 3: Prepare employee names string
        $first_names = '';
        foreach ($admin as $user) {
            $first_names .= $user['first_name'] . ' ' . $user['last_name'] . ' [' . $user['id'] . '](' . $user['role'] . '), ';
        }
        $first_names = rtrim($first_names, ', ');

        

        $roles = ['admin', 'employee']; // Add the roles you want to retrieve
        $admin = $this->User_model->get_users('', '*', '', '', '', '', $roles);
        // echo "<pre>";
        // print_r($admin);
        // echo "<pre>";
        // print_r($company);
        // exit;
        //$data['data'] = $admin;
        //dd($company);

        $employeeIds = explode(',', $company['employees']);

        // Filter array1 to get only the employees whose IDs are in the $employeeIds array
        $matchedEmployees = array_filter($admin, function($employee) use ($employeeIds) {
            return !in_array($employee['id'], $employeeIds);
        });
        // echo "<pre>";
        // print_r($matchedEmployees);
        // exit;
        $data = [];
        $data['template']   = 'company/company_edit';
        $data['title']      = "Edit Company";
        $data['data']       = $company;
        $data['admin']       = $matchedEmployees;
        $data['first_names'] = $first_names;
        //dd($data);
        $this->load->view('default', $data);
    }   

    public function update($companyId){
        $_POST['companyId'] = $companyId;

        $this->form_validation->set_rules('companyId', 'Company id', 'required|integer|exists[company.id]');

        $this->form_validation->set_rules('name', 'Name', 'required|is_unique_except[company.name.'.$companyId.']', array('is_unique_except' =>'Company name must be unique.'));


        //$this->form_validation->set_rules('domain', 'Domain',  array('required') );
        $this->form_validation->set_rules('location', 'Location',  array('required') );
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[0,1]');


        if ($this->form_validation->run() == FALSE)
        {   
            sendResponse(0, validation_errors());
        }


        $name                   =  trim($this->input->post('name',TRUE));
        //$domain                 =  trim($this->input->post('domain',TRUE));
        $location               =  trim($this->input->post('location',TRUE));
        $status                 =  trim($this->input->post('status',TRUE));
      
        $employee = $this->input->post('Employee_add'); // New employees (expected to be an array)
        $already_emp = $this->input->post('already_emp'); // This might be a comma-separated string

        // If $already_emp is a comma-separated string, convert to array
        if (!is_array($already_emp)) {
            $already_emp = explode(',', $already_emp);
        }

        // Similarly, ensure $employee is an array
        if (!is_array($employee)) {
            $employee = explode(',', $employee);
        }

        $final_employee_ids = [];

        // Merge existing employees (if any)
        if (!empty($already_emp) && $already_emp[0] != '') {
            $final_employee_ids = array_merge($final_employee_ids, $already_emp);
        }

        // Merge newly added employees (if any)
        if (!empty($employee) && $employee[0] != '') {
            $final_employee_ids = array_merge($final_employee_ids, $employee);
        }

        // Remove duplicates
        $final_employee_ids = array_unique($final_employee_ids);


        // $isInvalid = $this->validateDomain($domain);
        // if($isInvalid){
        //     sendResponse(0, $isInvalid);
        // }
        //end validation
       
        //Store
        $data = [];
        $data['name']       = $name;
        //$data['domain']     = $domain;
        $data['location']   = $location;
        $data['status']     = $status;
        $data['updated_by'] = $this->userid;
        $data['created_at'] = getDt();

        $where = ['id' => $companyId];
        $update = $this->Company_model->update_company($where,$data);
        
        if ($update) {
            // Always delete existing mappings
            $this->db->where('company_id', $companyId)->delete('company_manager_mapping');

            // Insert the merged (final) employee mappings
            foreach ($final_employee_ids as $userId) {
                $this->db->insert('company_manager_mapping', [
                    'company_id' => $companyId,
                    'user_id' => $userId
                ]);
            }

            $this->session->set_flashdata('message', array('status' => 1, 'message' => 'Company updated successfully'));
            sendResponse(1, 'Success');
        } else {
            sendResponse(0, 'Failed to update company');
        }



    }//end store dept


    /*private function validateDomain($domain){

        $error = '';
        if (strpos($domain, 'https://') !== false || strpos($domain, 'http://') !== false ) {
            $error = 'Please remove protocol from domain name';
        }
        else if (strpos($domain, 'www.') !== false) {
            $error = 'Please remove www from domain name';
        }

        else if (!strpos($domain, '.') !== false ) {  // if dot does not exist
            $error = 'Invalid domain name';
        }

        else if(substr($domain, -1) == "."){  // if dot is a last character
            $error = 'Invalid domain name!';
        }

        else if (!preg_match('/^[a-zA-Z0-9-_\.]*$/', $domain)){  //if other than specified chars
            $error = 'Invalid domain name. Enter only alphanumeric and special chars like <b>. - _ </b> ';
        }else{
            $arr = explode(".", $domain);

            if (!preg_match('/^[a-zA-Z]*$/', end($arr) ) ){
                $error = 'Invalid domain name.';
            }
        }

        return $error;
    }*/

    
    public function migrate_company_employees_to_mapping() {
    // Fetch all companies
        $companies = $this->db->get('company')->result_array();

        foreach ($companies as $company) {
            $companyId = $company['id'];
            $employeeIds = explode(',', $company['employees']);

            foreach ($employeeIds as $userId) {
                $userId = trim($userId);
                if (!empty($userId)) {
                    // Check if mapping already exists to prevent duplicates
                    $exists = $this->db->get_where('company_manager_mapping', [
                        'company_id' => $companyId,
                        'user_id' => $userId
                    ])->row();

                    if (!$exists) {
                        $this->db->insert('company_manager_mapping', [
                            'company_id' => $companyId,
                            'user_id' => $userId
                        ]);
                    }
                }
            }
        }

        echo "Migration completed.";
    }

}

?>