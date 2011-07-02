<?php
defined('SYSPATH') or die('No direct access allowed.');

class Model_Invite extends ORM
{
	protected $_belongs_to = array('user' => array());

	// Validation rules
	protected $_rules = array(
		'email' => array(
			'not_empty'  => NULL,
			'min_length' => array(4),
			'max_length' => array(127),
			'email'      => NULL,
		),
	);
}
?>
