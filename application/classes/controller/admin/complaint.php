<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Complaint extends Controller
{
	public function action_index()
	{
		$this->request->redirect('admin/overview');
	}
	
	public function action_accept($id)
	{
		$complaint = ORM::factory('complaint', $id);
		
		if (!$complaint->loaded())
		{
			$this->session->set('top_message', 'Invalid abuse report id');
		} else {
			$complaint->url->status = 'deleted_msg';
			$complaint->url->delete_message = 'Abuse report'; //TODO : Prompt admin for an abuse message
			$complaint->url->save();
			
			$complaint->delete();
			
			$this->session->set('top_message', 'Abuse report accepted, link deleted.');
		}
		
		$this->request->redirect('admin/overview');
	}
	
	public function action_reject($id)
	{
		
		$this->session->set('top_message', 'Abuse report rejected, link accepted.');
		$this->request->redirect('admin/overview');
	}
}
?>
