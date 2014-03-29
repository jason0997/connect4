<?php

class Board extends CI_Controller {
     
    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	session_start();
    } 
          
    public function _remap($method, $params = array()) {
	    	// enforce access control to protected functions	
    		
    		if (!isset($_SESSION['user']))
   			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again
	    	return call_user_func_array(array($this, $method), $params);
    }
    
    
    function index() {
		$user = $_SESSION['user'];
    		    	
	    	$this->load->model('user_model');
	    	$this->load->model('invite_model');
	    	$this->load->model('match_model');
			//get update user info
	    	$user = $this->user_model->get($user->login);
	    	$invite = $this->invite_model->get($user->invite_id);

			//Waiting for other user, get information of the other user.
	    	if ($user->user_status_id == User::WAITING) {
	    		$invite = $this->invite_model->get($user->invite_id);
	    		$otherUser = $this->user_model->getFromId($invite->user2_id);
	    	}
			//Playing with other user, get information of other user accordingly.
	    	else if ($user->user_status_id == User::PLAYING) {			
	    		$match = $this->match_model->get($user->match_id);
	    		if ($match->user1_id == $user->id)
	    			$otherUser = $this->user_model->getFromId($match->user2_id);
	    		else
	    			$otherUser = $this->user_model->getFromId($match->user1_id);
	    	}
	    	
	    	$data['user']=$user;
	    	$data['otherUser']=$otherUser;
			
	    	//Set user status
	    	switch($user->user_status_id) {
	    		case User::PLAYING:	
	    			$data['status'] = 'playing';
	    			break;
	    		case User::WAITING:
	    			$data['status'] = 'waiting';
	    			break;
	    	}
		$game_board = array();
		$game_board_row = array();
		for($i = 0; $i < 7; $i = $i + 1){
			for($j = 0; $j < 6; $j = $j + 1){				
				$game_board_row[$j] = 0; 
			}
			array_push($game_board, $game_board_row);
		}
		
		$data['game_board'] = $game_board;		
		$this->load->view('match/board',$data);
    }

 	function postMsg() {
 		$this->load->library('form_validation');
 		$this->form_validation->set_rules('msg', 'Message', 'required');

		if ($this->form_validation->run() == TRUE) {
 			$this->load->model('user_model');
 			$this->load->model('match_model');
			
 			$user = $_SESSION['user'];
 			$user = $this->user_model->getExclusive($user->login);
			//lock the user in user table
			//If status is not playing,cant post message
 			if ($user->user_status_id != User::PLAYING) {	
				$errormsg="Not in PLAYING state";
 				goto error;
 			}
 			
 			$match = $this->match_model->get($user->match_id);			
 			//Get the msg
 			$msg = $this->input->post('msg');

 			if ($match->user1_id == $user->id)  {
				//Clear msg in database
 				$msg = $match->u1_msg == ''? $msg :  $match->u1_msg . "\n" . $msg;
 				$this->match_model->updateMsgU1($match->id, $msg);
 			}
 			else {//if user is user 2
				//Clear msg in database
 				$msg = $match->u2_msg == ''? $msg :  $match->u2_msg . "\n" . $msg;
 				$this->match_model->updateMsgU2($match->id, $msg);
 			}
			
 			//create json data "status: success", and send it to view			! We might use json_encode to set the blob ?!
 			echo json_encode(array('status'=>'success'));
 			 
 			return;
 		}
		
 		$errormsg="Missing argument";
 		
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 
	function getMsg() {
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 			
 		$user = $_SESSION['user'];
 		 
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		// start transactional mode  
 		$this->db->trans_begin();
 		//lock the user in match table
 		$match = $this->match_model->getExclusive($user->match_id);			
 		
		//get msg from the other user
 		if ($match->user1_id == $user->id) {
			$msg = $match->u2_msg;
 			$this->match_model->updateMsgU2($match->id,"");
 		}
 		else {
 			$msg = $match->u1_msg;
 			$this->match_model->updateMsgU1($match->id,"");
 		}
		
 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}
 		
 		// if all went well commit changes
 		$this->db->trans_commit();
 		
 		echo json_encode(array('status'=>'success','message'=>$msg));
		return;
		
		transactionerror:
		$this->db->trans_rollback();
		
		error:
		echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 	
	function getClickon(){
		//Communicate with database, get game_baord info... coming soon
 			$this->load->model('user_model');
 			$this->load->model('match_model');
			
 			$user = $_SESSION['user'];
 			$user = $this->user_model->getExclusive($user->login);
			//lock the user in user table
			//If status is not playing,cant post message
 			if ($user->user_status_id != User::PLAYING) {	
				$errormsg="Not in PLAYING state";
 				goto error;
 			}
 			
 			$match = $this->match_model->get($user->match_id);			
 			//Get the game_board 
 			$game_board = $this->input->post('game_board');

 			if ($match->user1_id == $user->id)  {
 				$this->match_model->updateBorad($match->id, $game_board);
 			}
 			else {//if user is user 2
				/*
 				$msg = $match->u2_msg == ''? $msg :  $match->u2_msg . "\n" . $msg;
 				$this->match_model->updateMsgU2($match->id, $msg);
				*/
				$this->match_model->updateBorad($match->id, $game_board);
 			}
			
 			//create json data "status: success", and send it to view			! We might use json_encode to set the blob ?!
 			echo json_encode(array('status'=>'success'));
 			 
 			return;
		
 		$errormsg="Missing argument";
 		
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
		
	}
 }

