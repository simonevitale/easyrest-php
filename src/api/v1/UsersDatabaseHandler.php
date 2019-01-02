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
	
/**
 * Manages the database operations about users
 *
 * @author Simone Vitale
 */

class Role {
	public $RoleId	  = 0;
	public $Name  	  = "";
	public $Modules	  = null;
	
	public function __construct($id, $name, $modules) {
        $this->RoleId  = intval($id);
        $this->Name    = $name;
		$this->Modules = explode(",", $modules);
    }
}

class License {
	public $LicenseId	  = 0;
	public $Name  	   = "";
	public $MaxPlayers = null;
	
	public function __construct($id, $name, $maxPlayers) {
        $this->LicenseId  = intval($id);
        $this->Name    = $name;
		$this->MaxPlayers = intval($maxPlayers);
    }
}

class UsersDatabaseHandler extends DatabaseHandler
{
	public function Users($from, $count) {
		global $authIssueText;
		
		$sql = "SELECT UserId, Email, Username, LastLoginDateTime, Organization, Role.Name AS Role, Country.Name AS Country FROM User ";
		$sql .= "LEFT JOIN Country ON User.CountryId = Country.CountryId ";
		$sql .= "LEFT JOIN Role ON User.RoleId = Role.RoleId ";
		$sql .= "ORDER BY UserId LIMIT $from, $count";

		$result = $this->mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);

