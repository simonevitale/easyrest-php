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

require_once("Database.php");

$mysqli = Database::getInstance()->getConnection();

class LogsController extends DatabaseHandler
{
    /**
     * Get Logs
     * 
     * @url GET /logs
     */
    public function getPrivateLogs() {
		global $mysqli;
		
		$authIdUser = parent::CheckAuthentication(true, true);
		
		$logs = null;
		
		if(isset($_GET['action'])) $action = $_GET['action']; else $action = null;
		if(isset($_GET['agent']))  $agent = $_GET['agent']; else $agent = null;
		if(isset($_GET['from']))  $from = $_GET['from']; else $from = 0;
		if(isset($_GET['count'])) $count = $_GET['count']; else $count = 50;
		
		$sql = "SELECT Action, Agent, UserEmail, DateTime, Ip, Location FROM Log ";
		$sql .= " WHERE LogId > 0 ";
		
		// Filter by action
		if($action != null && strlen($action) > 0) {
			$sql .= " AND Action = '" . $action . "' ";
		}
		// Filter by agent
		if($agent != null && strlen($agent) > 0) {
			$sql .= " AND Agent = '" . $agent . "' ";
		}
		$sql .= " ORDER BY LogId DESC LIMIT $from, $count ";

		$result = $mysqli->query($sql) or die ($authIssueText);
		$recordsCount = mysqli_num_rows($result);

		if($recordsCount >= 1 && $result != null) {
			while($row = mysqli_fetch_array($result)) {
				$logs[] = array(
					Action => $row['Action'], 
					Agent => $row['Agent'], 
					Email  => $row['UserEmail'],
					DateTime => gmdate("D, d M Y H:i:s", $row['DateTime']),
					Ip => $row['Ip'],
					Location => $row['Location']);
			}
		}
		
		return $logs;
    }
}