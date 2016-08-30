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

require 'libs/PHPMailer/PHPMailerAutoload.php';

class UsersController extends UsersDatabaseHandler
{
    /**
     * Signs up a new user by e-mail
     *
     * @url POST /user/signup/
     */
    public function signUp() {
		global $messages, $authIssueText;
		
		$websiteName = Settings::getInstance()->p['websiteName'];
		//$eMailSendFrom = Settings::getInstance()->p['supportEmail'];
		
		$email    = $_POST['email'];
		$password = $_POST['password'];
		$country  = $_POST['country'];
		
		if(strlen($email) == 0 || strlen(password) == 0) {
			throw new RestException(400, "Wrong or missing parameters.");
		}
	
		$language = "it";// Settings::getInstance()->p['language'];
	
		$sql = "SELECT UserStateId FROM User WHERE Email = '$email'";
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);
		$row = mysqli_fetch_array($result);
		
		if($recordsCount > 0 && $row['UserStateId'] != 0) {
			throw new RestException(403, "Forbidden - The user already exists." . $sql);
		} else {
			$registrationCode = generateRandomString(10);
			
			if(!parent::CreateUser($email, $password, $registrationCode, $country, $language)) {
				throw new RestException(400, "Bad Request - The request was invalid or cannot be otherwise served.");
			} else {
				$newUserMessage = $messages[$language]["createUserMessage"];
				$newUserMessage = str_replace("<<SendTo>>", $email, $newUserMessage);
				$newUserMessage = str_replace("<<RegistrationCode>>", $registrationCode, $newUserMessage);
				
				$mailer = new PHPMailer;
				
				$mailer->isSMTP();                                      			// Set mailer to use SMTP
				$mailer->Host = Settings::getInstance()->p['emailHost'];			// Specify main and backup SMTP servers
				$mailer->SMTPAuth = true;                               			// Enable SMTP authentication
				$mailer->Username = Settings::getInstance()->p['email'];			// SMTP username
				$mailer->Password = Settings::getInstance()->p['emailPassword'];	// SMTP password
				//$mailer->SMTPSecure = 'tls';                            			// Enable TLS encryption, `ssl` also accepted
				$mailer->Port = Settings::getInstance()->p['emailPort'];			// TCP port to connect to

				$mailer->setFrom(Settings::getInstance()->p['email'], 'Support');
				$mailer->addAddress($email);
				$mailer->addReplyTo(Settings::getInstance()->p['email'], 'Information');
				
				$mailer->isHTML(false);                                  			// Set email format to HTML

				$mailer->Subject = $websiteName. ' ';
				$mailer->Body    = $newUserMessage; // HTML message body <b>in bold!</b>
				$mailer->AltBody = $newUserMessage; // Plain text body for non-HTML mail clients

				if(!$mailer->send()) {
					echo 'Message could not be sent.';
					echo 'Mailer Error: ' . $mailer->ErrorInfo;
					
					return "ERROR";
				}

				return "OK";
			}
		}
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
		
		$message = "From: ".$_POST["name"]." [ $eMailFrom ]\n";
		if(strlen($phone) > 0)
			$message .= "Phone: $phone \n";
		$message .= "\n\n".$_POST["message"];
		
		$websiteName = Settings::getInstance()->p['websiteName'];
		
		$mailer = new PHPMailer;
		
		$mailer->isSMTP();                                      			// Set mailer to use SMTP
		$mailer->Host = Settings::getInstance()->p['emailHost'];			// Specify main and backup SMTP servers
		$mailer->SMTPAuth = true;                               			// Enable SMTP authentication
		$mailer->Username = Settings::getInstance()->p['email'];			// SMTP username
		$mailer->Password = Settings::getInstance()->p['emailPassword'];	// SMTP password
		//$mailer->SMTPSecure = 'tls';                            			// Enable TLS encryption, `ssl` also accepted
		$mailer->Port = Settings::getInstance()->p['emailPort'];			// TCP port to connect to

		$mailer->setFrom(Settings::getInstance()->p['email'], 'Support');
		$mailer->addAddress($eMailFrom);
		$mailer->addReplyTo($eMailFrom, 'Information');
		
		$mailer->isHTML(false);                                  				// Set email format to HTML

		$mailer->Subject = $websiteName. ' ';
		$mailer->Body    = $message; // HTML message body <b>in bold!</b>
		$mailer->AltBody = $message; // Plain text body for non-HTML mail clients

