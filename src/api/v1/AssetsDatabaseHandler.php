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
 * Manages the database operations about assets
 *
 * @author Simone Vitale
 */
 
use \Jacwright\RestServer\RestException;

require_once "./FilesDatabaseHandler.php";
require_once "./PackagesDatabaseHandler.php";

class AssetsDatabaseHandler extends DatabaseHandler
{
	public function Assets($userId, $filters = null, $from = -1, $count = -1) {
		global $authIssueText;
		
		$assets = array();
		
		$userAssetsFolder = Settings::getInstance()->p['userAssetsFolder'];
		
		$fields = "Asset.UserId, Asset.AssetId, Asset.Name, Asset.CreationDateTime, AssetType.Type AS 'Type', Asset.Version, Asset.AssetTypeId ";
		
		$sql = "SELECT * FROM (";
		$sql .= "SELECT $fields ";
		$sql .= "FROM Asset, AssetType ";
		$sql .= "WHERE Asset.AssetTypeId = AssetType.AssetTypeId ";
		$sql .= "AND Asset.IsPublic = 1 ";
		if($userId != null) {
			$sql .= "UNION ";
			$sql .= "SELECT $fields ";
			$sql .= "FROM Asset, AssetType ";
			$sql .= "WHERE Asset.AssetTypeId = AssetType.AssetTypeId ";
			$sql .= "AND Asset.UserId = $userId ";
			$sql .= "UNION ";
			$sql .= "SELECT $fields ";
			$sql .= "FROM Asset, AssetType, UserAsset "; //, User
			$sql .= "WHERE Asset.AssetId = UserAsset.AssetId ";
			//$sql .= "AND User.UserId = UserAsset.UserId ";
			$sql .= "AND Asset.AssetTypeId = AssetType.AssetTypeId ";
			$sql .= "AND UserAsset.UserId = $userId ";
			//$sql .= "AND Asset.IsPublic = 0 OR OnlyAllowCustomThemes =  ";
		}
		$sql .= ") t ";
		
		$sql .= " WHERE t.AssetId > 0 ";
		
		// Apply Filters
		if($filters != null) {
			foreach ($filters as $key => $value) {
				// -orblank postfix for language filter shows also records with no set language
				$orBlankIndex = strpos($value, '-orblank');
				if($orBlankIndex !== false)
					$sql .= " AND (t.$key = '".substr($value, 2)."' OR t.$key = '')";
				else
					$sql .= " AND t.$key = '$value' ";
			}
		}
		
		$sql .= "ORDER BY AssetId ASC \n";
		
		if($from != -1 && $count != -1)
			$sql .= "LIMIT $from, $count \n";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			$filesHandler = new FilesDatabaseHandler;
			$packagesHandler = new PackagesDatabaseHandler;
			
			while($row = mysqli_fetch_array($result)) {
				$assets[] = array (
					'AssetId' => $row['AssetId'],
					'Name' => $row['Name'],
					'Type' => $row['Type'],
					'CreationDateTime' => $row['CreationDateTime'],
					'Version' => $row['Version'],
					'UserId' => $row['UserId'],
					'Files' => $filesHandler->AssetFiles($row['AssetId']),
					'Packages' => $packagesHandler->AssetPackagesByAssetId($row['AssetId'])
				);
			}
		}
		
