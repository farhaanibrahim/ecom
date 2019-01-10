<?php defined('BASEPATH') or Exit('No direct script access allowed');
/**
 * this is class for authinfication
 */
class Mauth extends CI_Model{

  function __construct(){
    $this->load->database();
  }

  // public function createDummyUser(){
  //   $data = array(
  //     'username'      => 'admin',
  //     'password'      => password_hash('admin', PASSWORD_DEFAULT),
  //     'email'         => 'admin@keraton.com',
  //     'user_type'          => '2'
  //   );
  //   $this->db->insert('user_login',$data);
  // }

  public function getData($condition=NULL, $selection=NULL, $singleRowResult = FALSE){

    // if we are selecting some condition
    if ($condition != NULL) {
      foreach($condition as $key => $value){
        $this->db->where($key, $value);
      }
    }

    // if we are selection some fields
    if ($selection != NULL) {
      foreach ($selection as $key => $value) {
        $this->db->select($value);
      }
    }

    $query = $this->db->get('user_login');

    if ($singleRowResult === TRUE) {
      return $query->row();
    } else {
      return $query->result();
    }
  }

  public function regis(){
    if ($this->session->userdata('uType') == NULL) {
      $uType = 4;
      $newCS = 0;
      $dataUserLogin = array(
        'username'  => $this->input->post('uname'),
        'password'  => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
        'email'     => $this->input->post('email'),
        'user_type' => $uType,
        'newer'     => $newCS
      );
      $queryULoging = $this->db->insert('user_login', $dataUserLogin);

      $userId = $this->getData(array('username' => $this->input->post('uname')),
        array('userIdField' => 'user_id'), TRUE);
      $id = $userId->user_id;

      $dataCustomer = array(
        'id_userLogin'  => $id,
        'first_name'    => $this->input->post('fname'),
        'last_name'     => $this->input->post('lname'),
        'gender'        => $this->input->post('gender')
      );

      $queryCustomer = $this->db->insert('tm_customer', $dataCustomer);

      return array(
        'queryULoging'  => $queryULoging,
        'queryCustomer' => $queryCustomer
      );
    } elseif($this->session->userdata('uType') == 2) {
        $uType = 3;
        $newOwner = 1;
        $pass = 'store_owner_agm';
        $created = $this->session->userdata('uId');
        $dataUserLogin = array(
          'username' => $this->input->post('uname'),
          'password' => password_hash(($pass), PASSWORD_DEFAULT),
          'email'    => $this->input->post('emai;'),
          'type'     => $uType,
          'newer'    => $newOwner,
          'created'  => $created
        );
        $queryULoging = $this->db->insert('user_login', $dataUserLogin);

        $userId = $this->getData(array('username' => $this->input->post('uname')),
          array('userIdField' => 'user_id'), TRUE);
        $id = $userId->user_id;

        $dataStoreOwner = array(
          'id_userLogin' => $id,
          'new_owner'    => 1,
          'company_name' => $this->input->post('company_name'),
          'address'      => $this->input->post('add'),
          'sub_district' => $this->input->post('sub_district'),
          'city'         => $this->input->post('sub_district'),
          'province'     => $this->input->post('province'),
          'postcode'     => $this->input->post('pCode'),
          'phone1'       => $this->input->post('phone1'),
          'phone2'       => $this->input->post('phone2'),
          'owner_name'   => $this->input->post('owner_name')
        );
        $queryStoreOwner = $this->db->insert('tm_store_owner', $dataStoreOwner);
        return array(
          'queryULoging'    => $queryULoging,
          'queryStoreOwner' => $queryStoreOwner
        );
    } elseif($this->session->userdata('uType') == 1){
      $uType = $this->input->post('adminType');
      $newAdmin = 1;
      $pass = "admin_agm";
      $created = $this->session->userdata('uId');
      $dataUserLogin = array(
        'username'  => $this->input->post('uname'),
        'password'  => password_hash($pass, PASSWORD_DEFAULT),
        'email'     => $this->input->post('email'),
        'user_type' => $uType,
        'newer'     => $newAdmin,
        'created'   => $created
      );
      $queryULoging = $this->db->insert('user_login', $dataUserLogin);
      $userId = $this->getData(array('username' => $this->input->post('uname')),
        array('userIdField' => 'user_id'), TRUE);
      $id = $userId->user_id;

      $dataAdmin = array(
        'id_userLogin' => $id,
        'first_name'   => $this->input->post('fname'),
        'last_name'    => $this->input->post('lname'),
        'phone'        => $this->input->post('phone')
      );
      $queryAdmin = $this->db->insert('tm_super_admin', $dataAdmin);
      return array(
        'queryULoging' => $queryULoging,
        'queryAdmin'   => $queryAdmin
      );
    }
  }

  function forgotPass($email = NULL, $username = NULL){
    $dataUdatePassword = array(
      'password' => password_hash($this->input->post('pass'), PASSWORD_DEFAULT),
    );

    if($email != NULL){
      foreach ($email as $key => $value) {
        $this->db->where($key, $value);
      }
    }

    if ($username != NULL) {
      foreach ($username as $key => $value) {
        $this->db->where($key, $value);
      }
    }

    return $this->db->update('user_login', $dataUdatePassword);
  }

  public function complete_profile($data_customer)
  {
    $this->db->insert('tm_customer',$data_customer);
  }
  public function update_newer($newer)
  {
    $this->db->where('user_id',$this->session->userdata('uId'));
    $this->db->set($newer);
    $this->db->update('user_login');
  }
}
