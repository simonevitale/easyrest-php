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

require_once("./AuthorsDatabaseHandler.php");
require_once("./UsersDatabaseHandler.php");
require_once("./LocationsDatabaseHandler.php");
	
/**
 * Manages the database operations about events
 *
 * @author Simone Vitale
 */
class EventsDatabaseHandler extends DatabaseHandler
{
	function Events($filters = null, $notFilters = null, $from = -1, $count = -1, $userId = -1, $orderBy = "", $upcoming = 1, $year = null) {
		$userEventsFolder = Settings::getInstance()->p['userEventsFolder'];

		$events = array();
		
		$sql  = "SELECT Event.EventId, Event.UserId, Event.Title, Event.DateTime, Event.Description, Event.Image, Event.Language, Event.Published, Event.FacebookLink, Event.YouTubeLink, Event.FlickrLink, Author.Name AS 'Author', Location.Name AS 'Location' ";
		$sql .= "FROM Event ";
		$sql .= "LEFT JOIN Author ON Event.AuthorId = Author.AuthorId ";
		$sql .= "LEFT JOIN Location ON Event.LocationId = Location.LocationId ";
		$sql .= "WHERE EventId > 0 ";
		if($userId > 0) $sql .= "AND Event.UserId = $userId ";

		//$filters['AuthorId'] = 1;
		
		// Apply Filters
		if($filters != null) {
			// Language filter shows also records with no set language
			foreach ($filters as $key => $value) {
				if(strcmp($key, "Language") == 0)
					$sql .= " AND (";
				else
					$sql .= " AND ";
				
				if(strcmp(gettype($value), "string") == 0)
					$sql .= " Event.$key = '$value' ";
				else
					$sql .= " Event.$key = $value ";
				
				if(strcmp($key, "Language") == 0)
					$sql .= " OR Event.$key = '') ";
			}
			foreach ($notFilters as $key => $value) {
				$sql .= " AND Event.$key <> '$value' ";
			}
		}
		
		// Showing upcoming events
		$tolleranceHours = 12;
		$d = date('Y-m-d H:m:s',strtotime('-'.$tolleranceHours.' hours'));
		if($upcoming == 1)
			$sql .= " AND Event.DateTime <> '' AND Event.DateTime >= '".$d."' ";
			
		// Showing past events
		if($upcoming == -1)
			$sql .= " AND Event.DateTime <> '' AND Event.DateTime < '".date('Y-m-d')."' ";
			
		if($year != null)
			$sql .= " AND date(Event.DateTime) BETWEEN date('$year-01-01') AND date('$year-12-31') ";
		
		if(strlen($orderBy) > 0)
			$sql .= "ORDER BY $orderBy ";
		else
			$sql .= "ORDER BY DateTime ASC, EventId DESC ";
		
		if($from != -1 && $count != -1)
			$sql .= " LIMIT $from, $count \n";
		
		//echo $sql;
		$result = $this->mysqli->query($sql);
		
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			$rowNum = 1;

			while($row = mysqli_fetch_array($result)) {
				$imageThumbnailUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($row[UserId], $row[Image], $userEventsFolder, true) : "";
				$imageUrl = (strlen($row[Image]) > 0) ? parent::GetImageUrl($row[UserId], $row[Image], $userEventsFolder, false) : "";
				
				$ShortDescription = parent::substrwords(strip_tags($row['Description']), 120);

				$events[] = array (
					'EventId' => $row['EventId'],
					'Title' => $row['Title'],
					'Author' => $row['Author'],
					'Location' => $row['Location'],
					'DateTime' => $row['DateTime'],
					'ShortDescription' => $ShortDescription,
					'Image' 	=> $imageUrl,
					'Thumbnail' => $imageThumbnailUrl,
					'FacebookLink' => $row['FacebookLink'],
					'YouTubeLink' => $row['YouTubeLink'],
					'FlickrLink' => $row['FlickrLink'],
					'Language' => $row['Language'],
					'Published' => $row['Published'],
					'UserId' => $row['UserId']
				);
			}
		}

