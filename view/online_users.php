
<!DOCTYPE html>
<html>
<body>

<h1>Getting server updates</h1>
<div id="result"></div>

<script>
if(typeof(EventSource) !== "undefined") {
	var source = new EventSource("<?php echo base_url()?>mapping/getLogUpdate");
	source.onmessage = function(event) {
		console.log(event);
		document.getElementById("result").innerHTML += event.data + "<br>";
	};
} else {
	document.getElementById("result").innerHTML = "Sorry, your browser does not support server-sent events...";
}
</script>
