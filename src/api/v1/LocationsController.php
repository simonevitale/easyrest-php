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

$mysqli = Database::getInstance()->getConnection();

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
		
		global $mysqli, $authIssueText;
		
		if(isset($_POST['IdLocation']) && is_numeric($_POST['IdLocation'])) {
			$locationId = $_POST['IdLocation'];
		} else {
			parent::CreateLocation($_POST['NameLocation'], $userId);
			$locationId = parent::GetLastId("Location", $userId);
		}
		
		parent::CheckIfOwned($userId, "Location", $locationId, true);
		
		$sql  = "UPDATE Location SET";
		$sql .= "  Name = \"".$mysqli->real_escape_string($_POST['NameLocation'])."\"";
		$sql .= ", Address1 = \"".$mysqli->real_escape_string($_POST['Address1Location'])."\"";
		$sql .= ", Address2 = \"".$mysqli->real_escape_string($_POST['Address2Location'])."\"";
		$sql .= ", PostCode = \"".$_POST['PostCodeLocation']."\"";
		$sql .= ", City = \"".$mysqli->real_escape_string($_POST['CityLocation'])."\"";
		$sql .= ", Country = \"".$_POST['CountriesLocation']."\"";
		$sql .= ", Description = \"".$mysqli->real_escape_string($_POST['DescriptionLocation'])."\"";
		$sql .= ", Phone = \"".$_POST['PhoneLocation']."\"";
		$sql .= ", Email = \"".$_POST['EmailLocation']."\"";
		$sql .= ", WebsiteLink = \"".$_POST['WebsiteLinkLocation']."\"";
		$sql .= ", FacebookLink = \"".$_POST['FacebookLinkLocation']."\"";
		$sql .= ", FlickrLink = \"".$_POST['FlickrLinkLocation']."\"";
		$sql .= " WHERE LocationId = ".$locationId;
		
		$result = $mysqli->query($sql) or die ($authIssueText);
		
		return $locationId;
	}
	
    /**
     * Delete Location
     * 
     * @url POST /location/delete/
     */
    public function deleteLocation() {
		global $mysqli;
	
		$authIdUser = parent::CheckAuthentication();
		
		$LocationInEventsCount = parent::GetRecordsCount('Event', $_POST['IdUser'], 'LocationId = '.$_POST['IdLocation']);
		
		if($LocationInEventsCount > 0) {
			parent::DeActivateRecord('Location', $_POST['IdLocation']);
		} else {
			parent::DeleteRecord('Location', $_POST['IdUser'], $_POST['IdLocation']);
		}
	}
}

?>