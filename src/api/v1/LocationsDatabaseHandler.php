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
 * Manages the database operations about locations
 *
 * @author Simone Vitale
 */
class LocationsDatabaseHandler extends DatabaseHandler
{
	public function Locations($idUser, $format, $filters = null) {
		$sql = "SELECT LocationId, Name, Address1, Address2, PostCode, City, Country, Phone, Email, Description, WebsiteLink, FacebookLink, FlickrLink, Active FROM Location WHERE UserId = $idUser AND Active = true ";
		
		// Apply Filters
		if($filters != null) {
			foreach ($filters as $key => $value) {
				$sql .= " AND Location.$key = '$value' ";
			}
		}
		
		$sql .= " ORDER BY Name";
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);
		
		$locations = array();
		
		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$locations[] = array(
					'LocationId' => intval($row['LocationId']),
					'Name' => $row['Name'],
					'Address1' => $row['Address1'],
					'Address2' => $row['Address2'],
					'PostCode' => $row['PostCode'],
					'City' => $row['City'],
					'Country' => $row['Country'],
					'Phone' => $row['Phone'],
					'Email' => $row['Email'],
					'Description' => $row['Description'],
					'WebsiteLink' => $row['WebsiteLink'],
					'FacebookLink' => $row['FacebookLink'],
					'FlickrLink' => $row['FlickrLink'],
					'Active' => intval($row['Active']),
					'UserId' => intval($row['UserId']) );
			}
		}

		return $locations;
	}
	
	public function LocationById($LocationId) {
		$sql = "SELECT LocationId, Name, Address1, Address2, PostCode, City, Country, Description, Phone, Email, WebsiteLink, FacebookLink, FlickrLink, Active, UserId FROM Location WHERE LocationId = $LocationId";

		$result = $this->mysqli->query($sql);
		$recordsCount = mysqli_num_rows($result);

		$data = 0;

		if($recordsCount >= 1 && $result != null) {
			$row = mysqli_fetch_array($result);

			$data = array(	'LocationId' => intval($row['LocationId']),
							'Name' => $row['Name'],
							'Address1' => $row['Address1'],
							'Address2' => $row['Address2'],
							'PostCode' => $row['PostCode'],
							'City' => $row['City'],
							'Country' => $row['Country'],
							'Phone' => $row['Phone'],
							'Email' => $row['Email'],
							'Description' => $row['Description'],
							'WebsiteLink' => $row['WebsiteLink'],
							'FacebookLink' => $row['FacebookLink'],
							'FlickrLink' => $row['FlickrLink'],
							'Active' => intval($row['Active']),
							'UserId' => intval($row['UserId']) );
		}

		return $data;
	}
	
	public function CreateLocation($Name, $UserId) {
		global $authIssueText;
		
		$sql = "INSERT INTO Location (Name, UserId) ";
		$sql .= "VALUES(\"".$this->mysqli->real_escape_string($Name)."\", $UserId)";
		
		$result = $this->mysqli->query($sql) or die ($authIssueText);
		
		return $result;
	}
}

?>