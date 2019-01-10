<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * this class for authentification user logging
 */
class Auth extends CI_Controller{
  function __construct(){
    parent::__construct();
    $this->load->helper('url');
    $this->load->model('Mauth', 'mauth');
  }

  public function login(){

    // if we are already get session to login
    if ($this->session->userdata('isLogin') === TRUE) {
      redirect();

    // this is block for condition that we aren't get session to login
    } else {
      $this->load->helper('form');
      $this->load->library('form_validation');

      $this->form_validation->set_rules('email', 'Email', 'required|callback_checkingEmail|valid_email');
      $this->form_validation->set_rules('password', 'Password', 'required|callback_checkingPassword');

      // this is block for if our form validation running unseccessly
      if ($this->form_validation->run() === FALSE) {
        $this->load->view('include/header');
        $this->load->view('login');
        $this->load->view('include/footer');

        // this block for giving session login if all the requiry is complete
      } else {
        $userType = $this->mauth->getData(array('email' => $this->input->post('email')),
          array('userTypeField' => 'user_type'), TRUE);
        $type = $userType->user_type;

        $userId = $this->mauth->getData(array('email' => $this->input->post('email')),
          array('userIdField' => 'user_id'), TRUE);
        $id = $userId->user_id;

        $newerId = $this->mauth->getData(array('email' => $this->input->post('email')),
          array('newerField' => 'newer'), TRUE);
        $newer = $newerId->newer;

        $data = array(
          'uId'     => $id,
          'uType'   => $type,
          'uNew'    => $newer, // store owner and admin
          'isLogin' => TRUE
        );
        $this->session->set_userdata($data);
        redirect();
      }
    }
  }

  // this function for unset sessio
  public function logout(){
    // $this->session->unset_userdata('UId');
    // $this->session->unset_userdata('uType');
    $this->session->sess_destroy();
    redirect('home', 'refresh');
  }

  // this function for checking email
  public function checkingEmail($email){
    $user = $this->mauth->getData(array('email' => $email),
      array('usernameField' => 'email'), TRUE);

    if (isset($user)) {
      return TRUE;
    } else {
      $this->session->set_flashdata('error', 'Wrong email!');
    }
  }
  // this function for checking password
  public function checkingPassword($password){
    if($this->checkingEmail($this->input->post('email'))){
      $user = $this->mauth->getData(array('email' => $this->input->post('email')),
        array('passwordField' => 'password'), TRUE);

      if (password_verify($password, $user->password)) {
        return TRUE;
      } else {
        $this->session->set_flashdata('error', 'Wrong password!');
        return FALSE;
      }
    } else {
      $this->session->set_flashdata('error', 'Wrong email!');
    }
  }

  // public function inputDummyUser(){
  //   $this->mauth->createDummyUser();
  // }

  public function regis(){
    $this->load->helper('form');
    $this->load->library('form_validation');

    $this->form_validation->set_rules('uname', 'Username', 'required|callback_checkingUnameReg');
    $this->form_validation->set_rules('email', 'Email', 'required|callback_checkingEmailReg|valid_email');

    if ($this->session->userdata('uType') == 1) {
      $this->form_validation->set_rules('phone', 'Phone', 'required');
      $this->form_validation->set_rules('adminType', 'Admin Authority', 'required');

      if ($this->form_validation->run() === FALSE) {
        $this->load->view('include/admin/header');
        $this->load->view('include/admin/left-sidebar');
        $this->load->view('register');
        $this->load->view('include/admin/footer');
      } else{
        $this->mauth->regis();
        redirect();
      }
    } elseif ($this->session->userdata('uType') == 2) {
      $this->form_validation->set_rules('company_name', 'Company name', 'required');
      $this->form_validation->set_rules('add', 'Address', 'required');
      $this->form_validation->set_rules('sub_district', 'Sub district', 'required');
      $this->form_validation->set_rules('city', 'City', 'required');
      $this->form_validation->set_rules('province', 'Province', 'required');
      $this->form_validation->set_rules('pCode', 'Postcode', 'required');

      if($this->form_validation->run() === FALSE){
        $this->load->view('include/header');
        $this->load->view('register');
        $this->load->view('include/footer');
      } else{
        $this->mauth->regis();
        redirect('home/adminStores');
      }
    } elseif ($this->session->userdata('uType') == NULL) {
      $this->form_validation->set_rules('password', 'Password', 'required');
      $this->form_validation->set_rules('conf_pass', 'Confirm Password', 'required|matches[password]');
      $this->form_validation->set_rules('fname', 'First name', 'required');
      $this->form_validation->set_rules('lname', 'Last name', 'required');
      $this->form_validation->set_rules('gender', 'Gender', 'required');
      $this->form_validation->set_rules('checkbox', 'Checkbox', 'required');

      if ($this->form_validation->run() === FALSE) {
          $this->load->view('include/header');
          $this->load->view('register');
          $this->load->view('include/footer');
      } else {
        $this->mauth->regis();
        redirect('auth/login');
      }
    }
  }

