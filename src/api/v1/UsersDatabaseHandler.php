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

$mysqli = Database::getInstance()->getConnection();
	
/**
 * Manages the database operations about users
 *
 * @author Simone Vitale
 */
class UsersDatabaseHandler extends DatabaseHandler
{
	public function UserById($userId) {
		global $mysqli, $authIssueText;

		$sql = "SELECT UserId, Email, Username, FirstName, LastName, Country, RegistrationDateTime, LastLoginDateTime, UserStateId, LoginAttempts, MobilePhone, Language, RoleId FROM User WHERE UserId = $userId";

		$result = $mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = null;

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);

			$data = array(	'UserId' => intval($row['UserId']),
							'Email' => $row['Email'],
							'Username' => $row['Username'],
							'FirstName' => $row['FirstName'],
							'LastName' => $row['LastName'],
							'Country' => $row['Country'],
							'RegistrationDateTime' => $row['RegistrationDateTime'],
							'LastLoginDateTime' => $row['LastLoginDateTime'],
							'UserStateId' => intval($row['UserStateId']),
							'LoginAttempts' => intval($row['LoginAttempts']),
							'MobilePhone' => $row['MobilePhone'],
							'Language' => $row['Language'],
							'RoleId' => intval($row['RoleId']));
		}

		return $data;
	}
	
	public function CreateUser($email, $password, $registrationCode, $country = null, $language = "en", $roleId = 2) {
		global $mysqli, $authIssueText;
		
		$sql = "SELECT UserStateId FROM User WHERE Email = '$email'";
		$result = $mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);
		$row = mysqli_fetch_array($result);
		
		$result = false;
		
		if($recordsCount > 0) {
			if($row['UserStateId'] == 0) {
				// User existing but not yet activated: replace!
				$sql = "UPDATE User SET PasswordHash = '$password', Country = '$country', Language = '$language', RegistrationToken = '$registrationCode', RegistrationDateTime = '".time()."', RoleId = $roleId ";
				$sql .= " WHERE Email = '$email' ";
				
				$result = $mysqli->query($sql) or die ($authIssueText);
			}
		} else {
			// User not existing: create!
			$sql = "INSERT INTO User (Email, PasswordHash, Country, Language, RegistrationToken, RegistrationDateTime, RoleId) ";
			$sql .= " VALUES ('$email', '$password', '$country', '$language', '$registrationCode', '".time()."', $roleId) ";
			
			$result = $mysqli->query($sql) or die ($authIssueText);
		}
		
		return $result;
	}
	
	public function ConfirmUserRegistration($Email, $RegistrationToken) {
		global $mysqli;
		
		$expirationTime = (24 * 60 * 60);
		
		$sql  = " UPDATE User SET ";
		$sql .= " RegistrationToken = '', ";
		$sql .= " UserStateId = 1 "; // Activate it
		$sql .= " WHERE Email = '$Email' AND RegistrationToken = '$RegistrationToken'";
		$sql .= " AND RegistrationDateTime > " . (time() - $expirationTime);
		
		$result = $mysqli->query($sql) or die ($authIssueText);
		
		if($mysqli->affected_rows > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	// Create a temporary passcode to allow the user to reset his own password
	public function CreateResetPasswordCode($userId, $token) {
		global $mysqli;
		
		$sql  = " UPDATE User SET ";
		$sql .= " PasswordResetToken = '".$token."', ";
		$sql .= " PasswordResetDateTime = '".time()."' ";
		$sql .= " WHERE UserId = $userId";
		
		$result = $mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	function DbUpdateUser($User) {
		global $mysqli, $authIssueText;
		
		$sql  = "UPDATE User SET";
		$sql .= " Email = \"".$mysqli->real_escape_string($User['Email'])."\"";
		$sql .= ", Username = \"".$mysqli->real_escape_string($User['Username'])."\"";
		$sql .= ", FirstName = \"".$mysqli->real_escape_string($User['FirstName'])."\"";
		$sql .= ", LastName = \"".$mysqli->real_escape_string($User['LastName'])."\"";
		$sql .= ", Country = \"".$mysqli->real_escape_string($User['Country'])."\"";
		$sql .= ", LastLoginDateTime = \"".$User['LastLoginDateTime']."\"";
		$sql .= ", UserStateId   = ".$User['UserStateId'];
		$sql .= ", LoginAttempts = ".$User['LoginAttempts'];
		$sql .= ", MobilePhone   = \"".$mysqli->real_escape_string($User['MobilePhone'])."\"";
		$sql .= ", Language = \"".$mysqli->real_escape_string($User['Language'])."\"";
		$sql .= " WHERE UserId = ".$User['UserId'];
		
		$result = $mysqli->query($sql) or die ($authIssueText);
		
		return true;
	}
}

?>