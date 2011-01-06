<?php
defined('SYSPATH') or die('No direct script access.');

Form::show_errors($errors);
?>

			<?php echo form::open('url/shorten', array('id' => 'shorten')); ?>

<?php 
// Temporary hack until stricter spam management is in place!
if (!$logged_in) : ?>
Due to spam, users are now required to create an account to use zURL. Sorry for the inconvenience.
<?php else: ?>
			
			
				<p>					
					<input type="hidden" name="token" value="<?php echo csrf::token(); ?>" />
					<label for="url">Enter a long URL to make short:</label><br />
					<input type="text" name="url" id="url" value="<?php echo htmlspecialchars($values['url']); ?>" />
<?php if (empty($captcha)): ?>
					<input type="submit" id="shorten" value="Shorten!" />
<?php endif; ?>
				</p>
<?php if ($logged_in): ?>
				<p>
					Type (<a href="about.htm#custom">what?</a>): 
					
					<?php echo Form::radio('type', 'standard', $values['type'] == '' || $values['type'] == 'standard', array('id' => 'type_standard')); ?>
					<label for="type_standard" title="Standard URL: A normal zURL link containing random letters and numbers in the URL">Standard</label>
					
					<?php echo Form::radio('type', 'custom', $values['type'] == 'custom', array('id' => 'type_custom')); ?>
					<label for="type_custom" title="Custom URL: Specify your own URL">Custom</label>
					
					<?php echo Form::radio('type', 'user', $values['type'] == 'user', array('id' => 'type_user')); ?>
					<label for="type_user" title="User URL: Like a custom URL, except with your username at the start">User</label>
				</p>
				<p id="alias_p">
					Short URL: http://<strong id="prefix"></strong>.zurl.ws/<input type="text" name="alias" id="alias" value="<?php echo !empty($values['alias']) ? htmlspecialchars($values['alias']) : ''; ?>" />
				</p>
<?php else: ?>
				<input type="hidden" name="type" value="standard" />
<?php endif; ?>
				
<?php if (!empty($captcha)): ?>
				<p>
					<label for="recaptcha_challenge_field">Security code:</label>
					<?php echo $captcha; ?>
					
					<input type="submit" id="shorten" value="Shorten!" />
				</p>
<?php endif; ?>
<?php endif; // Spam notice ?>
			</form>
