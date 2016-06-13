<!DOCTYPE html>
<html>
<head>
	<!-- CSS properties -->
	
	
	<!-- Title -->
	<title>Nick's Webpage</title>
	
	<!-- JavaScript -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script>
	// Get the Show Data
	function getShowData(){
		document.getElementById("showdata").innerHTML="Loading...";
		var xmlhttp;
		if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200){
				document.getElementById("showdata").innerHTML=xmlhttp.responseText;
			}
		}
		xmlhttp.open("GET","show_data.php?mode=df",true);
		xmlhttp.send();
	}
	// Update the Show Data
	function updateShowData(){
		document.getElementById("showdata").innerHTML="Updating...";
		var xmlhttp;
		if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200){
				document.getElementById("showdata").innerHTML=xmlhttp.responseText;
			}
		}
		xmlhttp.open("GET","show_data.php?mode=uf",true);
		xmlhttp.send();
	}
	// Download Torrent
	function downloadShow(index){
		var xmlhttp;
		if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200){
				document.getElementById("showdata").innerHTML=xmlhttp.responseText;
			}
		}
		xmlhttp.open("GET","show_data.php?mode=de&tr="+index,true);
		xmlhttp.send();
	}
	// Delete Torrent
	function deleteShow(index){
		var xmlhttp;
		if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200){
				document.getElementById("showdata").innerHTML=xmlhttp.responseText;
			}
		}
		xmlhttp.open("GET","show_data.php?mode=re&tr="+index,true);
		xmlhttp.send();
	}

	</script>
	
	<!-- PHP -->
	<?php
	// Disk space progress bar
    $df = disk_free_space("/home/pi/PiDrive");	// free space
    $dt = disk_total_space("/home/pi/PiDrive");	// total space
    $du = $dt - $df;	// used space
    $dp = sprintf('%.2f',($du / $dt) * 100);	// percentage
	$df = formatSize($df);
    $du = formatSize($du);
    $dt = formatSize($dt);
    function formatSize( $bytes ) {
            $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
            for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
                    return( round( $bytes, 2 ) . " " . $types[$i] );
    }
	
	?>
</head>


<body onload="getShowData()">	
	<!-- Disk space progress bar -->
	<p>
		<style type='text/css'>
			.progress {
				border: 2px solid #5E96E4;
				height: 32px;
				width: 540px;
				margin: 30px auto;
			}
			.progress .prgbar {
				background: #A7C6FF;
				width: <?php echo $dp; ?>%;
				position: relative;
				height: 32px;
				z-index: 999;
			}
			.progress .prgtext {
				color: #286692;
				text-align: center;
				font-size: 13px;
				padding: 9px 0 0;
				width: 540px;
				position: absolute;
				z-index: 1000;
			}
			.progress .prginfo {
				margin: 3px 0;
			}
		</style>
		<div class='progress'>
			<div class='prgtext'><?php echo $dp; ?>% Disk Used</div>
			<div class='prgbar'></div>
			<div class='prginfo'>
				<span style='float: left;'><?php echo "$du of $dt used"; ?></span>
				<span style='float: right;'><?php echo "$df of $dt free"; ?></span>
				<span style='clear: both;'></span>
			</div>
		</div>
	</p>
	<!-- Links -->
	<p>
		<a href="https://showrss.info/?cs=shows" target="_blank">Add or remove a show from the feed.</a> 
		<br>
		<a href="http://nicks-pi.ddns.net:9091/transmission/web/" target="_blank">Manage Downloads.</a>
	</p>	
	<!-- The display table -->
	<p>
		<style type='text/css'>
			.scrollit {
				overflow:scroll;
				
				width:98vw;
				position:fixed;
				bottom: 10px;
				top: 175px;
			}
		</style>
		<button type="button" onclick="updateShowData()">Click me to update RSS feed.</button>
		<div class="scrollit" style="border: thin solid black">
			<p id="showdata">This text will go away.</p>
		</div>
		
	</p>

	

</body>
