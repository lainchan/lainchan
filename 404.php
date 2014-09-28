<!DOCTYPE html>
<html>
	<meta charset="UTF-8">
	<head>
		<title>404</title>
		<style type="text/css">
		       
		  html, body {
				margin: 0px;
				padding: 0px;
				width: 100%;
				height: 100%;
				text-align: center;
				background-color: black;
			        color: red;
			        background-image: url(/stylesheets/img/7.gif), url(/stylesheets/img/idx2.gif);
			        background-repeat: no-repeat, repeat;
			background-size: contain, auto;
			background-position: right top, left top;
		        
			}
		       
		  html {background: black!important;background-image: url(/stylesheets/img/7.gif)!important;background-repeat: no-repeat!important;background-size: contain!important;background-position: right top!important;}

			#head {
				font-size: 100px;
				font-weight: 900;
				font-family: sans-serif;
				background-color: black;
			        border-radius: 50px;
			        padding: 25px;
			        position: relative;
			        top: calc(50% - .75em);
			        display: block;
			        width: 2em;
			        margin: 0 auto;
			        height: 0.9em;
			        animation: spin 3.2s infinite linear, flash 0.4s infinite linear;
			        -webkit-animation: spin 3.2s infinite linear, flash 0.4s infinite linear;
		  box-shadow: 0px 0px 10px black;
}
			@keyframes spin {
			        0%  {-moz-transform: rotate(0deg);}
			        100% {-moz-transform: rotate(360deg);}   
			}
			@-webkit-keyframes spin {
			        0%  {-moz-transform: rotate(0deg);}
			        100% {-moz-transform: rotate(360deg);}   
			}
		        @keyframes flash {
		   0% {width: 5em;}
		  100% {width: 2em;}
		  }
			
		       .blink {
			        animation: blink 0.4s linear infinite;
			        -webkit-animation: blink 0.4s linear infinite;
			        color: white;
                                font-size: 50px;
			        position: absolute;
			       top: calc(50% - 0.5em);
			width: 100%;
			left: 0px;
			z-index: 2;
			font-family: helvetica;
			font-weight: 800;
			letter-spacing: 0.2em;
			text-shadow: 0px 0px 10px black;
			
			}
			@keyframes blink {
			        0% { letter-spacing: 0.4em; font-size: 60px;}
		                100% {letter-spacing: 0.2em; font-size: 50px;}
			}
			@-webkit-keyframes blink {
			        to { visibility: hidden; }
			}

			iframe {
				width: 75%;
				height: 75%;
				margin: auto auto;
				display: block;
				border: none;
			}
		</style>
		<script type="text/javascript">
		        var colors = ["rgb(255, 0, 0)","rgb(255, 0, 255)","rgb(0, 0, 255)","rgb(0, 255, 255)","rgb(0, 255, 0)","rgb(255, 255, 0)"];
			function seizure(){
			if (document.title == "404")
			    document.title = "PARTY HARD NIGGERS";
			else document.title = "404";
			var i = colors[Math.floor(Math.random()*colors.length)];
			while(i == document.getElementById("head").style.color)
			{
			   i = colors[Math.floor(Math.random()*colors.length)];
			}
			document.getElementById("head").style.color = i;
			//document.body.style.backgroundColor = i;
			
		}
		</script>
	</head>
	<body onload="window.setInterval('seizure()', 400);">
	        <span class="blink">PARTY HARD</span>
		<span id="head"><span>404</span><br/></span>
		<iframe id="video"></iframe>
		
		  <audio autoplay loop>
		    <source src="/audio/48kbps.ogg" type="audio/ogg">
		      <source src="/audio/48kbps.mp3" type="audio/mpeg">
			Your browser does not support the audio element.
			</audio> 
		
	</body>
</html>