		return $events;
	}
	
	function EventById($EventId) {
		$userEventsFolder = Settings::getInstance()->p['userEventsFolder'];

		$EventFieldsSql = " Event.UserId, Event.EventId, Event.Title, Event.Image, Event.Description AS Description, Event.CreationDateTime, Event.DateTime, Event.FacebookLink, Event.YouTubeLink, Event.FlickrLink, Event.Language, Event.Published, Event.Statistics ";
		
		$sql = "SELECT $EventFieldsSql, AuthorId, LocationId FROM Event WHERE Event.EventId = $EventId";
		
		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = 0;

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);
			
			$imageUrl = parent::GetImageUrl($row['UserId'], $row['Image'], $userEventsFolder);
			$imageThumbnailUrl = parent::GetImageUrl($row['UserId'], $row['Image'], $userEventsFolder, true);
			
			$ShortDescription = parent::substrwords(strip_tags($row['Description']), 120);
			$Description = $row['Description'];
			
			$AuthorsDbHandler = new AuthorsDatabaseHandler;
			$LocationsDbHandler = new LocationsDatabaseHandler;
			
			$data = array(	'EventId' => $row['EventId'],
							'Title' => $row['Title'],
							'Image' => $row['Image'],
							'ImageUrl' => $imageUrl,
							'ThumbnailUrl' => $imageThumbnailUrl,
							'Description' => $Description,
							'CreationDateTime' => $row['CreationDateTime'],
							'DateTime' => $row['DateTime'],
							'FacebookLink' => $row['FacebookLink'],
							'YouTubeLink' => $row['YouTubeLink'],
							'FlickrLink' => $row['FlickrLink'],
							'Language' => $row['Language'],
							'Published' => $row['Published'],
							'ShortDescription' => $ShortDescription,
							'LocationId' => $row['LocationId'],
							'Location' => $LocationsDbHandler->LocationById($row['LocationId']),
							'AuthorId' => $row['AuthorId'],
							'Author' => $AuthorsDbHandler->AuthorById($row['AuthorId']),
							'UserId' => $row['UserId'],
							'Statistics' => $row['Statistics']);
		}

		return $data;
	}
	
	function CreateEvent($Title, $UserId) {
		global $authIssueText;
		
		$UsersHandler = new UsersDatabaseHandler;
		
		$User = $UsersHandler->UserById($UserId);
		
		$Language = $User['Language'];

		$sql = "INSERT INTO Event (Title, UserId, CreationDateTime, Language) ";
		$sql .= "VALUES('$Title', $UserId, '".time()."', '$Language')";
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
	
	function DbUpdateEvent($event) {
		global $authIssueText;
		
		$sql  = "UPDATE Event SET";
		$sql .= "  Title = \"".$this->mysqli->real_escape_string($event['Title'])."\"";
		$sql .= ", LocationId = ".$event['LocationId'];
		$sql .= ", AuthorId = ".$event['AuthorId'];
		$sql .= ", Image = \"".$event['Image']."\"";
		$sql .= ", Description = \"".$this->mysqli->real_escape_string($event['Description'])."\"";
		$sql .= ", DateTime = \"".$event['DateTime']."\"";
		$sql .= ", FacebookLink = \"".$event['FacebookLink']."\"";
		$sql .= ", YouTubeLink = \"".$event['YouTubeLink']."\"";
		$sql .= ", FlickrLink = \"".$event['FlickrLink']."\"";
		$sql .= ", Statistics = \"".$this->mysqli->real_escape_string($event['Statistics'])."\"";
		$sql .= ", Language = \"".$event['Language']."\"";
		$sql .= ", Published = ".$event['Published'];
		$sql .= " WHERE EventId = ".$event['EventId'];
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
	}
}

?>