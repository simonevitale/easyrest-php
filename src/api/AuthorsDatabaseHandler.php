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
 * Manages the database operations about authors
 *
 * @author Simone Vitale
 */
class AuthorsDatabaseHandler extends DatabaseHandler
{
	public function Authors($userId) {
		$userAuthorsFolder = Settings::getInstance()->p['userAuthorsFolder'];
		
		$UserHandler = new UsersDatabaseHandler();
		$User = $UserHandler->UserById($userId);
		
		if($User == null)
			throw new RestException(401, "Unauthorized");

		$sql = "SELECT AuthorId, Name, UniqueName, Image, Email, Website, AllowContact FROM Author WHERE UserId = $userId AND Active = true ORDER BY Name";

		$result = $this->mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);

		$authors = array();
		
		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$imageUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($userId, $row[Image], $userAuthorsFolder) : "";
				$imageThumbnailUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($userId, $row[Image], $userAuthorsFolder, true) : "";

				$authors[] = array (
					'AuthorId' => intval($row['AuthorId']),
					'UniqueName' => $row['UniqueName'],
					'Name' => $row['Name'],
					'Image' => $imageUrl,
					'Thumbnail' => $imageThumbnailUrl,
					'Email' => $row['Email'],
					'Website' => $row['Website'],
					'AllowContact' => $row['AllowContact']
				);
			}
		}
		
		return $authors;
	}
	
	private function AuthorBy($condition) {
		$userAuthorsFolder = Settings::getInstance()->p['userAuthorsFolder'];

		$sql = "SELECT AuthorId, Name, UniqueName, Image, UserId, Email, Website, AllowContact FROM Author $condition";

		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = 0;

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);

			$authorPictureUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($row['UserId'], $row['Image'], $userAuthorsFolder): "";
			$authorThumbnailUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($row['UserId'], $row['Image'], $userAuthorsFolder, true): "";
			
			$data = array(	'AuthorId' => intval($row['AuthorId']),
							'UniqueName' => $row['UniqueName'],
							'Name' => $row['Name'],
							'Image' => $row['Image'],
							'ImageUrl' => $authorPictureUrl,
							'ThumbnailUrl' => $authorThumbnailUrl,
							'UserId' => intval($row['UserId']),
							'Email' => $row['Email'],
							'Website' => $row['Website'],
							'AllowContact' => $row['AllowContact']);
		}

		return $data;
	}
	
	public function AuthorById($authorId) {
		return $this->AuthorBy("WHERE AuthorId = $authorId");
		/*$userAuthorsFolder = Settings::getInstance()->p['userAuthorsFolder'];

		$sql = "SELECT AuthorId, Name, IdName, Image, UserId FROM Author WHERE AuthorId = $authorId";

		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = 0;

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);

			$authorPictureUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($row['UserId'], $row['Image'], $userAuthorsFolder): "";
			$authorThumbnailUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($row['UserId'], $row['Image'], $userAuthorsFolder, true): "";
			
			$data = array(	'AuthorId' => intval($row['AuthorId']),
							'IdName' => $row['IdName'],
							'Name' => $row['Name'],
							'Image' => $row['Image'],
							'ImageUrl' => $authorPictureUrl,
							'ThumbnailUrl' => $authorThumbnailUrl,
							'UserId' => intval($row['UserId']));
		}

		return $data;*/
	}
	
	public function AuthorByUniqueName($authorUniqueName) {
		return $this->AuthorBy("WHERE LOWER(UniqueName) = LOWER('$authorUniqueName')");
	}
	
	public function CreateAuthor($name, $userId) {
		global $authIssueText;
		
		$sql = "INSERT INTO Author (Name, UserId) VALUES (\"".$this->mysqli->real_escape_string($name)."\", $userId)";
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	public function CheckIfIdNameExists($uniqueName) {
		$sql = "SELECT UniqueName FROM Author WHERE LOWER(UniqueName) = LOWER('$uniqueName')";

		$result = $this->mysqli->query($sql);
		return(mysqli_num_rows($result) > 0);
	}
	
	public function DbUpdateAuthor($author) {
		global $authIssueText;
		
		$sql  = "UPDATE Author SET";
		$sql .= "  Name = \"".$this->mysqli->real_escape_string($author['Name'])."\"";
		$sql .= ", UniqueName = \"".$this->mysqli->real_escape_string($author['UniqueName'])."\"";
		$sql .= ", Image = \"".$author['Image']."\"";
		$sql .= ", Email = \"".$author['Email']."\"";
		$sql .= ", Website = \"".$author['Website']."\"";
		$sql .= ", AllowContact = ".$author['AllowContact'];
		$sql .= " WHERE AuthorId = ".$author['AuthorId'];
		
		return $this->mysqli->query($sql) or die ($authIssueText);
	}
}

?>