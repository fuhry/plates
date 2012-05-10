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
						<li class="active"><a href="venues.php">Venues</a></li>
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
			<ul class="breadcrumb">
				<li><a href="index.php">Home</a> <span class="divider">/</span></li>
				<li>Venues</li>
			</ul>
			<?php
			require_once('inc/loader.php');
			
			switch(isset($_GET['sort']) ? $_GET['sort'] : 'rating')
			{
				case 'count':
					$sort_by = 'count';
					$sort_clause = 'review_count DESC';
					break;
				case 'rating':
				default:
					$sort_by = 'rating';
					$sort_clause = 'avg_rating DESC';
					break;
			}
			
			?>
			
			<div class="btn-group" style="float: right;">
				<a class="btn<?php if ( $sort_by == 'rating') echo ' active'; ?>" href="venues.php?sort=rating">Rating</a>
				<a class="btn<?php if ( $sort_by == 'count') echo ' active'; ?>" href="venues.php?sort=count"># of reviews</a>
			</div>
			<div style="float: right; line-height: 28px;">
				Sort by: &nbsp;
			</div>
			
			<h1>Venues</h1>
			
			<table class="table table-bordered table-striped" style="margin-top: 10px;">
			<thead>
				<tr>
					<th>Name</th>
					<th>Address</th>
					<th>Phone</th>
					<th>Average rating</th>
					<th>Number of reviews</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$q = db_query("SELECT v.id, v_name, v_addr, v_phone, AVG(r.overall_rating) AS avg_rating, COUNT(r.id) AS review_count FROM venues AS v LEFT JOIN reviews AS r ON ( v.id = r.venue_id ) GROUP BY r.venue_id ORDER BY $sort_clause;");
				while ( $row = db_fetch($q) )
				{
					echo '<tr>';
					$addr = $row['v_addr'];
					if ( !preg_match('/, ?NY$/', $addr) )
						$addr .= ', Rochester, NY';
					printf("<td><a href=\"reviews.php?by_venue=%d\">%s</a></td>", $row['id'], htmlspecialchars($row['v_name']));
					printf("<td><a href=\"http://maps.google.com/maps?q=%s\" onclick=\"window.open(this.href); return false;\">%s</a></td>", htmlspecialchars(urlencode($addr)), htmlspecialchars($row['v_addr']));
					printf("<td>%s</td>", htmlspecialchars($row['v_phone']));
					printf("<td>%s</td>", stars($row['avg_rating']));
					printf("<td>%d</td>", $row['review_count']);
					echo '</tr>';
				}
				?>
			</tbody>
			</table>
		</div>
		
		<div class="footer container">
			Plates of Rochester &copy; 2012 <a href="http://fuhry.us/">Dan Fuhry</a> &mdash; <a href="https://github.com/fuhry/plates">GitHub</a>
		</div>
		
	</body>
</html>
