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
						<li><a href="reviews.php">Reviews</a></li>
						<li><a href="submit.php">Submit</a></li>
						<li class="active"><a href="scheme.php">Schema management</a></li>
					</ul>
					<!-- form class="navbar-search pull-right" action="search.php">
						<input name="q" type="text" class="search-query" placeholder="Search reviews..." />
					</form -->
				</div>
			</div>
		</div>
		
		<div class="container">
			<h1>Schema</h1>
			
			<?php
			require_once('inc/loader.php');
			if ( !empty($_POST['attr']) )
			{
				$row = array('a_flags' => 0, 'a_size' => 1);
				foreach ( $_POST['attr'] as $k => $v )
				{
					if ( in_array($k, array('a_name', 'a_hint', 'a_type')) && is_string($v) )
					{
						$row[$k] = $v;
					}
					else if ( $k == 'a_flags' && is_array($v) )
					{
						foreach ( $v as $fl )
							$row['a_flags'] |= intval($fl);
					}
					else if ( $k == 'plugin' && is_array($v) )
					{
						if ( isset($_POST['attr']['plugin'][ $_POST['attr']['a_type'] ]) )
						{
							foreach ( $_POST['attr']['plugin'][ $_POST['attr']['a_type'] ] as $pk => $pv )
							{
								if ( $pk == 'a_options' )
									$row[$pk] = json_encode($pv);
								else if ( in_array($pk, array('a_size')) )
									$row[$pk] = intval($pv);
								else if ( $pk == 'a_flags' && is_array($pv) )
									foreach ( $pv as $fl )
										$row['a_flags'] |= $fl;
							}
						}
					}
				}
				foreach ( $row as &$v )
				{
					if ( is_string($v) )
						$v = "'" . db_escape($v) . "'";
				}
				unset($v);
				$sql = "INSERT INTO attrs(" . implode(', ', array_keys($row)) . ") VALUES\n  (" . implode(', ', $row) . ");";
				db_query($sql);
				echo '<div class="alert alert-success">Attribute created.</div>';
			}
			if ( isset($_POST['delete_attr']) )
			{
				db_query(sprintf("DELETE FROM attrs WHERE id = %d;", intval($_POST['delete_attr'])));
				echo '<div class="alert alert-success">Attribute deleted.</div>';
			}
			?>
			<form method="post">
			<table class="table table-bordered table-striped schema-table">
			<thead>
				<tr>
					<th>Control name</th>
					<th>Control type</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$q = db_query("SELECT * FROM attrs ORDER BY a_sort_order ASC, a_name ASC;");
				while ( $row = db_fetch($q) )
				{
					echo '<tr data-attrid="' . $row['id'] . '">';
					printf("<td><strong>%s</strong>%s</td>", htmlspecialchars($row['a_name']), !empty($row['a_hint']) ? '<br /><small>' . htmlspecialchars($row['a_hint']) . '</small>' : '');
					printf("<td>%s</td>", htmlspecialchars($row['a_type']));
					printf("<td><button class=\"btn btn-danger\" name=\"delete_attr\" value=\"%d\">Delete</button> <i class=\"icon-resize-vertical\"></i></td>", $row['id']);
					echo '</tr>';
				}
				?>
			</tbody>
			
			</table>
			</form>
			
			<div class="well">
				<form method="post" class="form form-horizontal" id="schema-create-form" enctype="multipart/form-data">
					<fieldset>
						<legend>Create an attribute</legend>
						
						<div class="control-group">
							<label class="control-label">Name:</label>
							<div class="controls">
								<input type="text" name="attr[a_name]" />
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label">Hint/subtext:</label>
							<div class="controls">
								<input type="text" name="attr[a_hint]" />
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label">Control type:</label>
							<div class="controls">
								<select name="attr[a_type]" id="schema-create-attr-type-select">
									<?php
										foreach ( $control_types as $ct )
										{
											printf('<option value="%s">%s</option>', $ct, $ct);
										}
									?>
								</select>
							</div>
						</div>
						
						<?php
						foreach ( $control_types as $ct )
						{
							echo "<div class=\"schema_copts $ct\" style=\"display: none;\">";
							echo "<h3>$ct options</h3>";
							$ccls = "Control_$ct";
							$cobj = new $ccls();
							if ( method_exists($cobj, 'options') )
							{
								$cobj->options();
							}
							else
							{
								echo '<p>No options for this control type.</p>';
							}
							echo "</div>";
						}
						?>
						
						<div class="form-actions">
							<input class="btn btn-primary" type="submit" value="Create attribute" />
						</div>
						
					</fieldset>
				</form>
			</div>
			
		</div>
		<div class="footer container">
			Plates of Rochester &copy; 2012 <a href="http://fuhry.us/">Dan Fuhry</a> &mdash; <a href="https://github.com/fuhry/plates">GitHub</a>
		</div>
		
	</body>
</html>
