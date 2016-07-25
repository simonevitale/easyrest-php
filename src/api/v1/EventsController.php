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
		
		if(isset($_POST['EventId']) && is_numeric($_POST['EventId'])) {
			$eventId = $_POST['EventId'];
		} else {
			parent::CreateEvent($_POST['Title'], $userId);
			$eventId = parent::GetLastId("Event", $userId);
		}
		
		parent::CheckIfOwned($userId, "Event", $eventId, true);
		
		$event = parent::EventById($eventId);
		$isImageUploading = (isset($_FILES['NewImage']) && is_uploaded_file($_FILES['NewImage']['tmp_name'])) ? 1 : 0;
		
		$destinationDirectory = "../../".parent::GetImageUrl($userId, "", $userEventsFolder, false, false, true)."/";

		if(strlen($_POST['Image']) == 0 || $isImageUploading) {
			$this->UnlinkRemovedEventImages($userId, $event['Image']);
		}
		
		// Upload new image
		if($isImageUploading == 1) {
			$image = uploadImage($_FILES['NewImage'], $destinationDirectory, 350);
		}
		
		$published = 0;
		if(strcmp($_POST['Published'], "true") == 0 || $_POST['Published'] == 1 || $_POST['Published'] == true) $published = 1;
		
		if(isset($_POST['Title'])) $event["Title"] = $_POST['Title'];
		if(isset($_POST['Image']) && $isImageUploading != 1) $event["Image"] = $_POST['Image']; else $event["Image"] = $image;
		if(isset($_POST['Language'])) $event["Language"] = $_POST['Language'];
		if(isset($_POST['Description'])) $event["Description"] = $_POST['Description'];
		if(isset($_POST['DateTime'])) $event["DateTime"] = $_POST['DateTime'];
		if(isset($_POST['FacebookLink'])) $event["FacebookLink"] = $_POST['FacebookLink'];
		if(isset($_POST['YouTubeLink'])) $event["YouTubeLink"] = $_POST['YouTubeLink'];
		if(isset($_POST['FlickrLink'])) $event["FlickrLink"] = $_POST['FlickrLink'];
		if(isset($_POST['Published'])) $event["Published"] = $published;
		if(isset($_POST['LocationId']) && is_numeric($_POST['LocationId']))
			$event["LocationId"] = $_POST['LocationId'];
		if(isset($_POST['AuthorId']) && is_numeric($_POST['AuthorId']))
			$event["AuthorId"] = $_POST['AuthorId'];
		
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
		
		if(parent::CheckIfOwned($userId, "Event", $_POST['EventId']) == true) {
			$Event = parent::EventById($_POST['EventId']);
			
			$this->UnlinkRemovedEventImages($userId, $Event['Image']);
			parent::DeleteRecord('Event', $_POST['UserId'], $_POST['EventId']);
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