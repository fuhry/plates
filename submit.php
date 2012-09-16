<?php
require('inc/loader.php');

$recaptcha_error = null;
$submit_error = null;
$prefill = array();
if ( !empty($_POST) )
{
	$prefill = $_POST;
	call_user_func(function() {
		global $submit_error, $recaptcha_error;
		
		if ( empty($_POST["recaptcha_response_field"]) )
			return $submit_error = "No response provided for the CAPTCHA.";
		
		global $recaptcha_config;
		$resp = recaptcha_check_answer($recaptcha_config['private'],
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
        if ( !$resp->is_valid )
        {
        	$recaptcha_error = $resp->error;
        	return $submit_error = "Check the CAPTCHA.";
        }
        
		$review = $_POST['review'];
		$review['submit_time'] = time();
		$overall_count = 0;
		$overall_n = 0;
		
		$update = false;
		if ( !empty($review['id']) && !empty($review['key']) )
		{
			$id = intval($review['id']);
			$key = $review['key'];
			if ( preg_match('/^[a-z0-9]{32}$/', $key) )
			{
				$q = db_query("SELECT 1 FROM reviews WHERE id = $id AND edit_key = '$key';");
				if ( db_numrows($q) )
				{
					$update = $id;
				}
			}
		}
		
		// fetch all attributes
		$attrs = array();
		$q = db_query("SELECT * FROM attrs ORDER BY a_sort_order ASC;");
		while ( $row = db_fetch($q) )
		{
			$attrs[ intval($row['id']) ] = $row;
		}
		
		foreach ( $_POST['attrs'] as $id => $attr )
		{
			$id = intval($id);
			if ( !isset($attrs[$id]) )
			{
				unset($_POST['attrs'][$id]);
				continue;
			}
			if ( $attrs[$id]['a_flags'] & CONTROL_RATING )
			{
				$overall_count++;
				$overall_n += intval($attr);
			}
		}
		
		// first, do we need to try and create the venue?
		if ( empty($review['venue_id']) )
		{
			$review['venue_id'] = db_insert('venues', array('v_name', 'v_addr', 'v_phone'), $_POST['venue']);
		}
		
		// determine overall rating
		$review['overall_rating'] = round($overall_n / $overall_count, 1);
		
		if ( empty($update) )
		{
			// we are creating a new review
			
			// generate an edit key
			$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
			$review['edit_key'] = '';
			while ( strlen($review['edit_key']) < 32 )
				$review['edit_key'] .= $chars{mt_rand(0, strlen($chars)-1)};
			
			$rid = db_insert('reviews', array('username', 'freetext', 'overall_rating', 'submit_time', 'venue_id', 'edit_key'), $review);
			
			$prefill['review']['key'] = $review['edit_key'];
		}
		else
		{
			// we are updating an existing review
			$rid = $update;
			
			$update = "UPDATE reviews SET ";
			foreach ( array('username', 'freetext', 'overall_rating', 'venue_id') as $column )
			{
				$update .= sprintf("%s = '%s', ", $column, mysql_real_escape_string($review[$column]));
			}
			$update = substr($update, 0, -2);
			$update .= " WHERE id = $rid;";
			
			db_query($update);
			// delete old attrs
			db_query("DELETE FROM review_data WHERE review_id = $rid;");
		}
		
		// ...and insert user-defined attributes
		$attr_rows = array();
		foreach ( $_POST['attrs'] as $id => $attr )
		{
			$ccls = "Control_{$attrs[$id]['a_type']}";
			$cobj = new $ccls;
			$attr_rows[] = array(
				'review_id' => $rid,
				'schema_id' => intval($id),
				'd_value' => $cobj->serialize($attr)
				);
		}
		db_insert('review_data', array('review_id', 'schema_id', 'd_value'), $attr_rows);
		
		// send the e-mail
		// FIXME: move this to an external template or something
		$now = date('r');
		$email_body = <<<EOF
Hey there, Plates admins!

Just wanted to let you know that someone has submitted a review to Plates of Rochester. You need to read and approve it before it will be displayed live.

You can view this review at the following link:

	http://{$_SERVER['HTTP_HOST']}/detail.php?show_review=$rid

Review information:
	Submitted by:	{$review['username']}
	IP address:		{$_SERVER['REMOTE_ADDR']}
	Date and time:	$now

Administer reviews using the following link:
	https://{$_SERVER['HTTP_HOST']}/admin.php

- The PoR admin bot

EOF;
	
		$mail_result = @smtp_mail($alerts_email, $alerts_email, "[PoR] New review submitted", $email_body);
		
		header('HTTP/1.1 302 Found');
		header("Location: reviews.php?sort=date&submitted=true&id=$rid&key={$prefill['review']['key']}");
		exit;
	});
}
else
{
	if ( isset($_GET['edit_review']) && isset($_GET['key']) )
	{
		$rid = intval($_GET['edit_review']);
		$key = $_GET['key'];
		if ( preg_match('/^[a-z0-9]{32}$/', $key) )
		{
			$q = db_query("SELECT v.v_name, r.id, r.venue_id, r.username, r.overall_rating, r.freetext, r.submit_time FROM reviews AS r LEFT JOIN venues AS v ON ( v.id = r.venue_id ) WHERE r.id = $rid AND r.edit_key = '$key';");
			if ( db_numrows($q) )
			{
				$row = db_fetch($q);
				$prefill = array(
						'review' => array(
								'username' => $row['username']
								, 'venue_id' => $row['venue_id']
								, 'freetext' => $row['freetext']
								, 'id' => $row['id']
								, 'key' => $key
							)
						, 'venue' => array(
								'v_name' => ''
								, 'v_addr' => ''
								, 'v_phone' => ''
							)
						, 'attrs' => array()
					);
				db_free_result($q);
				$q = db_query("SELECT schema_id, d_value FROM review_data WHERE review_id = $rid;");
				while ( $row = db_fetch($q) )
				{
					$prefill['attrs'][ intval($row['schema_id']) ] = $row['d_value'];
				}
			}
		}
	}
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<title>Plates of Rochester</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="res/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="res/plates.css" />
		<script type="text/javascript" src="res/jquery.js"></script>
		<script type="text/javascript" src="res/jquery-ui.js"></script>
		<script type="text/javascript" src="res/controls.js"></script>
	</head>
	<body>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="index.php">Plates of Rochester</a>
					<ul class="nav">
						<li><a href="index.php">Home</a></li>
						<li><a href="venues.php">Venues</a></li>
						<li><a href="reviews.php">Reviews</a></li>
						<li class="active"><a href="submit.php">Submit</a></li>
					</ul>
					<!-- form class="navbar-search pull-right" action="search.php">
						<input name="q" type="text" class="search-query" placeholder="Search reviews..." />
					</form -->
				</div>
			</div>
		</div>
		<div class="container">
			<h1>Submit a review</h1>
			<?php
			if ( !empty($submit_error) )
			{
				echo "<div class=\"alert alert-error\">$submit_error</div>";
			}
			?>
			<form method="post" class="form form-horizontal" enctype="multipart/form-data">
				<fieldset>
					<legend>Basic info</legend>
					
					<!-- NAME -->
					<div class="control-group">
						<label class="control-label">Your name:</label>
						<div class="controls">
							<input type="text" name="review[username]"
								<?php if ( isset($prefill['review']['username']) ) printf("value=\"%s\"", htmlspecialchars($prefill['review']['username'])); ?>
								/>
						</div>
					</div>
					
					<!-- VENUE -->
					<div id="venue-exists" style="<?php echo ( !empty($prefill) && empty($prefill['review']['venue_id']) ) ? "display: none;" : "display: block;"; ?>">
						<div class="control-group">
							<label class="control-label">Where did you eat?</label>
							<div class="controls">
								<select name="review[venue_id]" id="venue_id">
									<?php
									$q = db_query("SELECT id, v_name, v_addr FROM venues;");
									$select = isset($prefill['review']['venue_id']) ? intval($prefill['review']['venue_id']) : 0;
									while ( $row = db_fetch($q) )
									{
										$sel = intval($row['id']) === $select ? ' selected="selected"' : '';
										printf("<option value=\"%d\"%s>%s, %s</option>", $row['id'], $sel, htmlspecialchars($row['v_name']), htmlspecialchars($row['v_addr']));
									}
									?>
								</select><br />
								<span class="help-inline">
									or <a href="#" onclick="$('#venue-doesnt-exist').show(); $('#venue_id').attr('disabled', 'disabled'); $('#venue-exists').hide(); return false;">add a new restaurant...</a>
								</span>
							</div>
						</div>
					</div>
					
					<!-- NEW VENUE -->
					<div id="venue-doesnt-exist" style="<?php echo ( !empty($prefill) && empty($prefill['review']['venue_id']) ) ? "display: block;" : "display: none;"; ?>">
						<h3>Add a new restaurant</h3>
						
						<!-- VENUE NAME -->
						<div class="control-group">
							<label class="control-label">Name:</label>
							<div class="controls">
								<input type="text" name="venue[v_name]"
									<?php if ( isset($prefill['venue']['v_name']) ) printf("value=\"%s\"", htmlspecialchars($prefill['venue']['v_name'])); ?>
									/>
							</div>
						</div>
						
						<!-- VENUE ADDRESS -->
						<div class="control-group">
							<label class="control-label">Address:</label>
							<div class="controls">
								<input type="text" name="venue[v_addr]"
									<?php if ( isset($prefill['venue']['v_name']) ) printf("value=\"%s\"", htmlspecialchars($prefill['venue']['v_addr'])); ?>
									/><br />
								<span class="help-inline">No need to include "Rochester, NY."</span>
							</div>
						</div>
						
						<!-- VENUE PHONE NUMBER -->
						<div class="control-group">
							<label class="control-label">Phone number:</label>
							<div class="controls">
								<input type="text" name="venue[v_phone]"
									<?php if ( isset($prefill['venue']['v_name']) ) printf("value=\"%s\"", htmlspecialchars($prefill['venue']['v_phone'])); ?>
									/>
							</div>
						</div>
						
						<div class="control-group">
							<div class="controls">
								<a href="#" onclick="$('#venue-doesnt-exist').hide(); $('#venue_id').attr('disabled', false);  $('#venue-exists').show(); return false;">Use an existing restaurant</a>
							</div>
						</div>
					</div>
					
					<!-- FREETEXT -->
					<div class="control-group">
						<label class="control-label">Comments:</label>
						<div class="controls">
							<textarea rows="10" cols="80" class="span6" name="review[freetext]"><?php if ( isset($prefill['review']['freetext']) ) echo htmlspecialchars($prefill['review']['freetext']); ?></textarea>
						</div>
					</div>
					
				</fieldset>
				
				<fieldset>
					<legend>Tell us more about your experience</legend>
					
					<?php
					$q = db_query("SELECT * FROM attrs ORDER BY a_sort_order ASC;");
					while ( $row = db_fetch($q) )
					{
						$ccls = "Control_{$row['a_type']}";
						$cobj = new $ccls();
						$cobj->name = $row['a_name'];
						$cobj->hint = $row['a_hint'];
						$cobj->flags = $row['a_flags'];
						$cobj->options = !empty($row['a_options']) ? json_decode($row['a_options'], true) : array();
						$cobj->edit('attrs[' . $row['id'] . ']', !empty($prefill['attrs'][$row['id']]) ? $prefill['attrs'][$row['id']] : null);
					}
					?>
					
					<div class="control-group">
						<label class="control-label">Prove your humanity:</label>
						<div class="controls">
							<?php
							echo recaptcha_get_html($recaptcha_config['public'], $recaptcha_error);
							?>
						</div>
					</div>
					
					<div class="form-actions">
						<input type="submit" class="btn btn-primary" value="Submit review" />
					</div>
				</fieldset>
				
				<?php
				if ( !empty($prefill['review']['id']) && !empty($prefill['review']['key']) )
				{
					printf("<input type=\"hidden\" name=\"review[id]\" value=\"%d\" />", $prefill['review']['id']);
					printf("<input type=\"hidden\" name=\"review[key]\" value=\"%s\" />", htmlspecialchars($prefill['review']['key']));
				}
				?>
			</form>
		</div>
		<div class="footer container">
			Plates of Rochester &copy; 2012 <a href="http://www.grantcohoe.com/">Grant Cohoe</a> &amp; <a href="http://fuhry.us/">Dan Fuhry</a> &mdash; <a href="https://github.com/fuhry/plates">GitHub</a>
		</div>
		
	</body>
</html>
