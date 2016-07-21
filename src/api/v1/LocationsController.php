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

require_once("./LocationsDatabaseHandler.php");

class LocationsController extends LocationsDatabaseHandler
{
    /**
     * Get Locations
     * 
     * @url GET /locations/$idUser
     */
    public function getLocations($idUser) {
		$filters = array();
		
		if(isset($_GET['city']) && $_GET['city'] != "") $filters[City] = $_GET['city'];
		if(isset($_GET['format'])) $format = $_GET['format'];
		
		return parent::Locations($idUser, $format, $filters);
	}
	
    /**
     * Get Location
     * 
     * @url GET /location/$locationId
     */
    public function getLocation($locationId) {
		return parent::LocationById($locationId);
	}
	
    /**
     * Update Location
     * 
     * @url POST /location/update/
     */
    public function updateLocation() {
		$userId = parent::CheckAuthentication();
		
		global $authIssueText;
		
		if(isset($_POST['LocationId']) && is_numeric($_POST['LocationId'])) {
			$locationId = $_POST['LocationId'];
		} else {
			parent::CreateLocation($_POST['Name'], $userId);
			$locationId = parent::GetLastId("Location", $userId);
		}
		
		parent::CheckIfOwned($userId, "Location", $locationId, true);
		
		$sql  = "UPDATE Location SET";
		$sql .= "  Name = \"".$this->mysqli->real_escape_string($_POST['Name'])."\"";
		$sql .= ", Address1 = \"".$this->mysqli->real_escape_string($_POST['Address1'])."\"";
		$sql .= ", Address2 = \"".$this->mysqli->real_escape_string($_POST['Address2'])."\"";
		$sql .= ", PostCode = \"".$_POST['PostCode']."\"";
		$sql .= ", City = \"".$this->mysqli->real_escape_string($_POST['City'])."\"";
		$sql .= ", Country = \"".$_POST['Country']."\"";
		$sql .= ", Description = \"".$this->mysqli->real_escape_string($_POST['Description'])."\"";
		$sql .= ", Phone = \"".$_POST['Phone']."\"";
		$sql .= ", Email = \"".$_POST['Email']."\"";
		$sql .= ", WebsiteLink = \"".$_POST['WebsiteLink']."\"";
		$sql .= ", FacebookLink = \"".$_POST['FacebookLink']."\"";
		$sql .= ", FlickrLink = \"".$_POST['FlickrLink']."\"";
		$sql .= " WHERE LocationId = ".$locationId;
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return $locationId;
	}
	
    /**
     * Delete Location
     * 
     * @url POST /location/delete/
     */
    public function deleteLocation() {
		$userId = parent::CheckAuthentication();
		
		$locationInEventsCount = parent::GetRecordsCount('Event', $userId, 'LocationId = '.$_POST['LocationId']);
		
		if($locationInEventsCount > 0) {
			parent::DeActivateRecord('Location', $_POST['LocationId']);
		} else {
			parent::DeleteRecord('Location', $userId, $_POST['LocationId']);
		}
	}
}

?>