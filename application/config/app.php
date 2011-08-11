<?php
defined('SYSPATH') or die('No direct script access.');

return array
(
	// TODO : I don't like mixing the variable type... This should probably become some kind of global remapping to an integer... Or something...
	// Set the type of registration allowed (true for standard registration, false for no registration, 'invite-only' for invite registration)
	'allow_registration' => true,
	
	// Allow guests to submit URLs
	'allow_guest_urls' => false,
);
?>
