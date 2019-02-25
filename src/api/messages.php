<?
	require_once("./Settings.php");
	
	$authIssueText = "The current operation cannot be processed due to a problem of authorization or wrong or missing params.";
	$unsupportedFileMsg = "Unsupported file.";
	
	$websiteName = Settings::getInstance()->p['websiteName'];
	$websiteUrl = Settings::getInstance()->p['websiteUrl'];
	$portalUrl = Settings::getInstance()->p['portalUrl'];

	$eMailSendFrom = "support@".$websiteDomain;
	
	$messages = array(
		"en" => array(
			"resetPasswordMessage" =>  	"It seems you requested a password reset for your $websiteName account 	(<<SendTo>>).<br><br>" .
										"Your new password is <b><<NewPassword>></b> and you can change it logging in the administration web site in your user area <br><br>" .
										"Thanks,<br><br>$websiteName Support Team<br>",
										
			"confirmAccount" =>			"I'm in! :)",
			
			"forgotPassword" =>			"I forgot my password"
			
		),
		
		"it" => array(
			"resetPasswordMessage" =>  "La password del tuo account $websiteName (<<SendTo>>) è stata reimpostata come da te richiesto.<br><br>" .
										"La tua nuova password è <b><<NewPassword>></b> e puoi cambiarla in ogni momento semplicemente loggandoti al portale di amministrazione tramite l'apposito form nella tua area utente.<br><br>" .
										"Grazie,<br>$websiteName Support Team<br>",
										
			"confirmAccount" =>			"Ci sto! :)",
			
			"forgotPassword" =>			"Ho dimenticato la password"
		)
	);
	
?>