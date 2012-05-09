<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<title>Plates of Rochester</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="res/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="res/plates.css" />
		<script type="text/javascript" src="res/jquery.js"></script>
		<script type="text/javascript" src="res/controls.js"></script>
	</head>
	<body>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="#">Plates of Rochester</a>
					<ul class="nav">
						<li><a href="index.php">Home</a></li>
						<li><a href="reviews.php">Reviews</a></li>
						<li><a href="submit.php">Submit</a></li>
						<li class="active"><a href="scheme.php">Schema management</a></li>
					</ul>
					<form class="navbar-search pull-right" action="search.php">
						<input name="q" type="text" class="search-query" placeholder="Search reviews..." />
					</form>
				</div>
			</div>
		</div>
		
		<div class="container">
			<h1>Schema</h1>
			
			<?php
			require_once('inc/loader.php');
			?>
			<form method="post">
			<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>Control name</th>
					<th>Control type</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$q = db_query("SELECT * FROM attrs ORDER BY a_name ASC;");
				while ( $row = db_fetch($q) )
				{
					echo '<tr>';
					printf("<td><strong>%s</strong>%s</td>", htmlspecialchars($row['a_name']), !empty($row['a_hint']) ? '<br /><small>' . htmlspecialchars($row['a_hint']) . '</small>' : '');
					printf("<td>%s</td>", htmlspecialchars($row['a_type']));
					printf("<td><button class=\"btn btn-danger\" name=\"delete_attr\" value=\"%d\">Delete</button></td>", $row['id']);
					echo '</tr>';
				}
				?>
			</tbody>
			
			</table>
			</form>
			
			<div class="well">
				<form method="post" class="form form-horizontal">
					<fieldset>
						<legend>Create an attribute</legend>
						<?php
						$cont = new Control_Rating();
						$cont->name = "Sketchiness:";
						$cont->edit('Sketchiness', 5.0);
						?>
					</fieldset>
				</form>
			</div>
			
		</div>
	</body>
</html>
