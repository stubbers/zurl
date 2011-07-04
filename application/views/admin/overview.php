<?php
defined('SYSPATH') or die('No direct script access.');

Form::show_errors($errors);
?>
			<h2>Admin Overview</h2>
				<p>Welcome to Zurl.ws admin. <?php if(Kohana::config('app.allow_registration')): ?>Registration is currently enabled and invites are <?php if (!Kohana::config('app.allow_invites')): ?>not<?php endif; ?> required for registration<?php else: ?>Registration is currently disabled<?php endif; ?>. Guests are<?php if (!Kohana::config('app.allow_guest_urls')): ?> not<?php endif;?> allowed to shorten urls. To change these settings please modify the app config.</p>

			<h2>Oustanding Abuse Reports</h2>
				<p>Below are outstanding abuse reports. When a report is either acccepted or rejected it will be deleted from the database, there is no audit logging.</p>
				<!-- Yes, inline styles. Must remove these eventually. -->
				<table cellpadding="0" cellspacing="0">
					<thead>
						<tr>
							<th style="width: 3.5em"></th>
							<th class="date">Date Reported</th>
							<th class="shorturl">Short URL</th>
							<th>Long URL</th>
							<th>Reporter Email</th>
							<th>Reason</th>
						</tr>
					</thead>
					<tbody>
<?php
foreach ($unhandled_complaints as $complaint)
{
	$wrapped_url = chunk_split($complaint->url->url, 35, '&#8203;');

	echo '
					<tr id="url_', $complaint->url_id, '">
						<td>
							<img src="res/icons/bin_closed.png" alt="Delete" title="Delete" class="icon delete" width="16" height="16" />
						</td>
						<td>', Date::format($complaint->date, false), '</td>
						<td><a href="', $complaint->url, '">', $complaint->url, '</a></td>
						<td>',  $wrapped_url, '</td>
						<td>', $complaint->email, '</td>
						<td>', $complaint->comments, '</td>
					</tr>';
}
?>
					</tbody>
				</table>
