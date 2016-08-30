<?
////////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2016 Simone Vitale
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
//
////////////////////////////////////////////////////////////////////////////////

require_once("./Settings.php");

$authIssueText = "The current operation cannot be processed due to a problem of authorization or wrong or missing params.";
$unsupportedFileMsg = "Unsupported file.";

$websiteName = Settings::getInstance()->p['websiteName'];
$websiteUrl  = Settings::getInstance()->p['websiteUrl'];

$messages = array(
	"en" => array(
		"forgotPasswordMessage" =>  "Hello, \r\n\r\n" .
									"did you request a password reset for your $websiteName account (<<SendTo>>)?\r\n\r\n" .
									"If you requested this password reset, please click on the following link or copy and paste the text into your browser address bar by 24 hours to complete the request:\r\n\r\n" .
									"$websiteUrl/portal/client/ResetPassword?iduser=<<IdUser>>&code=<<ResetCode>>\r\n\r\n" .
									"If you didn't, please ignore this message.\r\n\r\n" .
									"Thanks,\r\n\r\n$websiteName Support Team\r\n",
									
		"resetPasswordMessage" =>  	"It seems you requested a password reset for your $websiteName account 	(<<SendTo>>).\r\n\r\n" .
									"Your new password is <<NewPassword>> and you can change it logging in the administration web site in your user area \r\n\r\n" .
									"Thanks,\r\n\r\n$websiteName Support Team\r\n",
									
		"createUserMessage" =>  	"Hello,\r\n\r\nthat's great! It seems you have joined us creating a new $websiteName account.\r\n\r\n" .
									"If you requested an account, please confirm clicking on the following link or copy and paste the text into your browser address bar by 24 hours to complete the registration:\r\n\r\n" .
									"$websiteUrl/portal/client/ConfirmAccount?email=<<SendTo>>&code=<<RegistrationCode>>\r\n\r\n" .
									"If you didn't, please ignore this message.\r\n\r\n" .
									"Thanks,\r\n\r\n$websiteName Support Team\r\n",
									
		"signUpMailSubject" => 		"new account"
		
	),
	
	"it" => array(
		"forgotPasswordMessage" =>  "Hai richiesto il reset della password per il tuo account $websiteName (<<SendTo>>)?\r\n\r\n" .
									"Clicca sul link seguente o copia e incollalo nella barra degli indirizzi del tuo browser entro 24 ore per completare la richiesta di reimpostazione password:\r\n\r\n" .
									"$websiteUrl/portal/client/ResetPassword?iduser=<<IdUser>>&code=<<ResetCode>>\r\n\r\n" .
									"Se non avessi richiesto la reimpostazione della password, per cortesia ignora questo messaggio.\r\n\r\n" .
									"Grazie,\r\n\r\n$websiteName Support Team\r\n",
									
		"resetPasswordMessage" =>  "La password del tuo account $websiteName (<<SendTo>>) è stata reimpostata come da te richiesto.\r\n\r\n" .
									"La tua nuova password è <<NewPassword>> e puoi cambiarla in ogni momento semplicemente loggandoti al portale di amministrazione tramite l'apposito form nella tua area utente.\r\n\r\n" .
									"Grazie,\r\n\r\n$websiteName Support Team\r\n",
									
		"createUserMessage" =>  	"Ciao,\r\n\r\nè fantastico che tu ti sia unito a noi registrando un nuovo account $websiteName.\r\n\r\n" .
									"Clicca sul link seguente o copia e incollalo nella barra degli indirizzi del tuo browser entro 24 ore per completare la registrazione:\r\n\r\n" .
									"$websiteUrl/portal/client/ConfirmAccount?email=<<SendTo>>&code=<<RegistrationCode>>\r\n\r\n" .
									"Se non avessi richiesto la registrazione, per cortesia ignora questo messaggio.\r\n\r\n" .
									"Grazie,\r\n\r\n$websiteName Support Team\r\n",
									
		"signUpMailSubject" => 		"nuovo account"
	)
);
	
?>