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
			<h1>Search</h1>
			
			<form action="search.php" method="get">
			</form>
			
			<?php
			require_once('inc/loader.php');
			
			$q = db_query("SELECT v.v_name, r.id, r.username, r.overall_rating, r.freetext, r.submit_time FROM reviews AS r LEFT JOIN venues AS v ON ( v.id = r.venue_id ) ORDER BY r.overall_rating DESC LIMIT 3;");
			while ( $row = db_fetch($q) )
			{
				echo '<div class="span12">';
				echo '<div style="float: right;">' . stars($row['overall_rating']) . '</div>';
				printf("<h2><strong>%s</strong> <small>by <strong>%s</strong></small></h2>
						<blockquote>%s</blockquote>
						<em>%s</em> | <a href=\"reviews.php?show_review=%d\">Read full review &raquo;</a>",
							htmlspecialchars($row['v_name']),
							htmlspecialchars($row['username']),
							htmlspecialchars($row['freetext']),
							date('F j, Y', $row['submit_time']),
							$row['id']);
				echo '</div>';
			}
			?>
		</div>
		<div class="footer container">
			Plates of Rochester &copy; 2012 <a href="http://fuhry.us/">Dan Fuhry</a> &mdash; <a href="https://github.com/fuhry/plates">GitHub</a>
		</div>
		
	</body>
</html>
