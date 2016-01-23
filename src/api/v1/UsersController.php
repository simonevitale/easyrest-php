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

$mysqli = Database::getInstance()->getConnection();

class UsersController extends UsersDatabaseHandler
{
    /**
     * Signs up a new user by e-mail
     *
     * @url POST /user/signup/
     */
    public function signUp() {
		global $messages, $authIssueText, $eMailSendFrom;
		global $mysqli;
		
		$websiteName = Settings::getInstance()->p['websiteName'];
		
		$email    = $_POST['email'];
		$password = $_POST['password'];
		$country  = $_POST['country'];
		
		if(strlen($email) == 0 || strlen(password) == 0) {
			throw new RestException(400, "Wrong or missing parameters.");
		}
	
		$language = "en";
	
		$sql = "SELECT UserStateId FROM User WHERE Email = '$email'";
		$result = $mysqli->query($sql) or die ($authIssueText);
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
				
				// In case any of our lines are larger than 70 characters, we should use wordwrap()
				$newUserMessage = wordwrap($newUserMessage, 70, "\r\n");
						
				$headers =  'From: ' . $eMailSendFrom.'' . "\r\n" .
							'Reply-To: ' . $eMailSendFrom . "\r\n" .
							'X-Mailer: PHP/' . phpversion();
							
				mail($email, "SquizMind Account", $newUserMessage, $headers);
				
				return "OK";
			}
		}
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
		global $mysqli, $eMailSendFrom, $messages;
		
		$email = $_POST['email'];
		
		if(strlen($email) == 0) {
			throw new RestException(400, "Wrong or missing parameters.");
		}
		
		$sql = "SELECT UserId, Language FROM User WHERE Email = '".$email."'";
		
		$result = $mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);
			
			$resetCode = generateRandomString(32);
			
			// Set the code in the database
			parent::CreateResetPasswordCode($row['UserId'], $resetCode);
			
			$forgotPasswordMessage = str_replace("<<IdUser>>", $row['UserId'], $messages[$row[1]]["forgotPasswordMessage"]);
			$forgotPasswordMessage = str_replace("<<ResetCode>>", $resetCode, $forgotPasswordMessage);
			$forgotPasswordMessage = str_replace("<<SendTo>>", $email, $forgotPasswordMessage);
			
			// In case any of our lines are larger than 70 characters, we should use wordwrap()
			$forgotPasswordMessage = wordwrap($forgotPasswordMessage, 70, "\r\n");
					
			$headers =  'From: '.$eMailSendFrom.'' . "\r\n" .
						'Reply-To: '.$eMailSendFrom.'' . "\r\n" .
						'X-Mailer: PHP/' . phpversion();
						
			mail($email, $eMailSubject, $forgotPasswordMessage, $headers);
			
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
		global $mysqli, $messages, $eMailSendFrom;
		
		if($idUser != null && $code != null) {
			$sql = "SELECT Email, PasswordResetDateTime, Language FROM User WHERE PasswordResetToken = '$code' AND UserId = $idUser";
			
			$result = $mysqli->query($sql) or die ($authIssueText);
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

					$result = $mysqli->query($sql) or die ($authIssueText);
					
					$resetPasswordMessage = str_replace("<<NewPassword>>", $newPassword, $messages[$row[2]]["resetPasswordMessage"]);
					$resetPasswordMessage = str_replace("<<SendTo>>", $sendTo, $resetPasswordMessage);
							
					// In case any of our lines are larger than 70 characters, we should use wordwrap()
					$resetPasswordMessage = wordwrap($resetPasswordMessage, 70, "\r\n");
							
					$headers =  'From: '.$eMailSendFrom.'' . "\r\n" .
								'Reply-To: '.$eMailSendFrom.'' . "\r\n" .
								'X-Mailer: PHP/' . phpversion();
								
					mail($sendTo, $eMailSubject, $resetPasswordMessage, $headers);
					
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
		global $mysqli;
		
		$idUser = $_POST['IdUser'];
		$password = $_POST['OldPassword'];
		$newPassword = $_POST['NewPassword'];
		
		$sql  = "UPDATE User SET ";
		$sql .= " PasswordHash = '".$newPassword."' ";
		$sql .= " WHERE UserId = $idUser AND PasswordHash = '".$password."'";
		
		if($mysqli->query($sql) === TRUE) {
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
		
		$user = parent::UserById($userId);
		
		if(isset($_POST['Username']))  $user["Username"] = $_POST['Username'];
		if(isset($_POST['FirstName'])) $user["FirstName"] = $_POST['FirstName'];
		if(isset($_POST['LastName']))  $user["LastName"] = $_POST['LastName'];
		if(isset($_POST['Country']))   $user["Country"] = $_POST['Country'];
		if(isset($_POST['MobilePhone'])) $user["MobilePhone"] = $_POST['MobilePhone'];
		if(isset($_POST['Language']))  $user["Language"] = $_POST['Language'];
		
		return (parent::DbUpdateUser($user) == true) ? $user : "ERROR";
	}
}
