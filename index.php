<!DOCTYPE>
<html>
<head>
	<title>dexter</title>
	<link href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:300" rel="stylesheet" type="text/css">	
	<link href="client_src/structure.css" rel="stylesheet" type="text/css">	
</head>
<body class="centerwrapper">
	<div class='section' id='interface'>
		<div id='title'>Dexter</div>	
		
		<form id='interfaceform'><span>Crawler Interface</span><br>
			<input type='text' style='width:400px' placeholder='subject' name='subject' id='subject'><br>
			<input type='text' style='width:45px;' value='10' name='max' id='max'> max links per page (~ 100)<br>
			<select name='depth' id='depth'><option>1</option></select> search depth (grows at a factor of number_of_links^n) <br>
			<select name='debug' id='debug'>
				<option>0</option>
				<option>1</option>
			</select> feedback level (debugging threshold)<br>
			<select name='pagetype' id='pagetype'>
				<option>wikipedia</option>
			</select> content type<br>
			<input type='submit' value='explore'>	
			<div id='log' >
				<a href="http://localhost/db_viewer.php">view db as graph</a>
			</div>
		</form>
		
	</div>
	
	<script src="client_src/js/jquery.1.6.1.js"></script>
	<script src="client_src/js/form.js"></script>
</body>
</html>
