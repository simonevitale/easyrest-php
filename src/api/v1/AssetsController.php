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
use \Jacwright\RestServer\RestFormat;

require_once("./AssetsDatabaseHandler.php");

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
		
		if(isset($_GET['typeId']) && $_GET['typeId'] != "" && $_GET['typeId'] > 0) $filters[AssetTypeId]  = $_GET['typeId'];
		
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
		$userId = parent::CheckAuthentication(true);
		parent::CheckIfOwned($userId, $assetId, true);
		
		$asset = parent::AssetById($assetId);
		
		if($asset == null)
			throw new RestException(404, "Asset not found");
		
		return $asset;
	}
	
    /**
     * Get Asset
     * 
     * @url GET /asset/$assetId/file
     */
    public function getAssetFile($assetId) {
		/*global $server ;
		//$userId = parent::CheckAuthentication(true);
		//parent::CheckIfOwned($userId, true);
		
		$asset = parent::AssetById($assetId);
		$filePath = "../../".$asset['FilePath'];
		
		$server->format = RestFormat::FILE;
		$server->fileName = $asset['FileName'];
		$server->filePath = $filePath;*/
		
		/*header('Content-Length: 34325' . filesize($filePath));
		
		$server->format = RestFormat::FILE;
		$server->filename = $asset["FileName"];
		
		$userAssetsFolder = Settings::getInstance()->p['userAssetsFolder'];
		*/
		/*if(file_exists($filePath)) {
			$file = fopen($filePath, 'rb');
			//$server->contentlength = 1560;//filesize($filePath);
			
			$data = fread($file, filesize($filePath));
			fclose($file);
			return $data;//readfile($filePath);
		} else {
			throw new RestException(404, "Asset file not found.");
		}*/
	}
}

?>