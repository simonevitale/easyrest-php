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
include "libs/phpqrcode/qrlib.php"; 

require_once("./EventsDatabaseHandler.php");
require_once("./AuthorsDatabaseHandler.php");

class EventsController extends EventsDatabaseHandler
{
    /**
     * Get all the Events
	 * If authentication is not provided, it shows only published events
	 * If authentication is provided, it show published event for the authenticated user
	 *
     * @url GET /events
     * @url GET /events/$userId
	 */
	public function getEvents($userId = -1) {
		$authIdUser = parent::CheckAuthentication(false);
		
		if ($authIdUser > 0) {
			$authenticated = true;
			if($userId <= 0)
				$userId = $authIdUser;
		}
		
		$filters = array();
		$notFilters = array();
			
		$authorsDbHandler = new AuthorsDatabaseHandler;
		
		if(isset($_GET['userId']) && $_GET['userId'] != "") $filters[UserId] = $_GET['userId'];
		if(isset($_GET['language']) && $_GET['language'] != "") $filters[Language] = $_GET['language'];
		if(isset($_GET['published']) && $_GET['published'] != "") $filters[Published] = $_GET['published'];
		if(isset($_GET['author']) && $_GET['author'] != "") $filters[AuthorId] = $authorsDbHandler->AuthorByUniqueName($_GET['author'])[AuthorId];

		if($authenticated == false || isset($_GET['userId'])) {
			// If not authenticated shows only published events
			$filters[Published] = 1;
		}
		
		if(isset($_GET['upcoming']))   $upcoming = $_GET['upcoming']; else $upcoming = 1;
		if(isset($_GET['from']))  $from = $_GET['from']; else $from = -1;
		if(isset($_GET['count'])) $count = $_GET['count']; else $count = -1;
		if(isset($_GET['year']))  $year = $_GET['year']; else $year = null;
		if(isset($_GET['orderby'])) $orderby = $_GET['orderby']; else $orderby = "";
		
		return parent::Events($filters, $notFilters, $from, $count, $userId, $orderby, $upcoming, $year);
	}

    /**
     * Get Event
     * 
     * @url GET /event/$eventId
     */
    public function getEvent($eventId) {
		$event = parent::EventById($eventId);
		
		// Requires auth and ownership to show non published articles
		if($event['Published'] == 0) {
			$userId = parent::CheckAuthentication(true);
			parent::CheckIfOwned($userId, "Event", $eventId, true);
		}
		return $event;
	}
	
    /**
     * Get Event
     * 
     * @url GET /event/$eventId/qr/
     * @url GET /event/$eventId/qr/$size/
     * @url GET /event/$eventId/qr/$size/$ecc
     */
    public function getEventQrUrl($eventId, $size = 4, $ecc = "L") {
		$event = parent::EventById($eventId);
		
		// Requires auth and ownership to show non published articles
		if($event['Published'] == 0) {
			$userId = parent::CheckAuthentication(true);
			parent::CheckIfOwned($userId, "Event", $eventId, true);
		}
		
		$websiteUrl = Settings::getInstance()->p['websiteUrl'];
		$portalFolder = Settings::getInstance()->p['portalFolder'];
		$userUploadFolder = Settings::getInstance()->p['userUploadFolder'];
		$userEventsFolder = Settings::getInstance()->p['userEventsFolder'];
		
		$qrPrefix = "qr.event.";
		$qrExt = ".png";
		$qrFilename = $qrPrefix."$ecc.$size.".$eventId.$qrExt;
		$qrRelUrl = "../../$userUploadFolder/".$event['UserId']."/".$userEventsFolder."/".$qrFilename;
		$qrUrl = $websiteUrl."/".$portalFolder."/".$userUploadFolder."/".$event['UserId']."/".$userEventsFolder."/".$qrFilename;
		
		$data = $websiteUrl."/".$userEventsFolder."/".$eventId;
		
		if(!file_exists($qrRelUrl)) {
			QRcode::png($data, $qrRelUrl, $ecc, $size, 2);
		}
		
		echo $qrUrl;
	}
	