		$users = array();
		
		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$users[] = array (
					'UserId' => intval($row['UserId']),
					'Email' => $row['Email'],
					'Username' => $row['Username'],
					'LastLogin' => $row['LastLoginDateTime'],
					'Country' => $row['Country'],
					'Role' => $row['Role'],
					'Organization' => $row['Organization']
				);
			}
		}
		
		return $users;
	}
	
	public function UserById($userId) {
		global $authIssueText;
		
		$userUserFolder = Settings::getInstance()->p['userUserFolder'];

		$sql = "SELECT UserId, Email, Username, FirstName, LastName, User.CountryId, TimeZone, Image, RegistrationDateTime, RegistrationCode, LastLoginDateTime, UserStateId, LoginAttempts, MobilePhone, Language, PortalLanguage, Properties, ";
		$sql .= "Role.RoleId, Role.Name AS RoleName, Role.Modules AS RoleModules, ";
		$sql .= "License.LicenseId, License.Name AS LicenseName, License.MaxPlayers AS LicenseMaxPlayers ";
		$sql .= "FROM User ";
		$sql .= "LEFT JOIN Country ON User.CountryId = Country.CountryId ";
		$sql .= "LEFT JOIN Role ON User.RoleId = Role.RoleId ";
		$sql .= "LEFT JOIN License ON User.LicenseId = License.LicenseId ";
		if(is_numeric($userId))
			$sql .= "WHERE UserId = $userId ";
		else
			$sql .= "WHERE Username = '$userId' ";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = null;

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);
			
			$imageUrl = parent::GetImageUrl($row['UserId'], $row['Image'], $userUserFolder);
			$imageThumbnailUrl = parent::GetImageUrl($row['UserId'], $row['Image'], $userUserFolder, true);

			$data = array(	'UserId' => intval($row['UserId']),
							'Email' => $row['Email'],
							'Username' => $row['Username'],
							'FirstName' => $row['FirstName'],
							'LastName' => $row['LastName'],
							'Country' => $row['CountryId'], // TODO: Country should be CountryId
							'TimeZone' => $row['TimeZone'],
							'Image' => $row['Image'],
							'ImageUrl' => $imageUrl,
							'ThumbnailUrl' => $imageThumbnailUrl,
							'RegistrationDateTime' => $row['RegistrationDateTime'],
							'RegistrationCode' => $row['RegistrationCode'],
							'LastLoginDateTime' => $row['LastLoginDateTime'],
							'UserStateId' => intval($row['UserStateId']),
							'LoginAttempts' => intval($row['LoginAttempts']),
							'MobilePhone' => $row['MobilePhone'],
							'Language' => $row['Language'],
							'PortalLanguage' => $row['PortalLanguage'],
							'Role' => new Role($row['RoleId'], $row['RoleName'], $row['RoleModules']),
							'License' => new License($row['LicenseId'], $row['LicenseName'], $row['LicenseMaxPlayers']),
							'Properties' => $row['Properties']);
		}

		return $data;
	}
	
	public function CreateUser($email, $password, $registrationToken, $countryId = null, $timeZone = 0, $language = "en", $roleId = 2, $code = "", $organization = "", $userStateId = 0, $licenseId = 1) {
		global $authIssueText;
		
		$sql = "SELECT UserId, UserStateId FROM User WHERE Email = '$email'";
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);
		$row = mysqli_fetch_array($result);
		
		$result = false;
		$dt = gmdate("Y-m-d H:i:s");
		
		$newUserId = -1;
		
		$portalLanguage = $language;
		
		if($recordsCount > 0) {
			$newUserId = $row['UserId'];
			
			if($row['UserStateId'] == 0) {
				// User existing but not yet activated: replace!
				$sql = "UPDATE User SET PasswordHash = '$password', CountryId = $countryId, TimeZone = $timeZone, Language = '$language', PortalLanguage = '$portalLanguage', RegistrationToken = '$registrationToken', RegistrationDateTime = '$dt', RoleId = $roleId, RegistrationCode = '$code', Organization = '$organization' ";
				$sql .= " WHERE Email = '$email' ";
				
				$result = $this->mysqli->query($sql) or die ("Couldn't update user " . $authIssueText);
			}
		} else {
			// User not existing: create!
			$sql = "INSERT INTO User (Email, PasswordHash, CountryId, TimeZone, Language, PortalLanguage, RegistrationToken, RegistrationDateTime, RoleId, RegistrationCode, LastLoginDateTime, Organization, UserStateId, LicenseId) ";
			$sql .= " VALUES ('$email', '$password', $countryId, $timeZone, '$language', '$portalLanguage', '$registrationToken', '$dt', $roleId, '$code', '', '$organization', $userStateId, $licenseId) ";
			
			$result = $this->mysqli->query($sql) or die ("$sql Couldn't insert new user " . $authIssueText);
			
			// Retrieve new UserId
			$result = $this->mysqli->query("SELECT MAX(UserId) AS 'MaxId' FROM User");
			
			if($result != null) {
				$row = mysqli_fetch_array($result);
				$newUserId = $row['MaxId'];
			}
		}
		
		return $newUserId;
	}
	
	public function ConfirmUserRegistration($Email, $RegistrationToken) {
		//$d = new DateTime("2012-07-08 11:14:15.889342"); 
		//$expirationTime = (24 * 60 * 60);
		
		$sql  = " UPDATE User SET ";
		$sql .= " RegistrationToken = '', ";
		$sql .= " UserStateId = 1 "; // Activate it
		$sql .= " WHERE Email = '$Email' AND RegistrationToken = '$RegistrationToken'";
		//$sql .= " AND RegistrationDateTime > " . (time() - $expirationTime);
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		if($this->mysqli->affected_rows > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	// Create a temporary passcode to allow the user to reset his own password
	public function CreateResetPasswordCode($userId, $token) {
		$sql  = " UPDATE User SET ";
		$sql .= " PasswordResetToken = '".$token."', ";
		$sql .= " PasswordResetDateTime = '".time()."' ";
		$sql .= " WHERE UserId = $userId";
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	function DbUpdateUser($user) {
		global $authIssueText;
		
		$sql  = "UPDATE User SET";
		$sql .= " Email = \"".$this->mysqli->real_escape_string($user['Email'])."\"";
		$sql .= ", Username = \"".$this->mysqli->real_escape_string($user['Username'])."\"";
		$sql .= ", FirstName = \"".$this->mysqli->real_escape_string($user['FirstName'])."\"";
		$sql .= ", LastName = \"".$this->mysqli->real_escape_string($user['LastName'])."\"";
		$sql .= ", Image = \"".$user['Image']."\"";
		$sql .= ", CountryId = ".$user['Country'];
		$sql .= ", TimeZone = ".$user['TimeZone'];
		$sql .= ", LastLoginDateTime = \"".$user['LastLoginDateTime']."\"";
		$sql .= ", UserStateId   = ".$user['UserStateId'];
		$sql .= ", LoginAttempts = ".$user['LoginAttempts'];
		$sql .= ", MobilePhone   = \"".$this->mysqli->real_escape_string($user['MobilePhone'])."\"";
		$sql .= ", Language = \"".$this->mysqli->real_escape_string($user['Language'])."\"";
		$sql .= ", PortalLanguage = \"".$this->mysqli->real_escape_string($user['PortalLanguage'])."\"";
		$sql .= " WHERE UserId = ".$user['UserId'];
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return true;
	}
	
	function AddAssetToUser($assetId, $userId) {
		global $authIssueText;
		
		$sql = "INSERT INTO userasset(UserId, AssetId) VALUES ($userId, $assetId);";
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	function DeleteUserDb($userId) {
		global $authIssueText;
		
		$result = $this->mysqli->query("DELETE FROM Article WHERE UserId = $userId;") or die ($authIssueText);
		$result = $this->mysqli->query("DELETE FROM Event WHERE UserId = $userId;") or die ($authIssueText);
		$result = $this->mysqli->query("DELETE FROM Location WHERE UserId = $userId;") or die ($authIssueText);
		$result = $this->mysqli->query("DELETE FROM Author WHERE UserId = $userId;") or die ($authIssueText);
		$result = $this->mysqli->query("DELETE FROM UserAsset WHERE UserId = $userId;") or die ($authIssueText);
		$result = $this->mysqli->query("DELETE FROM User WHERE UserId = $userId;") or die ($authIssueText);
		
		return $result;
	}
}

?>