<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin extends Controller_Template
{
	protected $secure_actions = array('index');
	
	public function action_index()
	{
		$this->request->redirect('admin/overview');
	}
}
?>
