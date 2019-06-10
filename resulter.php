<?php
set_time_limit(3600);
session_start();
require 'requires/prolog.php';
//запуск сетки
if(!Async::isWorkedThreadByPid($_SESSION['pid'] ?? 0)) {
	$thread = Async::query('feedinger.php');
	$_SESSION['pid'] = $thread->getPid();
}
else {
	$thread = Async::thread($_SESSION['pid']);
}

if($_GET['die'] ?? false) {
	$thread->die();

	echo json_encode(['message' => 'System: Neuros is stopped!']);
	exit();
} else if($_POST['message'] ?? false) {
	$data = ['message' => $_POST['message']];
	$response = $thread->send($data);

	echo json_encode(['message' => $response]);
	exit();
}
?>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
</head>
<body>
	<div id="content"></div>
	<input type="text" id="message"/>
</body>
<script>
	$('#message').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
    	let message = $('#message').val();
    	$('#message').val('');
    	$('#message').attr('disabled','true');
    	$('#content').append('<div><b>Вы:</b>' + message + '</div>');
    	let data = { 'message' : message};
    	$.ajax({
		  url: '',
		  type: "POST",
		  data: data,
		  success: function(data){
		  	  $('#message').val('');
    		$('#message').attr('disabled','true');
    		$('#content').append('<div><b>Neuros:</b>' + data.message + '</div>');
		  	$('#message').removeAttr('disabled');
		  },
		  dataType: "JSON"
		});
    }
});
</script>
