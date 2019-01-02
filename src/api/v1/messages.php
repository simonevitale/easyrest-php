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
			"forgotPasswordMessage" =>  "Hello,<br>" .
										"have you requested to reset your password for your $websiteName account (<<SendTo>>)?<br><br>" .
										"If so, please click on the following link or copy and paste the text into your browser address bar by 24 hours to complete the request:<br>" .
										//"$portalUrl/api/v1/users/resetpassword/<<IdUser>>/<<ResetCode>><br><br>" .
										"$portalUrl/?action=ResetPassword&iduser=<<IdUser>>&code=<<ResetCode>><br><br>" .
										"If you didn't, please ignore this message.<br><br>" .
										"Thanks,<br><br>$websiteName Support Team<br>",
										
			"resetPasswordMessage" =>  	"It seems you requested a password reset for your $websiteName account 	(<<SendTo>>).<br><br>" .
										"Your new password is <b><<NewPassword>></b> and you can change it logging in the administration web site in your user area <br><br>" .
										"Thanks,<br><br>$websiteName Support Team<br>",
										
			"createUserMessage" =>  	"Hello,<br><br>that's great you are joining us creating a new $websiteName account.<br><br>" .
										"To confirm your registration, click on the following link or copy and paste the text into your browser address bar by 24 hours<br><br>" .
										"$portalUrl/?action=ConfirmAccount&email=<<SendTo>>&code=<<RegistrationCode>><br><br>" .
										"If you haven't requested to register to our service, please ignore this message.<br><br>" .
										"Thanks,<br><br>$websiteName Support Team<br>",
										
			"confirmAccount" =>			"I'm in! :)",
			
			"forgotPassword" =>			"I forgot my password"
			
		),
		
		"it" => array(
			"forgotPasswordMessage" =>  "Hai richiesto il reset della password per il tuo account $websiteName (<<SendTo>>)?<br><br>" .
										"Clicca sul link seguente o copia e incollalo nella barra degli indirizzi del tuo browser entro 24 ore per completare la richiesta di reimpostazione password:<br>" .
										"$portalUrl/?action=ResetPassword&iduser=<<IdUser>>&code=<<ResetCode>><br><br>" .
										//"$portalUrl/portal/api/v1/users/resetpassword/<<IdUser>>/<<ResetCode>><br><br>" .
										"Se non avessi richiesto la reimpostazione della password, per cortesia ignora questo messaggio.<br><br>" .
										"Grazie,<br>$websiteName Support Team<br>",
										
			"resetPasswordMessage" =>  "La password del tuo account $websiteName (<<SendTo>>) è stata reimpostata come da te richiesto.<br><br>" .
										"La tua nuova password è <b><<NewPassword>></b> e puoi cambiarla in ogni momento semplicemente loggandoti al portale di amministrazione tramite l'apposito form nella tua area utente.<br><br>" .
										"Grazie,<br>$websiteName Support Team<br>",
										
			"createUserMessage" =>  	"Ciao,<br><br>è fantastico che tu ti sia unito a noi registrando un nuovo account $websiteName.<br><br>" .
										"Clicca sul link seguente o copia e incollalo nella barra degli indirizzi del tuo browser entro 24 ore per completare la registrazione:<br><br>" .
										"$portalUrl/?action=ConfirmAccount&email=<<SendTo>>&code=<<RegistrationCode>><br><br>" .
										"Se non avessi richiesto la registrazione, per cortesia ignora questo messaggio.<br><br>" .
										"Grazie,<br><br>$websiteName Support Team<br>",
										
			"confirmAccount" =>			"Ci sto! :)",
			
			"forgotPassword" =>			"Ho dimenticato la password"
		)
	);
	
?>