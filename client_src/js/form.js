$('#interfaceform').submit(function(){
	send_form();
	event.preventDefault();
});


function send_form(){
	$.ajax({
		type: "POST",
		url: "server/crawler_wikipedia.php",
		data: { 
			s: 		$('#subject').val(), 
			max:	$('#max').val(),
			depth:	$('#depth').val(),
			debug:	$('#debug').val(),
			type:	$('#pagetype').val()
		}
	}).done(function( msg ) {
		parse_search_result(msg);
	});
}
function parse_search_result(msg){
	if(!msg.length||msg.indexOf(';')<2){return;}
	var data=msg.split(';');
	var parent=data[0];
	var links=data[1].split(',');
	
	run_analyst(parent);
	if($('#depth').val()==1){
		links.map(run_analyst);
	}
}
function run_analyst(link){
	$.ajax({
		type: "POST",
		url: "server/analyst.php",
		data: { 
			address:link,
			max:	$('#max').val(),
			depth:	$('#depth').val(),
			debug:	$('#debug').val(),
			type:	$('#pagetype').val()
		}
	}).done(function( msg ) {
		if($('#debug').val()){
			$('#log').append(msg);
		}else{
			console.log(msg);
		}
	});
}

  
 