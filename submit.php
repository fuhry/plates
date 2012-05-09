<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
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
			<?php require('inc/loader.php'); ?>
			<?php
			if ( !empty($_POST) )
			{
				// echo '<pre>' . htmlspecialchars(print_r($_POST, true)) . '</pre>';
				
				$review = $_POST['review'];
				$review['submit_time'] = time();
				$overall_count = 0;
				$overall_n = 0;
				
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
				
				// Now create the review
				$review['overall_rating'] = round($overall_n / $overall_count, 1);
				
				$rid = db_insert('reviews', array('username', 'freetext', 'overall_rating', 'submit_time', 'venue_id'), $review);
				
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
				
				echo '<div class="alert alert-success">Review submitted. Thanks!</div>';
			}
			?>
			<form method="post" class="form form-horizontal" enctype="multipart/form-data">
				<fieldset>
					<legend>Basic info</legend>
					
					<!-- NAME -->
					<div class="control-group">
						<label class="control-label">Your name:</label>
						<div class="controls">
							<input type="text" name="review[username]" />
						</div>
					</div>
					
					<!-- VENUE -->
					<div id="venue-exists" style="display: block;">
						<div class="control-group">
							<label class="control-label">Where did you eat?</label>
							<div class="controls">
								<select name="review[venue_id]" id="venue_id">
									<?php
									$q = db_query("SELECT id, v_name, v_addr FROM venues;");
									while ( $row = db_fetch($q) )
									{
										printf("<option value=\"%d\">%s, %s</option>", $row['id'], htmlspecialchars($row['v_name']), htmlspecialchars($row['v_addr']));
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
					<div id="venue-doesnt-exist" style="display: none;">
						<h3>Add a new restaurant</h3>
						
						<!-- VENUE NAME -->
						<div class="control-group">
							<label class="control-label">Name:</label>
							<div class="controls">
								<input type="text" name="venue[v_name]" />
							</div>
						</div>
						
						<!-- VENUE ADDRESS -->
						<div class="control-group">
							<label class="control-label">Address:</label>
							<div class="controls">
								<input type="text" name="venue[v_addr]" /><br />
								<span class="help-inline">No need to include "Rochester, NY."</span>
							</div>
						</div>
						
						<!-- VENUE PHONE NUMBER -->
						<div class="control-group">
							<label class="control-label">Phone number:</label>
							<div class="controls">
								<input type="text" name="venue[v_phone]" />
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
							<textarea rows="10" cols="80" class="span6" name="review[freetext]"></textarea>
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
						$cobj->edit('attrs[' . $row['id'] . ']');
					}
					?>
					
					<div class="form-actions">
						<input type="submit" class="btn btn-primary" value="Submit review" />
					</div>
				</fieldset>
			</form>
		</div>
		<div class="footer container">
			Plates of Rochester &copy; 2012 <a href="http://fuhry.us/">Dan Fuhry</a> &mdash; <a href="https://github.com/fuhry/plates">GitHub</a>
		</div>
		
	</body>
</html>
