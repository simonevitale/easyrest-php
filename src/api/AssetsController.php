<?
////////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2018 Simone Vitale
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
use \Jacwright\RestServer\RestFormat;

require_once("./AssetsDatabaseHandler.php");
require_once("./FilesDatabaseHandler.php");

class AssetsController extends AssetsDatabaseHandler
{
    /**
     * Get Assets Collection
     * 
     * @url GET /assets/types/
     */
    public function getAssetTypes() {
		return parent::AssetTypes();
	}
	
    /**
     * Get Assets Collection
     * 
     * @url GET /assets/
     */
    public function getAssets() {
		$authIdUser = parent::CheckAuthentication(false);
		
		$userId = null;
		if ($authIdUser > 0) {
			$authenticated = true;
			$userId = $authIdUser;
		}
		
		$filters = array();
		
		if(isset($_GET['typeId']) && $_GET['typeId'] != "" && $_GET['typeId'] > 0) $filters[AssetTypeId] = $_GET['typeId'];
		
		if(isset($_GET['from']))  $from = $_GET['from']; else $from = -1;
		if(isset($_GET['count'])) $count = $_GET['count']; else $count = -1;
		
		return parent::Assets($userId, $filters, $from, $count);
	}
	
    /**
     * Get Asset
     * 
     * @url GET /asset/$assetId
     */
    public function getAsset($assetId) {
		$asset = parent::AssetById($assetId);
		
		if($asset["IsPublic"] != 1) {
			$userId = parent::CheckAuthentication(true);		
			parent::CheckIfOwned($userId, $assetId, true);
		}
		
		if($asset == null)
			throw new RestException(404, "Asset not found");
		
		return $asset;
	}
	
    /**
     * Get Asset
     * 
     * @url GET /asset/file/$fileId
     */
    public function getAssetFile($fileId) {
		//$userId = parent::CheckAuthentication(true);
		//parent::CheckIfOwned($userId, true);
		$filesDatabaseHandler = new FilesDatabaseHandler();
		
		return $filesDatabaseHandler->FileById($fileId);
	}
	
    /**
     * Get Asset
     * 
     * @url GET /asset/file/$fileId/deliver
     */
    public function deliverAssetFile($fileId) {
		$filesDatabaseHandler = new FilesDatabaseHandler();
		$file = $filesDatabaseHandler->FileById($fileId);

		$userAssetsFolder = Settings::getInstance()->p['userAssetsFolder'];

		$filePath = (strlen($file["OriginalFileName"]) > 0) ? "../../".parent::GetFileUrl($file["OwnerUserId"], $file["OriginalFileName"], $userAssetsFolder, false) : "";

		if(file_exists($filePath)) {
			parent::DeliverFile($filePath, $file[FileName]);
		} else {
			throw new RestException(404, "Asset file not found.");
		}
	}
	
	/**
     * Update or Create Asset
     * 
     * @url POST /asset/update/
     */
    public function updateAsset() {
		$userId = parent::CheckAuthentication(true, true);
		
		if(isset($_POST['AssetId']) && is_numeric($_POST['AssetId'])) {
			$assetId = $_POST['AssetId'];
		} else {
			//parent::CreateAsset($_POST['Name'], $userId);
			$assetId = parent::GetLastId("Asset", $userId);
		}
		
		//parent::CheckIfOwned($userId, "Asset", $assetId, true);
		
		$asset = parent::AssetById($assetId);
		
		$isPublic = 0;
		if(strcmp($_POST['IsPublic'], "true") == 0 || $_POST['IsPublic'] == 1 || $_POST['IsPublic'] == true) $isPublic = 1;
		
		if(isset($_POST['Name'])) $asset["Name"] = $_POST['Name'];
		if(isset($_POST['Version'])) $asset["Version"] = $_POST['Version'];
		if(isset($_POST['IsPublic'])) $asset["IsPublic"] = $isPublic;
		if(isset($_POST['TypeId']) && is_numeric($_POST['TypeId']))
			$asset["TypeId"] = $_POST['TypeId'];
		if($asset["TypeId"] === NULL) $asset["TypeId"] = 0;
		
		parent::DbUpdateAsset($asset);
		
		return $assetId;
	}
	
    /**
     * Delete Asset
     * 
     * @url POST /asset/delete/
     */
    public function deleteAsset() {
		/*$userId = parent::CheckAuthentication();
		
		if(parent::CheckIfOwned($userId, "Event", $_POST['EventId']) == true) {
			$Event = parent::EventById($_POST['EventId']);
			
			$this->UnlinkRemovedEventImages($userId, $Event['Image']);
			parent::DeleteRecord('Event', $userId, $_POST['EventId']);
			
			return "OK";
		}*/
	}
}

?>