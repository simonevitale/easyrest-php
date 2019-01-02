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
 * Manages the database operations about files
 *
 * @author Simone Vitale
 */
 
use \Jacwright\RestServer\RestException;

class FilesDatabaseHandler extends DatabaseHandler
{
	// TODO: rename to AssetFilesByAssetId
	public function AssetFiles($assetId, $filters = null, $from = -1, $count = -1) {
		global $authIssueText;
		
		$files = array();
		
		$userAssetsFolder = Settings::getInstance()->p['userAssetsFolder'];
		
		$fields = "AssetFile.AssetId, File.FileId, File.OwnerUserId, File.IsPublic, File.OriginalFileName, File.FileName, FileType.FileTypeId, FileType.Name AS 'FileType', FileRole.Name as 'FileRole' ";
		
		$sql .= "SELECT $fields ";
		$sql .= "FROM AssetFile ";
		$sql .= "LEFT JOIN FileRole ON AssetFile.FileRoleId = FileRole.FileRoleId ";
		$sql .= "LEFT JOIN File ON AssetFile.FileId = File.FileId ";
		$sql .= "LEFT JOIN FileType ON File.FileTypeId = FileType.FileTypeId ";
		/*if($userId != null) {
			... Implement Security ...
		}*/
		
		$sql .= " WHERE AssetId = $assetId ";
		
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
			while($row = mysqli_fetch_array($result)) {
				
				$fileUrl = (strlen($row[OriginalFileName]) > 0) ? parent::GetFileUrl($row[OwnerUserId], $row[OriginalFileName], $userAssetsFolder, true) : "";
			
				$files[] = array (
					'AssetId' => $row['AssetId'],
					'FileId' => $row['FileId'],
					'OwnerUserId' => $row['OwnerUserId'],
					'IsPublic' => $row['IsPublic'],
					'FileName' => $row['FileName'],
					'FileUrl' => $fileUrl, // Possibly not used anymore
					'OriginalFileName' => $row['OriginalFileName'],
					'FileType' => $row['FileType'],
					'FileRole' => $row['FileRole']
				);
			}
		}
		
		return $files;
	}
	
	// TODO: test this method (never tested)
	public function FileTypes() {
		$fileTypes = array();
		
		$sql = "SELECT FileTypeId, Name FROM FileType ";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$fileTypes[] = array (
					'FileTypeId' => $row['FileTypeId'],
					'Name' => $row['Name']
				);
			}
		}
		
		return $fileTypes;
	}
	
	function FileById($fileId) {
		$userAssetsFolder = Settings::getInstance()->p['userAssetsFolder'];

		$fields = "File.FileId, File.OwnerUserId, File.IsPublic, File.OriginalFileName, File.FileName, FileType.FileTypeId, FileType.Name AS 'FileType' ";
		
		$sql  = "SELECT $fields ";
		$sql .= "FROM File ";
		$sql .= "LEFT JOIN FileType ON File.FileTypeId = FileType.FileTypeId ";
		$sql .= "WHERE File.FileId = $fileId ";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = null;

		if($recordsCount >= 1 && $result != null) {
			$data = [];
			
			$row = mysqli_fetch_array($result);
			
			$fileUrl = (strlen($row[FileName]) > 0) ? parent::GetFileUrl($row[OwnerUserId], $row[FileName], $userAssetsFolder, true) : "";
			
			$data = array(	'FileId' => $row['FileId'],
							'OwnerUserId' => $row['OwnerUserId'],
							'IsPublic' => $row['IsPublic'],
							'FileName' => $row['FileName'],
							'OriginalFileName' => $row['OriginalFileName'],
							'FileUrl' => $fileUrl,
							'FileType' => $row['FileType']);
		}

		return $data;
	}
	
	/*
	function CheckIfOwned($userId, $assetId, $throwException = true) {
		global $mysqli;
		
		$sql = "SELECT * FROM Asset, UserAsset ";
		$sql .= "WHERE UserAsset.UserId = $userId ";
		$sql .= "AND Asset.AssetId = $assetId ";
		$sql .= "AND Asset.AssetId = UserAsset.AssetId ";
		$sql .= "OR Asset.OwnerUserId = $userId ";
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
	}*/
}

?>