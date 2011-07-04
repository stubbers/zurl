<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Account extends Controller_Template
{
	protected $secure_actions = array('index', 'settings', 'logout', 'urls', 'invite');
	
	public function action_index()
	{
		$this->request->redirect('account/settings');
	}
	
	public function action_settings()
	{
		$this->request->redirect('');
		/*$this->template->title = 'Account Settings';
		$page = $this->template->body = new View('account/settings');
		$page->errors = array();*/
	}
	
	public function action_login()
	{
		$this->template->title = 'Log in';
		$page = $this->template->body = new View('account/login');
		$user = ORM::factory('user');
		$timezone = Arr::get($_POST, 'timezone');
		
		// No POST data?
		if (!$_POST)
		{
			$page->errors = array();
			return;
		}
		// If we have POST data, try to log the user in.
		elseif (!$user->login($_POST))
		{
			$page->errors = $_POST->errors('login');
			return;
		}
		
		// If we're here, they logged in successfully!
		// Check if they submitted a timezone (via the JS)
		if ($timezone != '')
		{
			$user->timezone = $timezone;
			$user->save();
		}
		$this->session->set('top_message', 'You have been logged in. Welcome back!');
		$this->request->redirect('');
	}
	
	public function action_logout()
	{
		$this->auth->logout();
		$this->session->set('top_message', 'You have been logged out.');
		$this->request->redirect('');
	}
	
	public function action_register($invite_code)
	{
		// If they're logged in or we have disabled registration, we don't want to go to this page!
		if ($this->logged_in || !Kohana::config('app.allow_registration'))
			Request::instance()->redirect('');
			
		$this->template->title = 'Register';
		$this->template->jsload = 'Register.init';
		$page = $this->template->body = new View('account/register');
		$page->captcha = Recaptcha::get_html();
		$page->errors = array();
		$page->values = array('username' => '', 'email' => '', 'invite_code' => '');
		
		// If we are allowing invites they are required for registration
		if (Kohana::config('app.allow_invites'))
		{
			// TODO : This is messy, surely the structure here could be more logical, come back to it
			// Did the user post the form
			if ($_POST)
			{
				$invite_code = $_POST['invite_code'];
			}

			// If an invite code was provided
			if ($invite_code)
			{
				$invite = ORM::factory('invite')->where('auth_code', '=', $invite_code)->find();
		
				// Is the invite code invalid?
				if (!$invite->loaded())
				{
					$this->session->set('top_message', 'Invite code not valid. Registration denied');
					$this->request->redirect('');
				}
			} else {
				$this->session->set('top_message', 'No invite code provided. Invites are currently enabled, you must provide a valid invite code to register');
				$this->request->redirect('');
			}

			// Did the user post the form
			if ($_POST)
			{
				$user = ORM::factory('user');
				$user->values($_POST);
				// Add the CAPTCHA validation
				$user->validate()
					->callback('recaptcha_challenge_field', 'Recaptcha::validate');
				// TODO: This seems very messy, but it seems validate() doesn't check it. :(
				if (!csrf::valid($_POST['token']))
				{
					die('Token is invalid.');
				}

				// Check to ensure the post email matches the invite one
				if ($_POST['email'] != $invite->email)
				{
					$this->session->set('top_message', 'Stop messing with the post data please.');
					$this->request->redirect('');
				}
			
				if ($user->check())
				{
					// Do we have a timezone?
					$timezone = Arr::get($_POST, 'timezone');
					if ($timezone != '')
					{
						$user->timezone = $timezone;
					}
					
					$user->invited_by = $invite->user_id;
					$user->save();
					$user->add('roles', ORM::factory('role', array('name' => 'login')));

					// Delete the invite to invalidate it
					$this->invite->delete();

					$user->login($_POST);
					$this->session->set('top_message', 'Welcome to zURL! Your account has been created :)');
					$this->request->redirect('');
				}
			
				// If we're here, it failed, so we still have to show the page.
				// Let's grab the errors
				$page->errors = $user->validate()->errors('register');
				$page->values = $user->validate();
			} else {
				// If we got here, valid invite code and no post data
				$page->values = array('username' => '', 'email' => $invite->email, 'invite_code' => $invite_code);
			}

		// Invites are not turned on so use the standard registration
		} else {
			// Did the user post the form?
			if ($_POST)
			{
				$user = ORM::factory('user');
				$user->values($_POST);
				// Add the CAPTCHA validation
				$user->validate()
					->callback('recaptcha_challenge_field', 'Recaptcha::validate');
				// TODO: This seems very messy, but it seems validate() doesn't check it. :(
				if (!csrf::valid($_POST['token']))
				{
					die('Token is invalid.');
				}
			
				if ($user->check())
				{
					// Do we have a timezone?
					$timezone = Arr::get($_POST, 'timezone');
					if ($timezone != '')
					{
						$user->timezone = $timezone;
					}
				
					$user->save();
					$user->add('roles', ORM::factory('role', array('name' => 'login')));
					$user->login($_POST);
					$this->session->set('top_message', 'Welcome to zURL! Your account has been created :)');
					$this->request->redirect('');
				}
			
				// If we're here, it failed, so we still have to show the page.
				// Let's grab the errors
				$page->errors = $user->validate()->errors('register');
				$page->values = $user->validate();
			}
		}
	}
	
	public function action_invite()
	{
		// If they aren't logged in or we have disabled invites, we don't want to go to this page!
		if (!$this->logged_in || !Kohana::config('app.allow_invites'))
			Request::instance()->redirect('');
		
		$this->template->title = 'Invite Friends';
		$page = $this->template->body = new View('account/invite');
		$page->errors = array();
		$page->values = array('email' => '');
		
		// Did the user post the form?
		if ($_POST)
		{
			$invite = ORM::factory('invite');
			$invite->values($_POST);
			$invite->user = $this->user;
			
			// TODO : This needs some validation even though it's unlikely it would double up
			$invite->auth_code = text::random($type = 'alnum', $length = 10);
			$invite->validate();
			
			// If someone has already invited this user
			// TODO : This will probably work but Dan will need to clean this up a little
			if ((bool) ORM::factory('invite')->where('email', '=', $invite->email)->count_all())
			{
				$page->errors = array('email' => 'User already invited');
			} elseif ($invite->check()) {
				// Decriment the number of invites remaining for this user
				$this->user->invites_remaining --;
				$this->user->save();

				$invite->save();
				$this->session->set('top_message', 'Invite sent');
				$this->request->redirect('account/invite');
			} else {
				// If we're here, it failed, so we still have to show the page.
				// Let's grab the errors
				$page->errors = $invite->validate()->errors();
				$page->values = $invite->validate();
			}
		}
	}
	
	public function action_check_username()
	{
		if (empty($_POST['username']))
			die();
			
		// Check that this username is available
		$user = ORM::factory('user', array('username' => $_POST['username']));
		if ($user->loaded())
		{
			die(json_encode(array(
				'available' => false,
			)));		
		}
		
		die(json_encode(array(
			'available' => true,
		)));
	}
	
	public function action_urls()
	{
		$this->template->title = 'My URLs';
		$this->template->jsload = 'Listing.init';
		
		// Get the URLs
		$benchmark = Profiler::start('zURL', 'Get user URLs');
		$count = ORM::factory('url')->count_user($this->user);
		$pagination = Pagination::factory(array(
			'total_items' => $count,
			'items_per_page' => 25
		));
		
		$urls = ORM::factory('url')
			->where('user_id', '=', $this->user)
			->and_where('status', '=', 'ok')
			->order_by('id', 'desc')
			->limit($pagination->items_per_page)
			->offset($pagination->offset)
			->find_all();
		Profiler::stop($benchmark);
		
		$benchmark = Profiler::start('zURL', 'Get user hits');
		// Let's get the 10 most recent visitors, too.
		/*$visitors = ORM::factory('hit')
			->where('';*/
		$visits = DB::select('hits.*', array('UNIX_TIMESTAMP("hits.date")', 'date'), 'urls.*', array('countries.printable_name', 'country_name'))
			->from('hits')
			->join('urls')->on('urls.id', '=', 'hits.url_id')
			->join('users')->on('users.id', '=', 'urls.user_id')
			->join('countries', 'left')->on('countries.iso', '=', 'hits.country')
			->where('users.id', '=', $this->user)
			->order_by('hits.id', 'desc')
			->limit(20)
			->as_object()
			->execute();
		
		Profiler::stop($benchmark);
		
		$page = $this->template->body = new View('account/urls');
		$page->urls = $urls;
		$page->count = $count;
		$page->pagination = $pagination->render();
		$page->visits = $visits;
	}
}
?>
