<?

// Return the timezone offset from the London/Greenwhich universal time
function getTimeOffset($dtz) {
	$date = new DateTime(null, new DateTimeZone('Europe/London'));
	
	$timeOffset = $dtz->getOffset($date);
	$timeOffset = ($timeOffset / 60 / 60);
	
	return $timeOffset;
}

function getTimeOffsetsList() {
	$timeOffsets = array();
	
	$identifiers = DateTimeZone::listIdentifiers();
	
	foreach($identifiers as $timezone) {
		// A $timezone identifier is something like "Europe/Rome"
		$dtz = new DateTimeZone($timezone);
		$timeOffset = getTimeOffset($dtz);
		
		if (!in_array($timeOffset, $timeOffsets)) {
			array_push($timeOffsets, $timeOffset);
		}
	}
	
	sort($timeOffsets);

	return $timeOffsets;
}

function getTimeOffsetsByIdentifiers($identifiers) {
	$timeOffsets = array();
	
	foreach($identifiers as $timezone) {
		// A $timezone identifier is something like "Europe/Rome"
		$dtz = new DateTimeZone($timezone);
		$timeOffset = getTimeOffset($dtz);
		
		$timeOffsets[$timezone] = $timeOffset;
	}
	
	return $timeOffsets;
}

function parseTimeZoneStringToHours ($t) {
	$val = null;
	
	if($t != null && strlen($t) == 6) {
		$val = intval(substr($t, 1, 3)) + intval(substr($t, 4, 2)) / 60;
		if(strcmp(substr($t, 0, 1), "-") == 0)
			$val *= -1;
	}
	
	return $val;
}

function parseTimeZoneHoursToString ($t) {
	if($t != null) {
		$t = doubleval($t);
		
		if($t >= -11 && $t <= 14) {
			return (($t >= 0) ? "+" : "-") . ((abs($t) < 10) ? "0" : "") . abs(intval($t)) . ":" . abs(getDecimalPart($t) * 60) . ((abs(getDecimalPart($t)) == 0) ? "0" : "");
		}
	}
	return null;
}

function getDecimalPart($n) {
	return $n - intval($n);
}

?>