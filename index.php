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
						<li class="active"><a href="index.php">Home</a></li>
						<li><a href="venues.php">Venues</a></li>
						<li><a href="reviews.php">Reviews</a></li>
						<li><a href="submit.php">Submit</a></li>
					</ul>
					<!-- form class="navbar-search pull-right" action="search.php">
						<input name="q" type="text" class="search-query" placeholder="Search reviews..." />
					</form -->
				</div>
			</div>
		</div>
		
		<div class="container">
			<header class="jumbotron head">
				<h1>Plates of Rochester</h1>
				<p class="lead">
					Reviews, recommendations and buzz on Rochester's plates. <a href="whatis.php">What's a plate?</a>
				</p>
			</header>
			
			<h2>Latest reviews</h2>
			
			<?php
			require('inc/loader.php');
			$q = db_query("SELECT v.v_name, r.id, r.username, r.overall_rating, r.freetext, r.submit_time FROM reviews AS r LEFT JOIN venues AS v ON ( v.id = r.venue_id ) ORDER BY r.submit_time DESC, r.id DESC LIMIT 3;");
			while ( $row = db_fetch($q) )
			{
				echo '<div class="span4" style="margin-left: 0; padding-right: 10px; width: 290px;">';
				printf("Review of <strong>%s</strong> by <strong>%s</strong><br />
						<blockquote>%s</blockquote>
						<em>%s</em> %s | <a href=\"detail.php?show_review=%d\">Read full review &raquo;</a>",
							htmlspecialchars($row['v_name']),
							htmlspecialchars($row['username']),
							strlen($row['freetext']) > INDEX_SUMMARY_MAXLEN ? htmlspecialchars(substr($row['freetext'], 0, INDEX_SUMMARY_MAXLEN - 3) . '...') : htmlspecialchars($row['freetext']),
							date('F j, Y', $row['submit_time']),
							stars($row['overall_rating']),
							$row['id']);
				echo '</div>';
			}
			?>
			
			<h2 style="clear: both;">Top rated venues</h2>
			
			<?php
			$q = db_query("SELECT v.id, v.v_name, AVG(r.overall_rating) AS rating, COUNT(r.id) AS num_reviews FROM reviews AS r LEFT JOIN venues AS v ON ( v.id = r.venue_id ) GROUP BY v.id ORDER BY rating DESC LIMIT 3;");
			while ( $row = db_fetch($q) )
			{
				echo '<div class="span4" style="margin-left: 0; padding-right: 10px; width: 290px;">';
				printf("<h3>%s</h3>
						%s<br />
						Reviewed by <strong>%d</strong> %s<br /><a href=\"reviews.php?by_venue=%d\">See all reviews &raquo;</a>",
						htmlspecialchars($row['v_name']),
						stars($row['rating']),
						intval($row['num_reviews']),
						$row['num_reviews'] == 1 ? 'person' : 'people',
						$row['id']
						);
				echo '</div>';
			}
			?>
			
		</div>
		
		<div class="footer container">
			Plates of Rochester &copy; 2012 <a href="http://fuhry.us/">Dan Fuhry</a> &mdash; <a href="https://github.com/fuhry/plates">GitHub</a>
		</div>
		
	</body>
</html>
