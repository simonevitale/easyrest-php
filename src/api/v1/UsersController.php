<?php
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

use \Jacwright\RestServer\RestException;

require_once("UsersDatabaseHandler.php");
require_once("functions.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/src/Exception.php';
require 'libs/PHPMailer/src/PHPMailer.php';
require 'libs/PHPMailer/src/SMTP.php';

class UsersController extends UsersDatabaseHandler
{
	private function mailUser($email, $htmlMessage, $subject) {
		$replyTo = Settings::getInstance()->p['email'];
		
		$this->mailUserReply($email, $htmlMessage, $subject, $replyTo);
	}
	
	private function mailUserReply($email, $htmlMessage, $subject, $replyTo) {
		$mailer = new PHPMailer;
		$websiteName = Settings::getInstance()->p['websiteName'];
		
		$mailer->isSMTP();                                      			// Set mailer to use SMTP
		$mailer->Host = Settings::getInstance()->p['emailHost'];			// Specify main and backup SMTP servers
		$mailer->SMTPAuth = true;                               			// Enable SMTP authentication
		$mailer->Username = Settings::getInstance()->p['email'];			// SMTP username
		$mailer->Password = Settings::getInstance()->p['emailPassword'];	// SMTP password
		$mailer->SMTPSecure = 'ssl';                            			// Enable TLS encryption, `ssl` also accepted
		$mailer->Port = Settings::getInstance()->p['emailPort'];			// TCP port to connect to

		$mailer->addAddress($email);
		$mailer->addReplyTo($replyTo, 'SquizMaster');
		$mailer->setFrom(Settings::getInstance()->p['email'], 'SquizMaster');

		$mailer->isHTML(true);                                  			// Set email format to HTML
		$mailer->Subject = $subject;
		$mailer->Body    = $htmlMessage;
		//$mailer->AltBody = $newUserMessage; // Plain text body for non-HTML mail clients

		if(!$mailer->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mailer->ErrorInfo;
			
			return "ERROR";
		}
		
		return "OK";
	}
	
	/**
     * Signs up a new user by e-mail
     *
     * @url GET /user/facebook/signup/
     */
    public function facebookSignUp() {
		echo "OK";
	}
	
	/**
     * Signs up a new user by e-mail
     *
     * @url POST /user/signup/
     */
    public function signUp() {
		global $messages, $authIssueText;
		
		$websiteName = Settings::getInstance()->p['websiteName'];
		
		$email    = $_POST['email'];
		$password = $_POST['password'];
		$countryId  = $_POST['country'];
		$timeZone  = $_POST['timeZone'];
		$organization = $_POST['organization'];
		$code  	  = (isset($_POST['code'])) ? $_POST['code'] : "";
		$language = (isset($_POST['language'])) ? $_POST['language'] : "en";
		
		if(strlen($email) == 0 || strlen($password) == 0) {
			throw new RestException(400, "Wrong or missing parameters.");
		} else if(strcmp($code, "") != 0 && strcmp($code, "AEGEE") != 0 && strcmp($code, "steam18") != 0 && strcmp($code, "friends") != 0) { // Check here if the code is valid
			throw new RestException(412, "Code $_POST[code] not valid.");
		}
		
		if($language != "en" && $language != "it")
			$language = "en";
	
		$sql = "SELECT UserStateId FROM User WHERE Email = '$email'";
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);
		$row = mysqli_fetch_array($result);
		
		if($recordsCount > 0) { // && $row['UserStateId'] != 0) {
			throw new RestException(409, "Conflict - The user already exists.");
		} else {
			$registrationCode = generateRandomString(10);
			
			// Assign basic license if no promo code was provided
			$licenseId = (strcmp($code, "") == 0) ? 1 : 2;
			
			// TODO: CONFIRM REGISTRATION TEMPORARILY DISABLED: USERS ARE ALREADY ACTIVE
			$newUserId = parent::CreateUser($email, $password, $registrationCode, $countryId, $timeZone, $language, 2, $code, $organization, 1, $licenseId);
		
			if($newUserId > 0 && strcmp($code, "AEGEE") == 0) {
				// Add Theme Asset for AEGEE
				parent::AddAssetToUser(5, $newUserId); // Associate AEGEE Theme to the account
				parent::AddAssetToUser(12, $newUserId); // Associate AEGEE Category to the account
			}
			
			// Html Template
			$portalUrl = Settings::getInstance()->p['portalUrl'];
			$newUserMessageHtml = file_get_contents('templates/sm-template.html');
			if($language == "it")
				$newUserMessageHtml = str_replace("{Content}", file_get_contents('templates/signup-it.html'), $newUserMessageHtml);
			else
				$newUserMessageHtml = str_replace("{Content}", file_get_contents('templates/signup-en.html'), $newUserMessageHtml);
			$newUserMessageHtml = str_replace("{ConfirmAccount}", $messages[$language]["confirmAccount"], $newUserMessageHtml);
			$newUserMessageHtml = str_replace("{ConfirmAccountUrl}", "$portalUrl/?action=ConfirmAccount&email=$email&code=$registrationCode", $newUserMessageHtml);
			
			$this->mailUser($email, $newUserMessageHtml, "SquizMaster");
			$this->mailUser(Settings::getInstance()->p['email'], $email . " just signed up! ", "SquizMaster New User");
		}
		
		return "OK";
	}
	
    /**
     * Contact Us Form
     *
     * @url POST /user/contactus/
     */
	public function contactUs() {
		$name = $_POST["name"];
		$eMailFrom = $_POST["email"];
		$phone = $_POST["phone"];
		$why = $_POST["why"];
		
		$message = "From: ".$_POST["name"]." [ $eMailFrom ]<br />";
		if(strlen($phone) > 0)
			$message .= "Phone: $phone <br />";
		$message .= "<br />".rawurldecode($_POST["why"])."<br />";
		$message .= "<br />".rawurldecode($_POST["message"]);
		
		$this->mailUserReply(Settings::getInstance()->p['email'], $message, "SquizMaster", $eMailFrom);
		
		return "OK";
	}
	
    /**
     * Confirm a new user registration
     *
     * @url GET /user/confirmregistration/
     */
    public function confirmRegistration() {
		$email = $_GET['email'];
		$registrationToken = $_GET['registrationCode'];
		
		if($email != null && $registrationToken != null
				&& parent::ConfirmUserRegistration($email, $registrationToken)) {
			return "OK";
		} else {
			throw new RestException(403, "Forbidden - The user is already confirmed or the specified data is not valid.");
		}
	}
	
    /**
     * Logs in a user with the given username and password POSTed. Though true
     * REST doesn't believe in sessions, it is often desirable for an AJAX server.
     *
     * @url POST /user/login/
     */
    public function login() {
		$agent = (isset($_POST['agent'])) ? $_POST['agent'] : "";
		$agentVersion = (isset($_POST['agentVersion'])) ? $_POST['agentVersion'] : "";
		
		$authUserId = parent::CheckAuthentication();
		$user = parent::UserById($authUserId);
		
		// Set Last Login Date Time
		date_default_timezone_set('Europe/London');
		$user["LastLoginDateTime"] = date("Y-m-d H:i:s");
		parent::DbUpdateUser($user);
		
		parent::AppendLog("login", $agent, $agentVersion, $user['Email']);
		
		return $user;
    }
	
    /**
     * Logs out a user with the given username and password POSTed. 
     *
     * @url POST /user/logout/
     */
    public function logout() {
		$agent = (isset($_POST['agent'])) ? $_POST['agent'] : "";
		$agentVersion = (isset($_POST['agentVersion'])) ? $_POST['agentVersion'] : "";
		
		$authUserId = parent::CheckAuthentication(false);
		
		if($authUserId > 0) {
			$user = parent::UserById($authUserId);
			parent::AppendLog("logout", $agent, $agentVersion, $user['Email']);
		}
	}

    /**
     * Get all the Users
	 * Requires authentication
	 *
     * @url GET /users
	 */
	public function getUsers() {
		//$authIdUser = parent::CheckAuthentication(true);
		
		if(isset($_GET['from']))  $from = $_GET['from']; else $from = -1;
		if(isset($_GET['count'])) $count = $_GET['count']; else $count = -1;
		
		return parent::Users($from, $count);
	}
	
    /**
     * Gets the user by userId
     *
     * @url GET /user/$userId
     */
    public function getUser($userId) {
		return parent::UserById($userId);
    }
	
    /**
     * Gets the user image by userId
     *
     * @url GET /user/$userId/image
     */
    public function getUserImage($userId) {
		$user = parent::UserById($userId);
		
		$placeholderPath = "../../assets/images/placeholder.png";
		
		$userUserFolder = Settings::getInstance()->p['userUserFolder'];
		$portalFolder = Settings::getInstance()->p['portalFolder'];
		
		$userImagePath = parent::GetImageUrl($user['UserId'], $user['Image'], $userUserFolder, false, false);
		
		$filePath = ($userImagePath != null && strlen($userImagePath) > 0) ? "../../".$userImagePath : "";
		
		if(file_exists($filePath)) {
			parent::DeliverFile($filePath, $user["Image"]);
		} else {
			parent::DeliverFile($placeholderPath, "placeholder.png");
			//throw new RestException(404, "Image file not found.");
		}
    }
	
    /**
     * Forgot Password. 
     *
     * @url POST /user/forgotpassword/
     */
    public function forgotPassword() {	
		global $messages;
		
		$websiteName = Settings::getInstance()->p['websiteName'];
		
		$email = $_POST['email'];
		
		if(strlen($email) == 0) {
			throw new RestException(400, "Wrong or missing parameters.");
		}
		
		$sql = "SELECT UserId, Language FROM User WHERE Email = '".$email."'";
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);
			
			$resetCode = generateRandomString(32);
			
			// Set the code in the database
			parent::CreateResetPasswordCode($row['UserId'], $resetCode);
			
			//$forgotPasswordMessage = str_replace("<<IdUser>>", $row['UserId'], $messages[$row[1]]["forgotPasswordMessage"]);
			//$forgotPasswordMessage = str_replace("<<ResetCode>>", $resetCode, $forgotPasswordMessage);
			//$forgotPasswordMessage = str_replace("<<SendTo>>", $email, $forgotPasswordMessage);
			
			$language = $row['Language'];
			
			$portalUrl = Settings::getInstance()->p['portalUrl'];
			$forgotPasswordMessage = file_get_contents('templates/sm-template.html');
			if($language == "it")
				$forgotPasswordMessage = str_replace("{Content}", file_get_contents('templates/forgotpassword-it.html'), $forgotPasswordMessage);
			else
				$forgotPasswordMessage = str_replace("{Content}", file_get_contents('templates/forgotpassword-en.html'), $forgotPasswordMessage);

			$forgotPasswordMessage = str_replace("{ForgotPassword}", $messages[$language]["forgotPassword"], $forgotPasswordMessage);
			$forgotPasswordMessage = str_replace("{ForgotPasswordUrl}", "$portalUrl/?action=ResetPassword&iduser=$row[UserId]&code=$resetCode", $forgotPasswordMessage);
			
			$this->mailUser($email, $forgotPasswordMessage, "SquizMaster");
			
			return "OK";
		} else {
			// User not found
			throw new RestException(403, "Forbidden - The user " . $email . " was not found." . $sql);
		}
	}
	
    /**
     * Reset Password. 
     *
     * @url GET /user/resetpassword/$idUser/$code
     */
    public function resetPassword($idUser = null, $code = null) {
		global $messages;
		
		$websiteName = Settings::getInstance()->p['websiteName'];
		
		if($idUser != null && $code != null) {
			$sql = "SELECT Email, PasswordResetDateTime, Language FROM User WHERE PasswordResetToken = '$code' AND UserId = $idUser";
			
			$result = $this->mysqli->query($sql) or die ($authIssueText);
			$recordsCount = mysqli_num_rows($result);
		
			if($recordsCount >= 1 && $result != null) {
				$row = mysqli_fetch_array($result);
				
				$resetTime = $row['PasswordResetDateTime'];
				$curtime = time();

				// Reset password allowed by 24 hours
				if(($curtime - $resetTime) < 86400) {
					$sendTo = $row['Email'];
					$newPassword = generateRandomString(8);
						
					// Change Password in the db
					$sql  = " UPDATE User SET ";
					$sql .= " PasswordHash = '".md5($newPassword)."', ";
					$sql .= " PasswordResetToken = '' ";
					$sql .= " WHERE UserId = $idUser ";

					$result = $this->mysqli->query($sql) or die ($authIssueText);
					
					$resetPasswordMessage = str_replace("<<NewPassword>>", $newPassword, $messages[$row[2]]["resetPasswordMessage"]);
					$resetPasswordMessage = str_replace("<<SendTo>>", $sendTo, $resetPasswordMessage);
					
					$this->mailUser($sendTo, $resetPasswordMessage, "SquizMaster");
					
					return "OK";
				} else {
					throw new RestException(403, "Expired. Reset Timestamp: " . $row['ResetPasswordDateTime']);
				}
			} else {
				throw new RestException(403, "User not found.");
			}
		}
	}
	
    /**
     * Change Password. 
     *
     * @url POST /user/changepassword
     */
    public function changePassword() {
		$userId = $_POST['UserId'];
		$password = $_POST['OldPassword'];
		$newPassword = $_POST['NewPassword'];
		
		if(strlen($password) == 0 || strlen($newPassword) == 0) {
			throw new RestException(400, "Wrong or missing parameters.");
		}
		
		$sql  = "UPDATE User SET ";
		$sql .= " PasswordHash = '".$newPassword."' ";
		$sql .= " WHERE UserId = $userId AND PasswordHash = '".$password."'";
		
		if($this->mysqli->query($sql) != null && $this->mysqli->affected_rows > 0) {
			return "OK";
		} else {
			throw new RestException(403, "Forbidden - Couldn't change the current password");
		}
	}

    /**
     * Update User
     * 
     * @url POST /user/update/
     */
    public function updateUser() {
		$userId = parent::CheckAuthentication();
		
		$userUserFolder = Settings::getInstance()->p['userUserFolder'];
		
		$user = parent::UserById($userId);
		
		$isImageUploading = (isset($_FILES['NewImage']) && is_uploaded_file($_FILES['NewImage']['tmp_name'])) ? 1 : 0;
		
		$destinationDirectory = "../../".parent::GetImageUrl($userId, "", $userUserFolder, false, false, true)."/";

		if(strlen($_POST['Image']) == 0 || $isImageUploading) {
			$this->UnlinkRemovedUserImages($userId, $user['Image']);
		}
		
		// Upload new image
		if($isImageUploading == 1) {
			$image = uploadImage($_FILES['NewImage'], $destinationDirectory, 350);
		}
		
		if(isset($_POST['Username']))  $user["Username"] = $_POST['Username'];
		if(isset($_POST['FirstName'])) $user["FirstName"] = $_POST['FirstName'];
		if(isset($_POST['LastName']))  $user["LastName"] = $_POST['LastName'];
		if(isset($_POST['Country']))   $user["Country"] = $_POST['Country'];
		if(isset($_POST['TimeZone']))   $user["TimeZone"] = $_POST['TimeZone'];
		if(isset($_POST['Image']) && $isImageUploading != 1) $user["Image"] = $_POST['Image']; else $user["Image"] = $image;
		if(isset($_POST['MobilePhone'])) $user["MobilePhone"] = $_POST['MobilePhone'];
		if(isset($_POST['Language']))  $user["Language"] = $_POST['Language'];
		if(isset($_POST['PortalLanguage']))  $user["PortalLanguage"] = $_POST['PortalLanguage'];
		
		// Return the up-to-date user
		return (parent::DbUpdateUser($user) == true) ? parent::UserById($userId) : "ERROR";
	}
	
    /**
     * Delete User
     * 
     * @url POST /user/delete
     */
    public function DeleteUser() {
		if(isset($_POST['UserId']))
			$userId = $_POST['UserId'];
		else
			throw new RestException(400, "Wrong or missing parameters.");
		
		$authUserId = parent::CheckAuthentication(false, false, true); // Check if admin or owned by the user
		
		if($authUserId <= 0 || $authUserId != $userId) {
			throw new RestException(401, "$authUserId: Unauthorized. Authentication credentials are missing or incorrect for user ".$_SERVER['PHP_AUTH_USER']);
		}
		
		$path = "../../".Settings::getInstance()->p['userUploadFolder']."/$userId";
		
		removeDirectory($path);
		
		parent::DeleteUserDb($userId);
		
		return "OK";
	}
	
	private function UnlinkRemovedUserImages($userId, $image) {
		$userUserFolder = Settings::getInstance()->p['userUserFolder'];
		
		$imageFileToRemove = "../../".parent::GetImageUrl($userId, $image, $userUserFolder, false, false);
		$imageThumbnailFileToRemove = "../../".parent::GetImageUrl($userId, $image, $userUserFolder, true, false);
		
		if(strlen($image) > 0) {
			if(file_exists($imageFileToRemove))
				unlink($imageFileToRemove);
			if(file_exists($imageThumbnailFileToRemove))
				unlink($imageThumbnailFileToRemove);
		}
	}
}
