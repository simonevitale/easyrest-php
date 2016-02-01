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

use \Jacwright\RestServer\RestException;

require_once("./Database.php");

$mysqli = Database::getInstance()->getConnection();
	
/**
 * Manages the basic database operations
 *
 * @author Simone Vitale
 */
class DatabaseHandler
{	
	protected $mysqli;
	
	public function __construct() {
		$this->mysqli = Database::getInstance()->getConnection();
	}
	
	/*
	 * @returns:
	 *  -1: wrong credentials
	 *  -2: account not confirmed
	 */
	public function AuthenticateByEmail($email, $password) {
		global $authIssueText;
		global $mysqli;
	
		$sql = "SELECT UserId, UserStateId FROM User WHERE Email = '$email' AND PasswordHash = '$password' ";
		
		$result = $mysqli->query($sql);
		
		$row = mysqli_fetch_array($result);
		
		if($row != null) {
			if($row['UserStateId'] <= 0) return -2; // account not confirmed
			if($row['UserStateId'] == 2) return -3; // account suspended
			if($row['UserStateId'] == 1) return $row['UserId']; // valid auth
		}
		
		return -1;
	}
	
	function CheckAuthentication($throwException = true, $checkIfAdmin = false) {
		// TODO: Implement "$checkIfAdmin"
		
		$userId = -1;
		
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
		{
			$userId = $this->AuthenticateByEmail($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		}

		if($userId <= 0 && $throwException) {
			throw new RestException(401, "Unauthorized. Authentication credentials are missing or incorrect for user ".$_SERVER['PHP_AUTH_USER']);
		}
		
		return $userId;
	}
	
	function CheckIfOwned($userId, $entity, $entityId, $throwException = true) {
		global $mysqli;
		
		$sql = "SELECT * FROM $entity WHERE UserId = $userId ";
		$sql .= "AND ".$entity."Id = $entityId";
		
		$result = $mysqli->query($sql);
		
		$recordsCount = mysqli_num_rows($result);
		
		if($recordsCount <= 0) {
			if($throwException) {
				throw new RestException(401, "Unauthorized. The user doesn't own the content");
			} else {
				return false;
			}
		}
		
		return true; // Authorized
	}

	public function GetImageUrl($userId, $image, $folder, $thumbnail = false, $absolutePath = true, $pathOnly = false) {
		$websiteUrl = Settings::getInstance()->p['websiteUrl'];
		$portalFolder = Settings::getInstance()->p['portalFolder'];
		$userUploadFolder = Settings::getInstance()->p['userUploadFolder'];
		$thumbnailPrefix = Settings::getInstance()->p['thumbnailPrefix'];
		
		$url = "";
		
		if($absolutePath == true) {
			$url .= "$websiteUrl/$portalFolder/";
		}
		$url .= "$userUploadFolder";
		if (!file_exists($url)) {
			mkdir($url, 0777, true);
		}
		$url .= "/$userId";
		if (!file_exists($url)) {
			mkdir($url, 0777, true);
		}
		$url .= "/$folder";
		if (!file_exists($url)) {
			mkdir($url, 0777, true);
		}
		if($pathOnly == false) {
			$url .= ("/".(($thumbnail == true) ? $thumbnailPrefix : "").$image);
		}
		
		return $url;
	}
	
	public function substrwords($text, $maxchar, $end = '...') {
		if (strlen($text) > $maxchar && strlen($text) > 0) {
			$words = preg_split('/\s/', $text);      
			$output = '';
			$i      = 0;
			while (1) {
				$length = strlen($output)+strlen($words[$i]);
				if ($length > $maxchar) {
					break;
				} 
				else {
					$output .= " " . $words[$i];
					++$i;
				}
			}
			$output .= $end;
		} else {
			$output = $text;
		}
		
		return $output;
	}
	
	public function Categories($entity) {
		global $mysqli;
		
		$jsonData = array();
	
		$sql = "SELECT DISTINCT Name FROM Category WHERE Entity = '$entity'";

		$result = $mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$jsonData[] = $row['Name'];
			}
		}
		
		return $jsonData;
	}
	
	public function Countries() {
		global $mysqli;
		
		$jsonData = array();
	
		$sql = "SELECT Name FROM Country";

		$result = $mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$jsonData[] = $row['Name'];
			}
		}
		
		return $jsonData;
	}
	
	public function Languages() {
		global $mysqli;
		
		$jsonData = array();
	
		$sql = "SELECT DISTINCT Code FROM Language";

		$result = $mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$jsonData[] = $row['Code'];
			}
		}
		
		return $jsonData;
	}
	
	public function Authenticate($userId, $password) {
		global $authIssueText;
		global $mysqli;
	
		$sql = "SELECT * FROM User WHERE Id = $userId AND Password = '$password' ";
		
		$result = $mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);
		
		return ($recordsCount > 0);
	}
	
	public function AppendLog($Action, $OrganizationName, $agent = "", $userEmail = "") {
		global $authIssueText;
		global $mysqli;
		
		$ip = "";
		
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		$info = ip_info($ip);
		$location = $info['country'] . ", " . $info['continent_code'];
		
		// gmdate("Y m d H:i:s")
		$sql = "INSERT INTO Log (Action, OrganizationName, Agent, UserEmail, DateTime, Ip, Location) VALUES ('$Action', '$OrganizationName', '$agent', '$userEmail', '".time()."', '$ip', '$location')";
	
		$result = $mysqli->query($sql) or die ($authIssueText);
	}
	
	public function GetRecordsCount($table, $userId, $conditions) {
		global $authIssueText, $mysqli;
	
		$sql = "SELECT COUNT(*) AS 'Counter' FROM $table WHERE UserId = $userId ";
		$sql .= (strlen($conditions) > 0) ? " AND $conditions " : "";
		
		$result = $mysqli->query($sql) or die ($authIssueText);
		
		if($result != null) {
			$row = mysqli_fetch_array($result);
			return $row['Counter'];
		}
		
		return 0;
	}
	
	public function DeActivateRecord($table, $recordId) {
		global $authIssueText, $mysqli;
		
		$sql  = "UPDATE $table SET Active = 0 ";
		$sql .= "WHERE $table"."Id = ".$recordId;
		
		$result = $mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	public function DeleteRecord($table, $userId, $recordId) {
		global $authIssueText, $mysqli;
	
		$sql = "DELETE FROM $table WHERE UserId = $userId AND $table"."Id = $recordId";
		$result = $mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	// Get last / maximum Id for the specified table
	public function GetLastId($table, $userId) {
		global $authIssueText, $mysqli;
		
		$sql = "SELECT MAX($table"."Id) AS 'MaxId' FROM $table WHERE UserId = $userId";
		
		$result = $mysqli->query($sql) or die ($authIssueText);
		
		if($result != null) {
			$row = mysqli_fetch_array($result);
			return $row['MaxId'];
		}
		
		return 0;
	}
}
