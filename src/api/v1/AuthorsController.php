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

require_once("./AuthorsDatabaseHandler.php");

class AuthorsController extends AuthorsDatabaseHandler
{	
    /**
     * Get Authors
     * 
     * @url GET /authors/$userId
     */
    public function getAuthors($userId = null) {
		if(isset($_GET['format'])) $format = $_GET['format']; else $format = null;
		
		return parent::Authors($userId, $format);
	}
	
    /**
     * Get Author
     * 
     * @url GET /author/$authorId
     */
    public function getAuthor($authorId) {
		return parent::AuthorById($authorId);
	}
	
    /**
     * Get Author
     * 
     * @url GET /author/exists/$idName
     */
    public function checkIfIdNameExists($idName) {
		return parent::CheckIfIdNameExists(strtolower($idName));
	}
	
    /**
     * Update Author
     * 
     * @url POST /author/update/
	 * @params AuthorId, Name, UniqueName, Image
     */
    public function updateAuthor() {
		$userId = parent::CheckAuthentication(true);
		
		$userAuthorsFolder = Settings::getInstance()->p['userAuthorsFolder'];
		
		if(isset($_POST['AuthorId']) && is_numeric($_POST['AuthorId'])) {
			$authorId = $_POST['AuthorId'];
		} else {
			parent::CreateAuthor($_POST['Name'], $userId);
			$authorId = parent::GetLastId("Author", $userId);
		}
	
		$author = parent::AuthorById($authorId);
		
		if(strlen($_POST['UniqueName']) > 0 && parent::CheckIfIdNameExists($_POST['UniqueName'])) {
			throw new RestException(405, "Unauthorized. The selected id name already exists.");
		}
		
		$isImageUploading = (file_exists($_FILES['NewImage']['tmp_name']) && isset($_FILES['NewImage']) && is_uploaded_file($_FILES['NewImage']['tmp_name'])) ? 1 : 0;
		$image = $_POST['Image'];
		
		$destinationDirectory = "../../".parent::GetImageUrl($userId, "", $userAuthorsFolder, false, false, true)."/"; 
		
		if(strlen($_POST['Image']) == 0 || $isImageUploading) {
			$this->unlinkRemovedAuthorImages($userId, $author['Image']);
		}
		
		// Upload new image
		if($isImageUploading == 1) {
			$image = uploadImage($_FILES['NewImage'], $destinationDirectory, 200);
		}
		
		$author['Name'] = $_POST['Name'];
		$author['Image'] = $image;
			
		parent::DbUpdateAuthor($author);
		
		return $authorId;
	}
	
    /**
     * Delete Author
     * 
     * @url POST /author/delete/
     */
    public function deleteAuthor() {
		$userId = parent::CheckAuthentication();
		$authorId = $_POST['AuthorId'];
		
		if(parent::CheckIfOwned($userId, "Author", $authorId) == true) {
			$authorInEventsCount = parent::GetRecordsCount('Event', $userId, 'AuthorId = '.$authorId);
			$authorInArticlesCount = parent::GetRecordsCount('Article', $userId, 'AuthorId = '.authorId);
			
			if(($authorInEventsCount + $authorInArticlesCount) > 0) {
				parent::DeActivateRecord('Author', $authorId);
			} else {
				$Author = parent::AuthorById($authorId);
				
				$this->unlinkRemovedAuthorImages($userId, $Author[Image]);
				parent::DeleteRecord('Author', $userId, $authorId);
			}
			
			return "OK";
		}
	}

	private function unlinkRemovedAuthorImages($userId, $image) {
		$userAuthorsFolder = Settings::getInstance()->p['userAuthorsFolder'];
		
		$imageFileToRemove = "../../".parent::GetImageUrl($userId, $image, $userAuthorsFolder, false, false);
		$imageThumbnailFileToRemove = "../../".parent::GetImageUrl($userId, $image, $userAuthorsFolder, true, false);
		
		if(strlen($image) > 0) {
			if(file_exists($imageFileToRemove))
				unlink($imageFileToRemove);
			if(file_exists($imageThumbnailFileToRemove))
				unlink($imageThumbnailFileToRemove);
		}
	}
}

?>