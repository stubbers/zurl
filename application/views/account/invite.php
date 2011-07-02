<?php
defined('SYSPATH') or die('No direct script access.');

Form::show_errors($errors);
?>

			<h2>Invite Friends</h2>
<?php if (Auth::instance()->get_user()->invites_remaining > 0) : ?>
				<p>You have <?php echo Auth::instance()->get_user()->invites_remaining; ?> invites remaining. Share the love!</p>
				<?php echo form::open('account/invite', array('id' => 'invite')); ?>
					<p>
						<label for="email">Email Address:</label>
						<input type="text" name="email" id="email" value="<?php echo htmlspecialchars($values['email']); ?>"/>
						<input type="submit" value="Invite" />
					</p>
				</form>
<?php else: ?>
				<p>You have no remaining invites. Please beg the administrators or get your already invited mates to invite others.</p>
<?php endif; ?>


