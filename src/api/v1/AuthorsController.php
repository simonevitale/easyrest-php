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
    public function getAuthors($userId = null)
    {
		if(isset($_GET['format'])) $format = $_GET['format']; else $format = null;
		
		return parent::Authors($userId, $format);
	}
	
    /**
     * Get Author
     * 
     * @url GET /author/$authorId
     */
    public function getAuthor($authorId)
	{
		return parent::AuthorById($authorId);
	}

    /**
     * Test Create Author
     * 
     * @url GET /authors/test/create
     */
    public function testCreate()
	{
		parent::CreateAuthor($_GET['Name'], $_GET['Id']);
		$authorId = parent::GetLastId("Author", $_GET['Id']);
		return $authorId;
	}
	
    /**
     * Update Author
     * 
     * @url POST /author/update/
     */
    public function updateAuthor() {
		$userId = parent::CheckAuthentication(true);
		
		$userAuthorsFolder = Settings::getInstance()->p['userAuthorsFolder'];
		
		if(isset($_POST['IdAuthor']) && is_numeric($_POST['IdAuthor'])) {
			$authorId = $_POST['IdAuthor'];
		} else {
			parent::CreateAuthor($_POST['NameAuthor'], $userId);
			$authorId = parent::GetLastId("Author", $userId);
		}
	
		$author = parent::AuthorById($authorId);
		$isImageUploading = (isset($_FILES['NewPictureAuthor']) && is_uploaded_file($_FILES['NewPictureAuthor']['tmp_name'])) ? 1 : 0;
		$image = $_POST['PictureAuthor'];
		
		$destinationDirectory = "../../".parent::GetImageUrl($userId, "", $userAuthorsFolder, false, false, true)."/"; 
		
		if(strlen($_POST['PictureAuthor']) == 0 || $isImageUploading) {
			$this->unlinkRemovedAuthorImages($_POST['IdUser'], $author['Image']);
		}
		
		// Upload new image
		if($isImageUploading == 1) {
			$image = uploadImage($_FILES['NewPictureAuthor'], $destinationDirectory, 200);
		}
		
		$author['Name'] = $_POST['NameAuthor'];
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
		global $websiteUrl, $userUploadFolder, $userAuthorsFolder, $ThumbnailPrefix;
	
		$authIdUser = parent::CheckAuthentication();
		
		$UserId = $_POST['IdUser'];
		
		$AuthorInEventsCount = parent::GetRecordsCount('Event', $UserId, 'AuthorId = '.$_POST['IdAuthor']);
		$AuthorInArticlesCount = parent::GetRecordsCount('Article', $UserId, 'AuthorId = '.$_POST['IdAuthor']);
		
		if(($AuthorInEventsCount + $AuthorInArticlesCount) > 0) {
			parent::DeActivateRecord('Authors', $_POST['IdAuthor']);
		} else {
			$Author = parent::AuthorById($_POST['IdAuthor']);
			
			$this->unlinkRemovedAuthorImages($UserId, $Author[Image]);
			parent::DeleteRecord('Author', $UserId, $_POST['IdAuthor']);
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