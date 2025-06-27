<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CuAccount extends My_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->is_customer(1,$this->CUST_LOGIN);
		$this->load->model('Customer_model');
		$this->load->model('Country_model');

		$this->upload_path = 'documents/customer/'.$this->userid.'/';
	}


	public function edit() {
		$customer = $this->Customer_model->get_customer(['id' => $this->userid]);
		if(!$customer){
			$this->sendFlashMsg(0,'Customer data not found', 'customer');
		}

		$phonecodes = $this->Country_model->get_phonecodes();
		
		$data = [];
		$data['template'] 	= 'account/acc_edit';
		$data['title'] 		= "Profile";
		$data['customer'] 	= $customer;
		$data['phonecodes'] 	= $phonecodes;
		$this->load->view('default', $data);
	}	

	public function update(){
		
		//start validation
		$this->form_validation->set_rules('first_name', 'First Name', 'required|alpha|max[100]');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required|alpha|max[100]');
		$this->form_validation->set_rules('country_code', 'Country code', 'required|exists[z_countries.phonecode]');
		$this->form_validation->set_rules('mobile', 'Mobile', 'required|regex_match[/^[0-9]{10}$/]');
		$this->form_validation->set_rules('location', 'Location', 'required|max[1000]');

		if ($this->form_validation->run() == FALSE)
        {	
        	sendResponse(0, validation_errors());
        }

        //Start File upload  x file validation
        $customer = $this->Customer_model->get_customer(['id' => $this->userid]);
        $file_name  =  $customer['profile_picture'];

		if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ''){
			$config['upload_path'] = $this->upload_path;
	        $config['allowed_types'] = 'jpg|jpeg|png';
	        $config['max_size'] = (1*1024); //1MB
	        $config['remove_spaces'] = TRUE;

       		$file_name = time().'-'.mt_rand(10000, 99999);
			$config['file_name'] = $file_name;

	        $this->load->library('upload', $config);

	        if (!is_dir( $this->upload_path ))
		    {	
		    	mkdir( $this->upload_path , 0777, true);		        
		    }

        	if (!$this->upload->do_upload('file')) {
	            $error = $this->upload->display_errors();
	            sendResponse(0, $error);
	        } 

	        $image_data = $this->upload->data();

	        $file_name  =  $this->upload_path.$image_data['file_name'];

	        if($customer['profile_picture']){
	        	$upload_path = './' . $customer['profile_picture'];
	        	if(file_exists($upload_path) ){
	        		unlink($upload_path);
	        	}				    
	        }

		} // end if file upload
		else if($this->input->post('prev_file') == "" ){
			$file_name  =  '';

        	if($customer['profile_picture']){
        		$upload_path = './' . $customer['profile_picture'];
	        	if(file_exists($upload_path)){
	        		unlink($upload_path);
	        	}
        	}
		}
        //end validation

        //Store
        $data = [];
        $data['first_name'] 		=  safe_trim($this->input->post('first_name',TRUE));
        $data['last_name'] 			=  safe_trim($this->input->post('last_name',TRUE));
        $data['mobile'] 			=  safe_trim($this->input->post('mobile',TRUE));
        $data['country_code'] 		=  safe_trim($this->input->post('country_code',TRUE));
        $data['location'] 			=  safe_trim($this->input->post('location',TRUE));
        $data['profile_picture'] 	=  $file_name;
		$data['updated_by'] 		= $this->userid;


		$where = ['id' => $this->userid];
		$insert = $this->Customer_model->update_customer($where,$data);
		if($insert){

			$customer = $this->Customer_model->get_customer($where);
			if($customer){
				$customer['role'] = 'customer';
				$this->session->set_userdata( $this->user, $customer );
			}

			$this->session->set_flashdata('message', array('status' => 1, 'message' => 'Profile updated successfully' ));

			sendResponse(1,'Success');
		}else{
			sendResponse(0,' Failed to update profile');
		}
	}//end update

	public function changePassword(){
		$data 				= [];
		$data['template'] 	= 'account/change_pass';
		$data['title'] 		= "Change Password";
		$this->load->view('default', $data);
	}

	public function changePasswordDb(){
		//Start validation
		$this->form_validation->set_rules('old_pass', 'Current Password', 'required');
		$this->form_validation->set_rules('pass_1', 'Password', 'required|min_length[8]');
		$this->form_validation->set_rules('pass_2', 'Confirm Password', 'required|matches[pass_1]');

		if ($this->form_validation->run() == FALSE)
        {	
        	sendResponse(0, validation_errors());
        }

		$pass_1 	=  $this->input->post('pass_1',TRUE);
		$old_pass 	=  $this->input->post('old_pass',TRUE);
		$old_pass 	=  hash('sha256', $old_pass);

		$customer = $this->Customer_model->get_customer(['id' => $this->userid, 'password' => $old_pass, 'status' => 1]);
		if(!$customer){
			sendResponse(0, 'Invalid old password');
		}
        //validate token with email

		//set new customer password
		$data = array('password' => hash('sha256', $pass_1));
		$cust_where = ['id' => $this->userid];
		$this->Customer_model->update_customer($cust_where,$data);

		$this->session->set_flashdata('message', array('status' => 1, 'message' => 'Password changed successfully' ));
		sendResponse(1, 'Password changed successfully');	
	}//end function



}

?>