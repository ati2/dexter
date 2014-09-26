<!DOCTYPE>
<html>
<head>
	<title>dexter</title>
	<link href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:300" rel="stylesheet" type="text/css">	
	
	<style> body{font-family: 'Source Sans Pro', sans serif; margin:30px;} </style>
</head>
<body>
	<h1>Dexter</h1>
	<ul>
		<a href="server/crawler.php"><li>this page runs through the file dir and calls the analyst</li></a>
		<a href="server/analyst.php"><li>analyst reads 1 page and links it to the database<br>
		(dont actually go to this one. crawler calls with and passes data into it)
		</li></a>
		<a href="db_viewer.php"><li>shows the state of the database</li></a>
	</ul>
</body>
</html>
