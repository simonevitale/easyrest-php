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

require_once("./libs/Hashids/HashGenerator.php");
require_once("./libs/Hashids/Hashids.php");
	
/**
 * Manages the database operations about events
 *
 * @author Simone Vitale
 */
class EventsDatabaseHandler extends DatabaseHandler {
	function Events($filters = null, $notFilters = null, $from = -1, $count = -1, $userId = -1, $orderBy = "", $upcoming = 1, $year = null) {
		$userEventsFolder = Settings::getInstance()->p['userEventsFolder'];

		$events = array();
		
		$serverTimeZone = "+00:00";
		
		$sql  = "SET time_zone='$serverTimeZone'; ";
		$this->mysqli->query($sql);
		
		$sql  = "SELECT Event.EventId, Event.UserId, Event.Title, Event.DateTime, Event.TimeZone, Event.Description, Event.Image, Event.Language, Event.Published, Event.FacebookLink, Event.YouTubeLink, Event.FlickrLink, Author.Name AS 'Author', Location.Name AS 'Location' ";
		$sql .= "FROM Event ";
		$sql .= "LEFT JOIN Author ON Event.AuthorId = Author.AuthorId ";
		$sql .= "LEFT JOIN Location ON Event.LocationId = Location.LocationId ";
		$sql .= "WHERE EventId > 0 ";
		if($userId > 0) $sql .= "AND Event.UserId = $userId ";
		
		// Apply Filters
		if($filters != null) {
			foreach ($filters as $key => $value) {
				// -orblank postfix for language filter shows also records with no set language
				$orBlankIndex = strpos($value, '-orblank');
				if(strcmp($key, "Language") == 0 && $orBlankIndex !== false)
					$sql .= " AND (Event.$key = '".substr($value, 2)."' OR Event.$key = '')";
				else
					$sql .= " AND Event.$key = '$value' ";
			}
			foreach ($notFilters as $key => $value) {
				$sql .= " AND Event.$key <> '$value' ";
			}
		}
		
		date_default_timezone_set('Europe/London');
		
		// Showing upcoming events
		//SELECT date(Event.DateTime), date(CONVERT_TZ('2018-02-23 22:00', '+00:00', '+03:00')) FROM Event
		if($upcoming == 1)
			$sql .= " AND Event.DateTime <> '' AND date(Event.DateTime) >= date(CONVERT_TZ('".date('Y-m-d H:i', strtotime('-6 hours'))."', '$serverTimeZone', Event.TimeZone))";
			//$sql .= " AND Event.DateTime <> '' AND Event.DateTime >= '".date('Y-m-d')."' ";
			
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
					'TimeZone' => $row['TimeZone'],
					'ShortDescription' => $ShortDescription,
					'Image' 	=> $row[Image],
					'ImageUrl' 	=> $imageUrl,
					'ThumbnailUrl' => $imageThumbnailUrl,
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

		$EventFieldsSql = " Event.UserId, Event.EventId, Event.Title, Event.Image, Event.Description AS Description, Event.CreationDateTime, Event.DateTime, Event.TimeZone, Event.FacebookLink, Event.YouTubeLink, Event.FlickrLink, Event.Language, Event.Published "; //, Event.Statistics
		
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
			$UsersDbHandler = new UsersDatabaseHandler;
			
			$hashids = new Hashids\Hashids('SquizMaster', 4, "abcdefghijklmnopqrstuvwxyz1234567890");
			
			$data = array(	'EventId' => intval($row['EventId']),
							'EventCode' => $hashids->encode($row['EventId']),
							'Title' => $row['Title'],
							'Image' => $row['Image'],
							'ImageUrl' => $imageUrl,
							'ThumbnailUrl' => $imageThumbnailUrl,
							'Description' => $Description,
							'CreationDateTime' => $row['CreationDateTime'],
							'DateTime' => $row['DateTime'],
							'TimeZone' => parseTimeZoneStringToHours($row['TimeZone']),
							'FacebookLink' => $row['FacebookLink'],
							'YouTubeLink' => $row['YouTubeLink'],
							'FlickrLink' => $row['FlickrLink'],
							'Language' => $row['Language'],
							'Published' => $row['Published'],
							'ShortDescription' => $ShortDescription,
							'LocationId' => intval($row['LocationId']),
							'Location' => $row['LocationId'] > 0 ? $LocationsDbHandler->LocationById($row['LocationId']) : null,
							'AuthorId' => intval($row['AuthorId']),
							'Author' => $row['AuthorId'] > 0 ? $AuthorsDbHandler->AuthorById($row['AuthorId']) : null,
							'UserId' => intval($row['UserId']),
							'User' => $row['UserId'] > 0 ? $UsersDbHandler->UserById($row['UserId']) : null
							);
		}
		$result->free();
		return $data;
	}
	
	function CreateEvent($Title, $UserId) {
		global $authIssueText;
		
		$UsersHandler = new UsersDatabaseHandler;
		
		$User = $UsersHandler->UserById($UserId);
		
		$Language = (intval($User['Country']) == 105) ? "it" : $User['Language'];

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
		$sql .= ", TimeZone = \"".parseTimeZoneHoursToString($event['TimeZone'])."\"";
		$sql .= ", FacebookLink = \"".$event['FacebookLink']."\"";
		$sql .= ", YouTubeLink = \"".$event['YouTubeLink']."\"";
		$sql .= ", FlickrLink = \"".$event['FlickrLink']."\"";
		//$sql .= ", Statistics = \"".$this->mysqli->real_escape_string($event['Statistics'])."\"";
		$sql .= ", Language = \"".$event['Language']."\"";
		$sql .= ", Published = ".$event['Published'];
		$sql .= " WHERE EventId = ".$event['EventId'];
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
	}
}

?>