		if(!$mailer->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mailer->ErrorInfo;
			
			return "ERROR";
		}
		
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
		
		$authUserId = parent::CheckAuthentication();
		$user = parent::UserById($authUserId);
		
		parent::AppendLog("login", '', $agent, $user['Email']);
		
		return $user;
    }
	
    /**
     * Logs out a user with the given username and password POSTed. 
     *
     * @url POST /user/logout/
     */
    public function logout() {
		$agent = (isset($_POST['agent'])) ? $_POST['agent'] : "";
		
		$authUserId = parent::CheckAuthentication(false);
		
		if($authUserId > 0) {
			$user = parent::UserById($authUserId);
			parent::AppendLog("logout", '', $agent, $user['Email']);
		}
	}

    /**
     * Gets the user by id
     *
     * @url GET /user/$idUser
     */
    public function getUser($idUser) {
		return parent::UserById($idUser);
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
			
			$forgotPasswordMessage = str_replace("<<IdUser>>", $row['UserId'], $messages[$row[1]]["forgotPasswordMessage"]);
			$forgotPasswordMessage = str_replace("<<ResetCode>>", $resetCode, $forgotPasswordMessage);
			$forgotPasswordMessage = str_replace("<<SendTo>>", $email, $forgotPasswordMessage);
			
			$mailer = new PHPMailer;
			
			$mailer->isSMTP();                                      			// Set mailer to use SMTP
			$mailer->Host = Settings::getInstance()->p['emailHost'];			// Specify main and backup SMTP servers
			$mailer->SMTPAuth = true;                               			// Enable SMTP authentication
			$mailer->Username = Settings::getInstance()->p['email'];			// SMTP username
			$mailer->Password = Settings::getInstance()->p['emailPassword'];	// SMTP password
			//$mailer->SMTPSecure = 'tls';                            			// Enable TLS encryption, `ssl` also accepted
			$mailer->Port = Settings::getInstance()->p['emailPort'];			// TCP port to connect to

			$mailer->setFrom(Settings::getInstance()->p['email'], 'Support');
			$mailer->addAddress($email);
			
			$mailer->isHTML(false);                                  			// Set email format to HTML

			$mailer->Subject = $websiteName. ' ';
			$mailer->Body    = $forgotPasswordMessage; // HTML message body <b>in bold!</b>
			$mailer->AltBody = $forgotPasswordMessage; // Plain text body for non-HTML mail clients

			//$mailer->SMTPDebug = 1;

			if(!$mailer->send()) {
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $mailer->ErrorInfo;
				
				return "ERROR";
			}
			
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
					
					$mailer = new PHPMailer;
					
					$mailer->isSMTP();                                      			// Set mailer to use SMTP
					$mailer->Host = Settings::getInstance()->p['emailHost'];			// Specify main and backup SMTP servers
					$mailer->SMTPAuth = true;                               			// Enable SMTP authentication
					$mailer->Username = Settings::getInstance()->p['email'];			// SMTP username
					$mailer->Password = Settings::getInstance()->p['emailPassword'];	// SMTP password
					//$mailer->SMTPSecure = 'tls';                            			// Enable TLS encryption, `ssl` also accepted
					$mailer->Port = Settings::getInstance()->p['emailPort'];			// TCP port to connect to

					$mailer->setFrom(Settings::getInstance()->p['email'], 'Support');
					$mailer->addAddress($sendTo);
					
					$mailer->isHTML(false);                                  			// Set email format to HTML

					$mailer->Subject = $websiteName. ' ';
					$mailer->Body    = $resetPasswordMessage; // HTML message body <b>in bold!</b>
					$mailer->AltBody = $resetPasswordMessage; // Plain text body for non-HTML mail clients

					if(!$mailer->send()) {
						echo 'Message could not be sent.';
						echo 'Mailer Error: ' . $mailer->ErrorInfo;
						
						return "ERROR";
					}
					
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
		if(isset($_POST['Image']) && $isImageUploading != 1) $user["Image"] = $_POST['Image']; else $user["Image"] = $image;
		if(isset($_POST['MobilePhone'])) $user["MobilePhone"] = $_POST['MobilePhone'];
		if(isset($_POST['Language']))  $user["Language"] = $_POST['Language'];
		
		// Return the up-to-date user
		return (parent::DbUpdateUser($user) == true) ? parent::UserById($userId) : "ERROR";
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
