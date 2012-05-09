<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<title>Plates of Rochester</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="res/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="res/plates.css" />
	</head>
	<body>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="index.php">Plates of Rochester</a>
					<ul class="nav">
						<li><a href="index.php">Home</a></li>
						<li class="active"><a href="reviews.php">Reviews</a></li>
						<li><a href="submit.php">Submit</a></li>
					</ul>
					<!-- form class="navbar-search pull-right" action="search.php">
						<input name="q" type="text" class="search-query" placeholder="Search reviews..." />
					</form -->
				</div>
			</div>
		</div>
		
		<div class="container">
			<ul class="breadcrumb">
				<li><a href="index.php">Home</a> <span class="divider">/</span></li>
				<li><a href="reviews.php">Reviews</a> <span class="divider">/</span></li>
				<li>View full review</li>
			</ul>
			<?php
			require('inc/loader.php');
			if ( !isset($_GET['show_review']) || intval($_GET['show_review']) < 1 )
			{
				die("<p>Invalid/missing review ID</p>");
			}
			$rid = intval($_GET['show_review']);
			$q = db_query("SELECT v.v_name, r.id, r.venue_id, r.username, r.overall_rating, r.freetext, r.submit_time FROM reviews AS r LEFT JOIN venues AS v ON ( v.id = r.venue_id ) WHERE r.id = $rid;");
			if ( db_numrows($q) < 1 )
				die("<p>That review was not found.</p>");
			
			$row = db_fetch($q);
			
			// Get average rating of the venue
			$q = db_query("SELECT AVG(overall_rating) FROM reviews WHERE venue_id = {$row['venue_id']} GROUP BY venue_id;");
			list($avg_rating) = array_values(db_fetch($q));
			?>
			<h1>Review of <?php echo htmlspecialchars($row['v_name']); ?> <?php echo stars(floatval($avg_rating)); ?></h1>
			<?php
			
			printf("<h3 class=\"thin\">Submitted on <strong>%s</strong> by <strong>%s</strong></h3>", date('F j, Y', $row['submit_time']), htmlspecialchars($row['username']));
			?>
			
			<table class="table table-bordered table-striped">
				<tr>
					<td style="width: 50%;"><strong>Overall rating:</strong></td>
					<td><?php echo stars($row['overall_rating']); ?></td>
				</tr>
				<?php
				$q = db_query("SELECT d.*, a.* FROM review_data AS d LEFT JOIN attrs AS a ON ( a.id = d.schema_id ) WHERE d.review_id = {$row['id']};");
				while ( $entry = db_fetch($q) )
				{
					if ( class_exists($clsname = "Control_{$entry['a_type']}") )
					{
						$control = new $clsname;
						$control->name = $entry['a_name'];
						$control->hint = $entry['a_hint'];
						$control->present($control->unserialize($entry['d_value']));
					}
					else
					{
						echo "<tr><td colspan=\"2\">Don't know how to display a {$entry['a_type']}</td></tr>"; 
					}
				}
				?>
				<tr>
					<td><strong>Comments:</strong></td>
					<td><?php echo nl2br(htmlspecialchars($row['freetext'])); ?></td>
				</tr>
			</table>
		</div>
		
		<div class="footer container">
			Plates of Rochester &copy; 2012 <a href="http://fuhry.us/">Dan Fuhry</a> &mdash; <a href="https://github.com/fuhry/plates">GitHub</a>
		</div>
		
	</body>
</html>
