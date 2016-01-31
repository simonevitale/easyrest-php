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

require_once("./ArticlesDatabaseHandler.php");

class ArticlesController extends ArticlesDatabaseHandler
{
    /**
     * Get all the Articles
	 * If authentication is not provided, it shows only published events
	 * If authentication is provided, it show published event for the authenticated user
	 *
     * @url GET /articles
     * @url GET /articles/$userId
	 */
    public function getArticles($userId = -1) {
		$authIdUser = parent::CheckAuthentication(false);
		
		if ($authIdUser > 0) {
			$authenticated = true;
			//if($userId <= 0)
				$userId = $authIdUser;
		}
		
		$filters = array();
		$notFilters = array();
		
		if(isset($_GET['userId']) && $_GET['userId'] != "") 	  $filters[UserId] = $_GET['userId'];
		if(isset($_GET['language']) && $_GET['language'] != "")   $filters[Language]  = $_GET['language'];
		if(isset($_GET['published']) && $_GET['published'] != "") $filters[Published] = $_GET['published'];
		if(isset($_GET['category']) && $_GET['category'] != "")   $filters[Category]  = $_GET['category'];
		
		if(isset($_GET['exceptCategory']) && $_GET['exceptCategory'] != "") $notFilters[ExceptCategory] = $_GET['exceptCategory'];
		
		if($authenticated == false || isset($_GET['userId'])) {
			// If not authenticated shows only published articles
			$filters[Published] = 1;
		}
		
		if(isset($_GET['from']))  $from = $_GET['from']; else $from = -1;
		if(isset($_GET['count'])) $count = $_GET['count']; else $count = -1;
		
		return parent::Articles($filters, $notFilters, $from, $count, $userId);
	}
	
    /**
     * Get Article
     * 
     * @url GET /article/$articleId
     */
    public function getArticle($articleId) {
		$article = parent::ArticleById($articleId);
		
		// Requires auth and ownership to show non published articles
		if($article['Published'] == 0) {
			$userId = parent::CheckAuthentication(true);
			parent::CheckIfOwned($userId, "Article", $articleId, true);
		}
			
		return $article;
	}
	
    /**
     * Update Article
     * 
     * @url POST /article/update/
     */
    public function updateArticle() {
		$userId = parent::CheckAuthentication(true);
		
		$userArticlesFolder = Settings::getInstance()->p['userArticlesFolder'];
		
		if(isset($_POST['IdArticle']) && is_numeric($_POST['IdArticle'])) {
			$articleId = $_POST['IdArticle'];
		} else {
			parent::CreateArticle($_POST['TitleArticle'], $_POST['IdUser']);
			$articleId = parent::GetLastId("Article", $_POST['IdUser']);
		}
		
		parent::CheckIfOwned($userId, "Article", $articleId, true);
		
		$article = parent::ArticleById($articleId);
		$isImageUploading = (isset($_FILES['NewImageArticle']) && is_uploaded_file($_FILES['NewImageArticle']['tmp_name'])) ? 1 : 0;
		
		$destinationDirectory = "../../".parent::GetImageUrl($userId, "", $userArticlesFolder, false, false, true)."/"; 

		if(strlen($_POST['ImageArticle']) == 0 || $isImageUploading) {
			$this->unlinkRemovedArticleImages($userId, $article['Image']);
		}
		
		// Upload new image
		if($isImageUploading == 1) {
			$image = uploadImage($_FILES['NewImageArticle'], $destinationDirectory, 350);
		}
		
		if(isset($_POST['TitleArticle'])) $article['Title'] = $_POST['TitleArticle'];
		if(isset($_POST['ImageArticle']) && $isImageUploading != 1) $article["Image"] = $_POST['ImageArticle']; else $article["Image"] = $image;
		if(isset($_POST['CategoriesArticle'])) $article['Category'] = $_POST['CategoriesArticle'];
		if(isset($_POST['LanguagesArticle'])) $article['Language'] = $_POST['LanguagesArticle'];
		if(isset($_POST['DescriptionArticle'])) $article['Description'] = $_POST['DescriptionArticle'];
		if(isset($_POST['DateTimeArticle'])) $article['DateTime'] = $_POST['DateTimeArticle'];
		if(isset($_POST['YouTubeLinkArticle'])) $article['YouTubeLink'] = $_POST['YouTubeLinkArticle'];
		if(isset($_POST['FlickrLinkArticle'])) $article['FlickrLink'] = $_POST['FlickrLinkArticle'];
		if(isset($_POST['PublishedArticle'])) $article['Published'] = $_POST['PublishedArticle'];
		if($_POST['AuthorsArticle'] != null && is_numeric($_POST['AuthorsArticle']))
			$article['AuthorId'] = $_POST['AuthorsArticle'];
			
		if($article['AuthorId'] === NULL) $article['AuthorId'] = 0;
		
		parent::DbUpdateArticle($article);
	
		return $articleId;
	}
	
    /**
     * Delete Article
     * 
     * @url POST /article/delete/
     */
    public function deleteArticle() {
		$userId = parent::CheckAuthentication();
		
		if(parent::CheckIfOwned($userId, "Article", $_POST['IdArticle']) == true) {
			$Article = parent::ArticleById($_POST['IdArticle']);
			
			$this->unlinkRemovedArticleImages($userId, $Article['Image']);
			parent::DeleteRecord('Article', 1, $_POST['IdArticle']);
		}
	}

	private function unlinkRemovedArticleImages($userId, $image) {
		$userArticlesFolder = Settings::getInstance()->p['userArticlesFolder'];
		
		$imageFileToRemove = "../../".parent::GetImageUrl($userId, $image, $userArticlesFolder, false, false);
		$imageThumbnailFileToRemove = "../../".parent::GetImageUrl($userId, $image, $userArticlesFolder, true, false);
		
		if(strlen($image) > 0) {
			if(file_exists($imageFileToRemove))
				unlink($imageFileToRemove);
			if(file_exists($imageThumbnailFileToRemove))
				unlink($imageThumbnailFileToRemove);
		}
	}
}

?>