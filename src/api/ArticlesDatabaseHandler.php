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

require_once "./AuthorsDatabaseHandler.php";
require_once "./UsersDatabaseHandler.php";
	
/**
 * Manages the database operations about articles
 *
 * @author Simone Vitale
 */
class ArticlesDatabaseHandler extends DatabaseHandler
{
	public function Articles($filters = null, $notFilters = null, $from = -1, $count = -1, $userId = -1) {	
		global $authIssueText;
		
		$userArticlesFolder = Settings::getInstance()->p['userArticlesFolder'];

		$articles = array();
		
		$sql  = "SELECT Article.UserId, Article.ArticleId, Article.Title, Article.Category, Article.Language, Article.CreationDateTime, Article.DateTime, Article.Image, Article.Published, Article.Description, Article.YouTubeLink, Author.Name AS 'Author' ";
		$sql .= "FROM Article ";
		$sql .= "LEFT JOIN Author ON Article.AuthorId = Author.AuthorId ";
		$sql .= "WHERE ArticleId > 0 ";
		if($userId > 0) $sql .= "AND Article.UserId = $userId ";
			
		// Apply Filters
		if($filters != null) {
			foreach ($filters as $key => $value) {
				// -orblank postfix for language filter shows also records with no set language
				$orBlankIndex = strpos($value, '-orblank');
				if(strcmp($key, "Language") == 0 && $orBlankIndex !== false)
					$sql .= " AND (Article.$key = '".substr($value, 2)."' OR Article.$key = '')";
				else
					$sql .= " AND Article.$key = '$value' ";
			}
			foreach ($notFilters as $key => $value) {
				$sql .= " AND Article.$key <> '$value' ";
			}
		}
		
		$sql .= "ORDER BY ArticleId DESC \n";
		
		if($from != -1 && $count != -1)
			$sql .= "LIMIT $from, $count \n";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$imageUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($row[UserId], $row[Image], $userArticlesFolder, false) : "";
				$imageThumbnailUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($row[UserId], $row[Image], $userArticlesFolder, true) : "";
				$ShortDescription = parent::substrwords(strip_tags($row['Description']), 120);

				$articles[] = array (
					'ArticleId' => $row['ArticleId'],
					'Title' => $row['Title'],
					'Author' => $row['Author'],
					'CreationDateTime' => $row['CreationDateTime'],
					'DateTime' => $row['DateTime'],
					'Category' => $row['Category'],
					'Language' => $row['Language'],
					'ShortDescription' => $ShortDescription,
					'Image' => $imageUrl,
					'Thumbnail' => $imageThumbnailUrl,
					'YouTubeLink' => $row['YouTubeLink'],
					'Published' => $row['Published'],
					'UserId' => $row['UserId']
				);
			}
		}
		
		return $articles;
	}

	public function ArticleById($IdArticle, $IdUser = null) {		
		$userArticlesFolder = Settings::getInstance()->p['userArticlesFolder'];

		$ArticleFieldsSql = " Article.UserId, Article.ArticleId, Article.Title, Article.Image, Article.Description AS Description, Article.DateTime, Article.YouTubeLink, Article.FlickrLink, Article.Language, Article.Published, Article.Category ";
		
		$sql = "SELECT $ArticleFieldsSql, AuthorId FROM Article WHERE ArticleId = $IdArticle";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = 0;

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);
			
			$imageUrl = parent::GetImageUrl($row['UserId'], $row['Image'], $userArticlesFolder);
			$imageThumbnailUrl = parent::GetImageUrl($row['UserId'], $row['Image'], $userArticlesFolder, true);
			
			$ShortDescription = parent::substrwords(strip_tags($row['Description']), 120);
			
			$AuthorsHandler = new AuthorsDatabaseHandler;
			
			$data = array(	'ArticleId' => intval($row['ArticleId']),
							'Title' => $row['Title'],
							'Image' => $row['Image'],
							'ImageUrl' => $imageUrl,
							'ThumbnailUrl' => $imageThumbnailUrl,
							'Description' => $row['Description'],
							'DateTime' => $row['DateTime'],
							'YouTubeLink' => $row['YouTubeLink'],
							'FlickrLink' => $row['FlickrLink'],
							'Language' => $row['Language'],
							'Published' => intval($row['Published']),
							'Category' => $row['Category'],
							'ShortDescription' => $ShortDescription,
							'Author' => $AuthorsHandler->AuthorById($row['AuthorId']),
							'UserId' => intval($row['UserId']));
		}

		return $data;
	}
	
	public function CreateArticle($Title, $UserId) {
		global $authIssueText;
			
		$UsersHandler = new UsersDatabaseHandler;
		
		$User = $UsersHandler->UserById($UserId);
		
		$Language = $User['Language'];
		
		$sql = "INSERT INTO Article (Title, UserId, CreationDateTime, Language) ";
		$sql .= "VALUES(\"".$this->mysqli->real_escape_string($Title)."\", $UserId, '".time()."', \"$Language\")";
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	public function DbUpdateArticle($Article) {
		global $authIssueText;
		
		$sql  = "UPDATE Article SET";
		$sql .= "  Title = \"".$this->mysqli->real_escape_string($Article['Title'])."\"";
		$sql .= ", Image = \"".$Article['Image']."\"";
		$sql .= ", Category = \"".$Article['Category']."\"";
		$sql .= ", Language = \"".$Article['Language']."\"";
		$sql .= ", Description = \"".$this->mysqli->real_escape_string($Article['Description'])."\"";
		$sql .= ", DateTime = \"".$Article['DateTime']."\"";
		$sql .= ", YouTubeLink = \"".$this->mysqli->real_escape_string($Article['YouTubeLink'])."\"";
		$sql .= ", FlickrLink = \"".$this->mysqli->real_escape_string($Article['FlickrLink'])."\"";
		$sql .= ", Published = ".$Article['Published'];
		$sql .= ", AuthorId = ".$Article['AuthorId'];
		$sql .= " WHERE ArticleId = ".$Article['ArticleId'];
		
		return $this->mysqli->query($sql) or die ($authIssueText);
	}
}

?>