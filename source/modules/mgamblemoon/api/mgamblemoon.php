<?php

class mgamblemoon{

	protected $reply;//回复
	protected $user;//用户
	protected $winner;//获胜

	public function __construct(){
	
		$this->reply = array(
		
			'rid' => NULL,
			'picture' => NULL,
			'description' => NULL,
			'rule'=NULL,
			'periodlottery' => NULL,
			'maxlottery' => NULL,
			'headpic' => NULL,
			'headurl' => NULL,
			'panzi' => NULL,
			'guzhuurl' => NULL,
			'prace_times' => NULL,
			'title' => NULL,
			'start_time' => NULL,
			'end_time' => NULL,
		
		);
		
		$this->user = array(
		
			'rid' => NULL,
			'from_user' => NULL,
			'count' => NULL,
			'points'=NULL,
			'friendcount' => NULL,
			'createtime' => NULL,
		
		);
		
		$this->winner = array(
		
			'rid' => NULL,
			'point' => NULL,
			'from_user' => NULL,
			'status'=NULL,
			'createtime' => NULL,
		
		);
	}

	public function getUser(){
	
	
	
	}



}