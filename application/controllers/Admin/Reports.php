<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reports extends My_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->is_logged_in(1);

		if($this->is_customer()){
			redirect($this->CUST_LOGIN);
		}

		$this->load->model('User_model');
		$this->load->model('Customer_model');
		$this->load->model('Company_model');
		$this->load->model('Project_model');
		$this->load->model('Complaint_model');
		$this->load->model('ComplaintHistory_model');
		$this->load->model('Department_model');
		$this->load->model('Notification_model');
		
	}

	public function index() {
		$projects = [];
		$projects = $this->Project_model->get_projects();
		$company = $this->Company_model->get_all_company(['status' => 1]);
		//dd($company);
		$data = [];
		$data['template'] 	= 'reports/reports_list';
		$data['title'] 		= "Reports";
		$data['data'] 		= '';
		$data['projects'] 	= $projects;
		$data['company'] 	= $company;
		$this->load->view('default', $data);
	}

    public function get_companies() {
        $term = $this->input->get('term'); // Get search term
        $this->db->like('name', $term);
        $this->db->select('name,id');
        $this->db->limit(10); // Limit the results
        $query = $this->db->get('company'); 
        
        $result = $query->result_array();
        $names = [];
        
        foreach ($result as $row) {
            $names[] = ['label' => $row['name'], 'value' => $row['id']];
        }
    
        echo json_encode($names);
    }
    

	public function list() {
        $year = $this->input->get('year'); 
        $month = $this->input->get('month'); 
        $from_date = $this->input->get('from_date');
		$to_date   = $this->input->get('to_date');
        // dd($year);
		if($this->role == 'admin' || $this->role == 'super_admin'){
			$params = $this->searchParam(['status','ticket_no','company_id','complaint_type','classification']);

		} else{
			$params = $this->searchParam(['c.status' => 'status','ticket_no','company_id','complaint_type','classification','action']);
		}	
		//dd($params);

		$draw 		= $params['draw'];
		$startrow 	= $params['startrow'];
		$rowperpage = $params['rowperpage'];
		$whereArr 	= $params['where'];
		$likeArr 	= $params['like'];
        // Add Year and Month filtering
        if (!empty($year)) {
            $whereArr['YEAR(created_at)'] = $year;  // Filter by year
        }
        if (!empty($year) && !empty($month)) {
            $whereArr['YEAR(created_at)'] = $year;  // Filter by year
            $whereArr['MONTH(created_at)'] = $month; // Filter by month
        }

        // Apply from_date and to_date filter directly in controller (using WHERE with raw condition)
		if (!empty($from_date) && !empty($to_date)) {
		    $whereArr['DATE(created_at) >='] = $from_date;
		    $whereArr['DATE(created_at) <='] = $to_date;
		} elseif (!empty($from_date)) {
		    $whereArr['DATE(created_at) >='] = $from_date;
		} elseif (!empty($to_date)) {
		    $whereArr['DATE(created_at) <='] = $to_date;
		}
		
		$action_taken = '';
		if($this->role == 'admin' || $this->role == 'super_admin'){

			## Total number of records without filtering
			$allres  = $this->Complaint_model->count();
			$totalRecords = $allres;

			## Total number of records with filtering
			$allres  = $this->Complaint_model->count($whereArr,$likeArr);
			$totalRecordwithFilter = $allres;

			$columns = 'id,ticket_no,company_id,complaint_type,description,customer_id,status,created_by,created_at,updated_at,classification';
			$list = $this->Complaint_model->get_complaints($whereArr,$columns,$startrow,$rowperpage , $likeArr);
			//dd($list);
		}else{
			$whereArr_a['h.emp_id'] = $this->userid;
            $whereArr_a['h.type'] = 'assign';
            $assignWhere = ['h.emp_id' => $this->userid,'h.type' => 'assign'];

            $columns1 = 'c.id,c.ticket_no,c.company_id,c.complaint_type,c.description,c.customer_id,c.status,c.created_by,c.created_at,c.updated_at,classification';
            $list1 = $this->Complaint_model->get_complaints_for_emp_new($whereArr_a,$columns1,$startrow,$rowperpage , $likeArr);
            // dd($this->pq());
            if (!empty($list1)) {
			    foreach ($list1 as &$item1_a) {
			        $item1_a['assign'] = 1;
			    }
			}

            $columns2 = 'id,ticket_no,company_id,complaint_type,description,customer_id,status,created_by,created_at,updated_at,classification';
			$list2 = $this->Complaint_model->get_complaints($whereArr,$columns2,$startrow,$rowperpage , $likeArr);
			if (!empty($list2)) {
			    foreach ($list2 as &$item2_a) {
			        $item2_a['assign'] = 0;
			    }
			}

            
            $list = array_merge($list1, $list2);
            ## Total number of records without filtering
            $totalRecords = $this->Complaint_model->get_complaints_for_emp_new($assignWhere,'c.id','','', $likeArr);
            $totalRecords = count($totalRecords);

            ## Total number of records with filtering
            
            $allres = $this->Complaint_model->get_complaints_for_emp_new($whereArr,'c.id',FALSE,FALSE, $likeArr);
            $totalRecordwithFilter = count($allres);
	  			
		}

		$complaint_types = complaint_types();
  		$classifications = classifications();

  		
		// dd($this->pq());
		$status_list = complaint_status_list();
		foreach ($list as $key => $value) {
			$unset = '';

			$customer = $this->Customer_model->get_customer_details(['c.id' => $value['customer_id']]);

			if($customer){
				$company = $this->Company_model->get_company(['name' => $customer['company_name']]);
		        if(!$company){
		            $this->sendFlashMsg(0,'Company data not found', 'company');
		        }
				//d($customer['company_name']);
				$list[$key]['company'] 	= cap($customer['company_name']);

				$assigned = $this->ComplaintHistory_model->get_complaint_history(['complaint_id' => $value['id']],'created_by',['type' => 'assign']);
					//dd($assigned);
				if(!empty($assigned)){
				    $assigned_admin = $this->User_model->get_user(['id' => $assigned['created_by']],'id,first_name,last_name');
				    $list[$key]['assigned_emp'] = $assigned_admin['first_name'].' '.$assigned_admin['last_name'];
				}else{
					$list[$key]['assigned_emp'] = '-';
				}

			}

			// $list[$key]['status'] 	= $status_list[$value['status']];
			$list[$key]['complaint_type'] 	= $complaint_types[$value['complaint_type']];
			$list[$key]['description'] 	= $value['description'];
			
			$list[$key]['classification'] = '';

			if(isset($classifications[$value['classification']])){
				$list[$key]['classification'] 	= $value['classification'].' - '.$classifications[$value['classification']];
			}
			
            $ticket_rise_date = new DateTime(date('Y-m-d', strtotime($value['created_at']))); // Convert to date only
			$current_date = new DateTime(date('Y-m-d')); // Current date without time
			$days_difference = $ticket_rise_date->diff($current_date)->days;
			$list[$key]['days'] 	= $days_difference;
			$list[$key]['created_at'] 	= custDate($value['created_at']);
			$list[$key]['updated_at'] 	= custDate($value['updated_at']);

            $list[$key]['completed_at'] = '';

			//if closed, add completion date
			if($value['status'] == 4){
				$whereHistory = array(
					'order_by' => ['id' => 'desc'],
					'limit' => 1,
				);
				// $history = $this->ComplaintHistory_model->get_complaint_history(['complaint_id' =>$value['id'], 'type'=>'remark','assigned_by' => ''],'*',$whereHistory);
				$history = $this->ComplaintHistory_model->get_complaint_history(['complaint_id' =>$value['id'], 'type'=>'remark'],'*',$whereHistory);
				//dd($history);
				if($history){
					$completed_date = new DateTime(date('Y-m-d', strtotime($history['created_at']))); // Completion date (date only)
					$days_difference = $ticket_rise_date->diff($completed_date)->days; 
                    $list[$key]['days'] 	= $days_difference;  
					$list[$key]['completed_at'] 	= custDate($history['created_at']);
				}
			}
			/*if($unset){
				unset($list[$key]);
			}*/
			//dd($value);
			if ($value['created_by'] != '') {
				$created_by_arr = $array = explode("_", $value['created_by']);
			}else{
				$created_by_arr = array();
			}
			//dd($created_by_arr);
			if ($created_by_arr[0] == 'c') {
				$whereArr 	= ['id' => $created_by_arr[1]];
				$columns 	= 'id,first_name,last_name';
				$customer 	= $this->Customer_model->get_customer($whereArr,$columns);
				//dd($customer);
				$created_by = $customer['first_name'].' '.$customer['last_name'];
			}elseif ($created_by_arr[0] == 'sa') {
				$whereArr 	= ['id' => $created_by_arr[1]];
				$columns 	= 'id,first_name,last_name';
				$superAdmin = $this->User_model->get_user($whereArr,$columns);
				//dd($superAdmin);
				$created_by = $superAdmin['first_name'].' '.$superAdmin['last_name'];
				
			} else{
				$created_by = '';
			}	
			$list[$key]['created_by'] = $created_by;
		}
		//dd($list);
		// $list = array_values($list);

		/*if($action_taken){
			$totalRecords = $totalRecordwithFilter = count($list);
		}*/
		if ($this->role == 'admin' || $this->role == 'employee') {
			$login_user = "FIND_IN_SET('".$this->userid."', employees) > 0";
			$company_n = $this->Company_model->get_all_company('','','','', '', '',$login_user);
			//dd($company_n);
			if (empty($company_n)) {
				//dd($list);
				//$list=array();
				//$totalRecords=0;
				//$totalRecordwithFilter=0;
				$company_names = [];
				$filtered_array = array_filter($list, function($element) use ($company_names) {
				    return isset($element['assign']) && $element['assign'] == 1 || in_array($element, $company_names);
				});

				$response = array(
				 	"draw" 					=> intval($draw),
				 	"totalRecords" 			=> $totalRecords,
				 	"totalRecordwithFilter" => $totalRecordwithFilter,
				 	"aaData" 				=> $filtered_array
				);
			}else{
				//dd($list);
				foreach ($company_n as $item) {
				    $company_names[] = cap($item['name']);
				}
				//dd($list);
				$filtered_array = array_filter($list, function($element) use ($company_names) {
				    return in_array($element['company'], $company_names) || (isset($element['assign']) && $element['assign'] == 1);
				});

				$filtered_array = array_values(filter_unique_ticket_no($filtered_array));
				//$filtered_array = array_values($filtered_array);
				//$totalRecords = count($filtered_array);
				//$totalRecordwithFilter = count($filtered_array);
				$response = array(
				 	"draw" 					=> intval($draw),
				 	"totalRecords" 			=> $totalRecords,
				 	"totalRecordwithFilter" => $totalRecordwithFilter,
				 	"aaData" 				=> $filtered_array
				);
			}
		}else{
			$response = array(
			 	"draw" 					=> intval($draw),
			 	"totalRecords" 			=> $totalRecords,
			 	"totalRecordwithFilter" => $totalRecordwithFilter,
			 	"aaData" 				=> $list
			);
		}
		//dd($response);

		sendResponse(1, 'success', $response);
	}

	function export()
    {	
    	$this->load->model('Equipment_model');
        $year = $this->input->get('year'); 
        $month = $this->input->get('month'); 
		if($this->role == 'admin' || $this->role == 'super_admin'){
			$params = $this->searchParam(['status','ticket_no','company_id','complaint_type','classification']);

		} else{
			$params = $this->searchParam(['status','ticket_no','company_id','complaint_type','classification','action']);
		}

		$whereArr 	= $params['where'];
		$likeArr 	= $params['like'];
        // Add Year and Month filtering
        if (!empty($year) && !empty($month)) {
            $whereArr['YEAR(created_at)'] = $year;  // Filter by year
            $whereArr['MONTH(created_at)'] = $month; // Filter by month
        }
		if($this->role == 'admin' || $this->role == 'super_admin'){
			$columns = 'id,ticket_no,company_id,complaint_type,description,customer_id,status,created_by,created_at,updated_at,classification';
			$list = $this->Complaint_model->get_complaints($whereArr,$columns,FALSE,FALSE, $likeArr);
			/*set column names*/
        	$table_columns = array('Sr No', 'Ticket ID','Company','Complaint Type','Description','Status','Complaint Date','Update on Ticket','Completion date','days');
		}else{
			$whereArr['h.emp_id'] = $this->userid;
			$whereArr['h.type'] = 'assign';
			$columns = '*';
			$list = $this->Complaint_model->get_complaints_for_emp_new($whereArr,$columns,FALSE,FALSE, $likeArr);

			/*set column names*/
        	$table_columns = array('Sr No', 'Ticket ID','Company','Complaint Type','Description','Status','Complaint Date','Update on Ticket','Completion date','days');
		}

		// dd($this->pq());
		$status_list = complaint_status_list();
		foreach ($list as $key => $value) {
			//customer data
			$customer = $this->Customer_model->get_customer_details(['c.id' => $value['customer_id']]);
			if($customer){
				$list[$key]['customer'] 	= ucfirst($customer['first_name']).' '.ucfirst($customer['last_name']);
				$list[$key]['company'] 	= $customer['company_name'];
			}else{
				$list[$key]['customer'] ='';
				$list[$key]['company'] 	='';
			}

			//equipment data
			//$equiDetails = $this->Equipment_model->get_equipemnt_details_by_project(['p.ga_no' => $value['ga_no']],'e.name,p.model');

			// $list[$key]['equipment'] = ($equiDetails) ? $equiDetails['name'] : '';
			// $list[$key]['model'] = ($equiDetails) ? $equiDetails['model'] : '';

			$list[$key]['status'] 	= $status_list[$value['status']];

            $ticket_rise_date = new DateTime(date('Y-m-d', strtotime($value['created_at']))); // Convert to date only
			$current_date = new DateTime(date('Y-m-d')); // Current date without time
			$days_difference = $ticket_rise_date->diff($current_date)->days;
			$list[$key]['days'] 	= $days_difference;

			$list[$key]['created_at'] 	= custDate($value['created_at']);
			$list[$key]['updated_at'] 	= custDate($value['updated_at']);
            

			$list[$key]['completed_at'] = '';

			//if closed, add completion date
			if($value['status'] == 4){
				$whereHistory = array(
					'order_by' => ['id' => 'desc'],
					'limit' => 1,
				);
				// $history = $this->ComplaintHistory_model->get_complaint_history(['complaint_id' =>$value['id'], 'type'=>'remark','assigned_by' => ''],'*',$whereHistory);
				$history = $this->ComplaintHistory_model->get_complaint_history(['complaint_id' =>$value['id'], 'type'=>'remark'],'*',$whereHistory);
				if($history){
                    $completed_date = new DateTime(date('Y-m-d', strtotime($history['created_at']))); // Completion date (date only)
					$days_difference = $ticket_rise_date->diff($completed_date)->days; 
                    $list[$key]['days'] 	= $days_difference;  
					$list[$key]['completed_at'] 	= custDate($history['created_at']);
				}
			}
		}
		// dd($list);
// die;
		// dd($list);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        
        $column = 1;
        foreach ($table_columns as $field) {
            $sheet->setCellValueByColumnAndRow($column, 1, $field);
            $column++;
        }
        /*end set column names*/

        $excel_row = 2; //now from row 2

        $complaint_types = complaint_types();
        $classifications = classifications();

        foreach ($list as $key=>$row) {

        	$class_e = isset($classifications[$row['classification']]) ? $classifications[$row['classification']] : '';
            //$feedback = ($row['feedback'] == 1) ? 'Yes' : '-';
        	$col_count = 1;

            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, ($key+1));
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $row['ticket_no']);
            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, clean_cell_formula($row['customer']));
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, clean_cell_formula($row['company']));
            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, clean_cell_formula($row['ga_no']));
            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, clean_cell_formula($row['equipment'])); //Equipment
            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, clean_cell_formula($row['model'])); //model
            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, clean_cell_formula($row['cust_equipment_no']));
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $complaint_types[$row['complaint_type']]);
            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $class_e);

            // if($this->role == 'admin' || $this->role == 'super_admin'){
           	//  	$sheet->setCellValueByColumnAndRow($col_count++, $excel_row,$feedback);
            // }

            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $row['from_date']);
            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $row['to_date']);
            //$sheet->setCellValueByColumnAndRow($col_count++, $excel_row, clean_cell_formula($row['order_no']));
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, clean_cell_formula($row['description']));
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $row['status']);
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $row['created_at']);
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $row['updated_at']);
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $row['completed_at']);
            $sheet->setCellValueByColumnAndRow($col_count++, $excel_row, $row['days']);
            $excel_row++;
        }
        $writer = new Xlsx($spreadsheet);
        // $writer->save('hello world.xlsx');
        // header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ticket_list.xlsx"');
        $writer->save('php://output');
    }//end export

}

?>