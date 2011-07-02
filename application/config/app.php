<?php
defined('SYSPATH') or die('No direct script access.');

return array
(
	// Allow new users to register
	'allow_registration' => true,
	
	// Allow guests to submit URLs
	'allow_guest_urls' => false,

	// Allow invites to other users, don't turn this on if you are enabling registration... That's just stupid
	'allow_invites' => true,
);
?>
