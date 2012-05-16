<?php
if ( isset($_GET['act']) && !empty($_POST) )
{
	header('Content-type: text/javascript');
	require('inc/loader.php');
	switch($_GET['act'])
	{
		case 'update_sort_order':
			if ( !preg_match('/^([0-9]+(,|$))+$/', $_POST['order']) )
				break;
			$order = explode(',', $_POST['order']);
			$i = 0;
			foreach ( $order as $aid )
			{
				db_query("UPDATE attrs SET a_sort_order = $i WHERE id = $aid;");
				$i++;
			}
			echo json_encode(true);
			break;
	}
	exit;
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
						<li><a href="submit.php">Submit</a></li>
						<li><a href="schema.php">Schema management</a></li>
						<li class="active"><a href="admin.php">Admin</a></li>
					</ul>
					<!-- form class="navbar-search pull-right" action="search.php">
						<input name="q" type="text" class="search-query" placeholder="Search reviews..." />
					</form -->
				</div>
			</div>
		</div>
		
		<div class="container">
			<h1>Administration</h1>
			
			<?php
			require_once('inc/loader.php');
			if ( isset($_POST['delete_review']) )
			{
				db_query(sprintf("DELETE FROM review_data WHERE review_id = %d;", intval($_POST['delete_review'])));
				db_query(sprintf("DELETE FROM reviews WHERE id = %d;", intval($_POST['delete_review'])));
				echo '<div class="alert alert-success">Review deleted.</div>';
			}
			?>
			
			<form method="post">
			<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>Username</th>
					<th>Venue</th>
					<th>Submit time</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$q = db_query("SELECT r.*, v.v_name FROM reviews AS r LEFT JOIN venues AS v ON ( v.id = r.venue_id ) ORDER BY r.submit_time DESC;");
				while ( $row = db_fetch($q) )
				{
					echo '<tr>';
					printf("<td>%s</td>", htmlspecialchars($row['username']));
					printf("<td>%s</td>", htmlspecialchars($row['v_name']));
					printf("<td>%s</td>", date('F j, Y g:i A', $row['submit_time']));
					printf("<td style=\"text-align: center\">
								<a title=\"See this review\" href=\"detail.php?show_review=%d\" class=\"btn btn-success btn-mini\"><i class=\"icon-white icon-search\"></i></a>
								<button title=\"Delete this review\" name=\"delete_review\" value=\"%d\" class=\"btn btn-danger btn-mini\"><i class=\"icon-white icon-trash\"></i></button>
							</td>", $row['id'], $row['id']);
					echo '</tr>';
				}
				?>
			</tbody>
			</table>
			</form>
		</div>
		<div class="footer container">
			Plates of Rochester &copy; 2012 <a href="http://www.grantcohoe.com/">Grant Cohoe</a> &amp; <a href="http://fuhry.us/">Dan Fuhry</a> &mdash; <a href="https://github.com/fuhry/plates">GitHub</a>
		</div>
		
	</body>
</html>
