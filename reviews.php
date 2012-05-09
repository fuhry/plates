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
				<li>Reviews</li>
			</ul>
			<?php
			require_once('inc/loader.php');
			
			switch(isset($_GET['sort']) ? $_GET['sort'] : 'rating')
			{
				case 'date':
					$sort_by = 'date';
					$sort_clause = 'r.submit_time DESC';
					break;
				case 'location':
					$sort_by = 'loc';
					$sort_clause = 'v.v_name ASC';
					break;
				case 'rating':
				default:
					$sort_by = 'rating';
					$sort_clause = 'r.overall_rating DESC';
					break;
			}
			
			?>
			
			<div class="btn-group" style="float: right;">
				<a class="btn<?php if ( $sort_by == 'rating') echo ' active'; ?>" href="reviews.php?sort=rating">Rating</a>
				<a class="btn<?php if ( $sort_by == 'date') echo ' active'; ?>" href="reviews.php?sort=date">Date</a>
				<a class="btn<?php if ( $sort_by == 'loc') echo ' active'; ?>" href="reviews.php?sort=location">Location</a>
			</div>
			<div style="float: right; line-height: 28px;">
				Sort by: &nbsp;
			</div>
			
			<?php
			$by_venue = isset($_GET['by_venue']) ? sprintf(" WHERE r.venue_id = %d ", intval($_GET['by_venue'])) : '';
			$q = db_query($sql = "SELECT v.v_name, r.id, r.username, r.overall_rating, r.freetext, r.submit_time FROM reviews AS r LEFT JOIN venues AS v ON ( v.id = r.venue_id ) $by_venue ORDER BY $sort_clause;");
			$i = 0;
			while ( $row = db_fetch($q) )
			{
				if ( $i++ == 0 )
				{
					echo $by_venue ? "<h1>Reviews of " . htmlspecialchars($row['v_name']) . "</h1>" : "<h1>All reviews</h1>";
				}
				echo '<div>';
				echo '<div style="float: right;">' . stars($row['overall_rating']) . '</div>';
				printf("<h2>%s</h2>
						<blockquote>%s</blockquote>
						<em>%s</em> | <a href=\"detail.php?show_review=%d\">Read full review &raquo;</a>",
							$by_venue ? sprintf("<strong>%s</strong>'s review", htmlspecialchars($row['username'])) : sprintf("<strong>%s</strong> <small>by <strong>%s</strong></small>", htmlspecialchars($row['v_name']), htmlspecialchars($row['username'])),
							htmlspecialchars($row['freetext']),
							date('F j, Y', $row['submit_time']),
							$row['id']);
				echo '</div>';
			}
			?>
		</div>
		
	</body>
</html>