  public function checkingUnameReg($username){
    $user = $this->mauth->getData(array('username' => $username),
      array('unameRegField' => 'username'), TRUE);

    if(isset($user)){
      $this->session->set_flashdata('error','Username has already been taken');
      return FALSE;
    }else{
      return TRUE;
    }
  }

  public function checkingEmailReg($email){
    if ($this->checkingUnameReg($this->input->post('uname'))) {
      $user = $this->mauth->getData(array('email' => $email),
        array('emailRegField' => 'email'), TRUE);

      if(isset($user)){
        $this->session->set_flashdata('error', 'Email has already been taken');
        return FALSE;
      }else{
        return TRUE;
      }
    }else {
      $this->session->set_flashdata('error', 'Username has already been taken');
      return FALSE;
    }
  }

  public function checkForgot(){
    $this->load->helper('form');
    $this->load->library('form_validation');

    $this->form_validation->set_rules('search', 'Search', 'required|callback_checkingUnameMail');
    $this->form_validation->set_rules('pass', 'Password', 'required');

    if($this->form_validation->run() === FALSE){
      $this->load->view('include/header');
      $this->load->view('forgot_pass');
      $this->load->view('include/footer');
    }else{
      $uname = $this->mauth->getData(array('username' => $this->input->post('search')),
        array('usernameField' => 'username'), TRUE);


      $mail = $this->mauth->getData(array('email' => $this->input->post('search')),
        array('emailField' => 'email'), TRUE);


      if(isset($uname)){
          $username = $uname->username;
          $this->mauth->forgotPass(NULL, $username);
      }
      if (isset($mail)) {
        $email = $mail->email;
        $this->mauth->forgotPass($email, NULL);
      }
      redirect('auth/login');
    }
  }

  public function checkingUnameMail($search){
    $uname = $this->mauth->getData(array('username' => $search),
      array('usernameField' => 'username'), TRUE);
    $mail = $this->mauth->getData(array('email' => $search),
      array('emailField' => 'email'), TRUE);

    if (isset($uname) || isset($mail)) {
      $this->session->set_flashdata('updatePass', TRUE);
      return TRUE;
    }else{
      $this->session->set_flashdata('wrongUnameOrPass', 'No search results');
      return FALSE;
    }
  }

  public function completing_profile()
  {
    $this->load->helper('form');
    $this->load->library('form_validation');

    $this->form_validation->set_rules('first_name', 'First Name', 'required');
    $this->form_validation->set_rules('last_name', 'Last Name', 'required');
    $this->form_validation->set_rules('gender', 'Gender', 'required');
    $this->form_validation->set_rules('address', 'Address', 'required');
    $this->form_validation->set_rules('phone', 'Phone', 'required');
    $this->form_validation->set_rules('postcode', 'Post Code', 'required');
    $this->form_validation->set_rules('province', 'Province', 'required');
    $this->form_validation->set_rules('city', 'City', 'required');
    $this->form_validation->set_rules('new_pass', 'Password', 'required');

    if ($this->form_validation->run() == FALSE) {
      $this->load->view('include/header');
      $this->load->view('register');
      $this->load->view('include/footer');
    } else {
      $data_customer = array(
        'id'=>'',
        'id_userlogin'=>$this->session->userdata('uId'),
        'first_name'=>$this->input->post('first_name'),
        'last_name'=>$this->input->post('last_name'),
        'address'=>$this->input->post('address'),
        'sub_district'=>$this->input->post('sub_district'),
        'city'=>$this->input->post('city'),
        'province'=>$this->input->post('province'),
        'postcode'=>$this->input->post('postcode'),
        'gender'=>$this->input->post('gender'),
        'phone'=>$this->input->post('phone')
      );

      $newer = array(
        'password'=>password_hash($this->input->post('new_pass'), PASSWORD_DEFAULT),
        'newer'=>'0'
      );

      $this->mauth->complete_profile($data_customer);
      $this->mauth->update_newer($newer);
      $this->session->set_userdata('uNew','0');
      redirect();
    }
    
  }



  // public function checkingNewPass(){
  //   if (condition) {
  //     // code...
  //   }
  // }

  // public function coba(){
  //   $read = $this->mauth->getData(array('username' => 'superAdmin'), array('emailField' => 'email'), TRUE);
  //   print_r($read);
  // }

}