	/**
     * Update or Create Event
     * 
     * @url POST /event/update/
     */
    public function updateEvent() {
		$userId = parent::CheckAuthentication();
		
		$userEventsFolder = Settings::getInstance()->p['userEventsFolder'];
		
		if(isset($_POST['IdEvent']) && is_numeric($_POST['IdEvent'])) {
			$eventId = $_POST['IdEvent'];
		} else {
			parent::CreateEvent($_POST['TitleEvent'], $userId);
			$eventId = parent::GetLastId("Event", $userId);
		}
		
		parent::CheckIfOwned($userId, "Event", $eventId, true);
		
		$event = parent::EventById($eventId);
		$isImageUploading = (isset($_FILES['NewImageEvent']) && is_uploaded_file($_FILES['NewImageEvent']['tmp_name'])) ? 1 : 0;
		
		$destinationDirectory = "../../".parent::GetImageUrl($userId, "", $userEventsFolder, false, false, true)."/"; 

		if(strlen($_POST['ImageEvent']) == 0 || $isImageUploading) {
			$this->UnlinkRemovedEventImages($userId, $event['Image']);
		}
		
		// Upload new image
		if($isImageUploading == 1) {
			$image = uploadImage($_FILES['NewImageEvent'], $destinationDirectory, 350);
		}
		
		if(isset($_POST['TitleEvent'])) $event["Title"] = $_POST['TitleEvent'];
		if(isset($_POST['ImageEvent']) && $isImageUploading != 1) $event["Image"] = $_POST['ImageEvent']; else $event["Image"] = $image;
		if(isset($_POST['LanguagesEvent'])) $event["Language"] = $_POST['LanguagesEvent'];
		if(isset($_POST['DescriptionEvent'])) $event["Description"] = $_POST['DescriptionEvent'];
		if(isset($_POST['DateTimeEvent'])) $event["DateTime"] = $_POST['DateTimeEvent'];
		if(isset($_POST['FacebookLinkEvent'])) $event["FacebookLink"] = $_POST['FacebookLinkEvent'];
		if(isset($_POST['YouTubeLinkEvent'])) $event["YouTubeLink"] = $_POST['YouTubeLinkEvent'];
		if(isset($_POST['FlickrLinkEvent'])) $event["FlickrLink"] = $_POST['FlickrLinkEvent'];
		if(isset($_POST['PublishedEvent'])) $event["Published"] = $_POST['PublishedEvent'];
		if(isset($_POST['LocationsEvent']) && is_numeric($_POST['LocationsEvent']))
			$event["LocationId"] = $_POST['LocationsEvent'];
		if(isset($_POST['AuthorsEvent']) && is_numeric($_POST['AuthorsEvent']))
			$event["AuthorId"] = $_POST['AuthorsEvent'];
		
		if($event['LocationId'] === NULL) $event['LocationId'] = 0;
		if($event['AuthorId'] === NULL) $event['AuthorId'] = 0;
		
		parent::DbUpdateEvent($event);
		
		return $eventId;
	}
	
    /**
     * Delete Event
     * 
     * @url POST /event/delete/
     */
    public function deleteEvent() {
		$userId = parent::CheckAuthentication();
		
		if(parent::CheckIfOwned($userId, "Event", $_POST['IdEvent']) == true) {
			$Event = parent::EventById($_POST['IdEvent']);
			
			$this->UnlinkRemovedEventImages($userId, $Event['Image']);
			parent::DeleteRecord('Event', $_POST['IdUser'], $_POST['IdEvent']);
		}
	}

	private function UnlinkRemovedEventImages($userId, $image) {
		$userEventsFolder = Settings::getInstance()->p['userEventsFolder'];
		
		$imageFileToRemove = "../../".parent::GetImageUrl($userId, $image, $userEventsFolder, false, false);
		$imageThumbnailFileToRemove = "../../".parent::GetImageUrl($userId, $image, $userEventsFolder, true, false);
		
		if(strlen($image) > 0) {
			if(file_exists($imageFileToRemove))
				unlink($imageFileToRemove);
			if(file_exists($imageThumbnailFileToRemove))
				unlink($imageThumbnailFileToRemove);
		}
	}
}

?>