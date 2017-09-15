<?php 
$name = $contactmethod = $email = $application = $antispam = $displaymessage = "";
require '/var/www/html/inc/functions.php';
if (isset ($_POST["antispam"])){
	if ($_POST["antispam"] == "DUCK"){
	$namecheck = ! empty($_POST["name"]);
	$contactmethodcheck = $_POST["contactmethod"] == "email";
	$emailcheck = ! empty($_POST["email"]);
	$emailischecked = $contactmethodcheck ?  $emailcheck : true;

	if ($emailischecked && $namecheck){
		$name = $_POST["name"];	
		$contactmethod = $_POST["contactmethod"];	
		$email = $_POST["email"];	
		$application = $_POST["application"];	
		$antispam = $_POST["antispam"];	
		$to = "admin@lainchan.org";
		$subject = "Lainchan.org Staff Application for " . $name;
		
		$message = "Name: " . $name . "\r\n";
		$message .= "Contact method: " . $contactmethod . "\r\n";
		$message .= $application;
		$message = wordwrap($message, 70, "\r\n");
		
		$source = $emailcheck ? $email : $to;
		$headers = 'From: ' . $source . "\r\n" .
			    'Reply-To: ' . $source . "\r\n" .
				'X-Mailer: PHP/' . phpversion();
		$sent = mail($to, $subject, $message, $headers);
		$sentmessage = $sent ? "was submitted successfully.</p>\r\n" : "was unable to be submitted.</p>\r\n";
		$displaymessage = "<p style=\"text-align:center;\"> Your staff application " . $sentmessage; 
		}	

	}
	else
	{
        $displaymessage =  "<p style=\"text-align:center;\">ANTISPAM VALUE INCORRECT</p>\r\n"; 
	
	}
}
else {
$displaymessage = "<p style=\"text-align:center;\">ANTISPAM NOT SET</p>\r\n"; 
}
$displaymessage .= '<span style="text-align:center; display: block;" >[ <a href="https://lainchan.org/"> Return Home </a> ]</span>';

echo Element('page.html', array(
		'index' => $config['root'],
		'title' => _('Staff Application'),
		'config' => $config,
		'boardlist' => createBoardlist(isset($mod) ? $mod : false),
		'body' => $displaymessage,
		)
	);
?>
