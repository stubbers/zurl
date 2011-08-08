<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Overview extends Controller_Template
{
	protected $secure_actions = array('index');
	
	public function action_index()
	{
		$this->template->title = 'Admin';
		$page = $this->template->body = new View('admin/overview');
		$page->errors = array();

		$complaints = ORM::factory('complaint')->find_all();
		
		$page->unhandled_complaints = $complaints;
	}
}
?>