		return $assets;
	}
	
	public function AssetTypes() {
		$assetTypes = array();
		
		$sql = "SELECT AssetTypeId, Type FROM AssetType ";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$assetTypes[] = array (
					'AssetTypeId' => $row['AssetTypeId'],
					'Type' => $row['Type']
				);
			}
		}
		
		return $assetTypes;
	}
	
	function AssetById($assetId) {
		$userAssetsFolder = Settings::getInstance()->p['userAssetsFolder'];

		$assetFieldsSql = "Asset.AssetId, Asset.Name, Asset.CreationDateTime, Asset.Version, Asset.UserId, AssetType.Type AS Type, Asset.AssetTypeId AS TypeId, Asset.IsPublic ";
		
		$sql  = "SELECT $assetFieldsSql ";
		$sql .= "FROM Asset, AssetType ";
		$sql .= "WHERE Asset.AssetTypeId = AssetType.AssetTypeId ";
		$sql .= "AND Asset.AssetId = $assetId ";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = null;

		if($recordsCount >= 1 && $result != null) {
			$data = [];
			
			$row = mysqli_fetch_array($result);
			
			$filesHandler = new FilesDatabaseHandler;
			
			$data = array(	'AssetId' => intval($row['AssetId']),
							'Name' => $row['Name'],
							'Type' => $row['Type'],
							'TypeId' => intval($row['TypeId']),
							'CreationDateTime' => $row['CreationDateTime'],
							'IsPublic' => intval($row['IsPublic']),
							//'Files' => $this->AssetFilesByAssetId($assetId),
							'Files' => $filesHandler->AssetFiles($assetId),
							'Version' => intval($row['Version']),
							'UserId' => intval($row['UserId']));
		}

		return $data;
	}
	
	function CreateAsset($name, $userId) {
		global $authIssueText;

		$sql = "INSERT INTO Asset (Name, CreationDateTime, UserId) ";
		$sql .= "VALUES('".$this->mysqli->real_escape_string($name)."', '".time()."', $userId)";
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	function DbUpdateAsset($asset) {
		global $authIssueText;
		
		$sql  = "UPDATE Asset SET";
		$sql .= "  Name = \"".$this->mysqli->real_escape_string($asset['Name'])."\"";
		$sql .= ", Version = ".$asset['Version'];
		$sql .= ", IsPublic = ".$asset['IsPublic'];
		$sql .= ", AssetTypeId = ".$asset['TypeId'];
		$sql .= " WHERE AssetId = ".$asset['AssetId'];
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
	}
	
	//TODEL: duplicated
	/*function AssetFilesByAssetId($assetId) {
		$sql  = "SELECT File.FileId, FileType.Name AS FileType, FileRole.Name AS FileRole, File.OwnerUserId, File.OriginalFileName, File.FileName ";
		$sql .= "FROM File, FileType, AssetFile, FileRole ";
		$sql .= "WHERE File.FileTypeId = FileType.FileTypeId ";
		$sql .= "AND File.FileId = AssetFile.FileId ";
		$sql .= "AND FileRole.FileRoleId = AssetFile.FileRoleId ";
		$sql .= "AND AssetFile.AssetId = $assetId";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = null;

		if($recordsCount >= 1 && $result != null) {
			$userAssetsFolder = Settings::getInstance()->p['userAssetsFolder'];
			
			$data = [];
			
			while($row = mysqli_fetch_array($result)) {
			
			$fileUrl = (strlen($row[OriginalFileName]) > 0) ? parent::GetFileUrl($row[OwnerUserId], $row[OriginalFileName], $userAssetsFolder, true) : "";
			
			$data[] = array('FileId' => $row['FileId'],
							'FileType' => $row['FileType'],
							'FileRole' => $row['FileRole'],
							'IsPublic' => $row['IsPublic'],
							//'CreationDateTime' => $row['CreationDateTime'],
							'OriginalFileName' => $row['OriginalFileName'],
							'FileName' => $row['FileName'],
							'FileUrl' => $fileUrl, // Possibly not used anymore
							'OwnerUserId' => $row['OwnerUserId']);
			}
		}
		
		return $data;
	}*/
	
	function CheckIfOwned($userId, $assetId, $throwException = true) {
		global $mysqli;
		
		$sql = "SELECT * FROM Asset, UserAsset ";
		$sql .= "WHERE UserAsset.UserId = $userId ";
		$sql .= "AND Asset.AssetId = $assetId ";
		$sql .= "AND Asset.AssetId = UserAsset.AssetId ";
		$sql .= "OR Asset.UserId = $userId ";
		$sql .= "OR Asset.IsPublic = 1 ";
		
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
}

?>