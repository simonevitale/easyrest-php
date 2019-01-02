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

// Watch out to not insert blank spaces in the HTML code (e.g. " <? ... ? >"

// The debug flag is only to check if the modules are loading correctly
// Enabling the debug the REST server WON'T work.
//$debug = true;

libxml_use_internal_errors(true);

require_once "messages.php";
require_once "functions.php";
require_once "Database.php";
require_once "Settings.php";
require_once "DatabaseHandler.php";

require __DIR__ . '/Jacwright/RestServer/RestServer.php';

$server = new \Jacwright\RestServer\RestServer('production');

// Load Modules

loadXMLModules("settings/Modules.xml");

function loadXMLModules($xmlFile) {
	global $debug, $server;

	$xml = simplexml_load_file($xmlFile) or die("Error: Cannot create object");

	if ($xml === false) {
		echo "Failed loading XML: ";
		foreach(libxml_get_errors() as $error) {
			if($debug) echo "<br>", $error->message;
		}
	} else {
		if($debug) echo "XML File loaded<br /><br />";
		
		foreach ($xml->module as $module) {
			if($debug) echo "Loading module ".$module[name]."<br />";
			
			foreach ($module->children() as $component) {
				if($debug) echo "Role: " . $component['role'] . ", File: " . $component[0];
				if($debug) echo "<br />";
				
				loadModule($component);
			}
			
			if($debug) echo "<br />";
		}
	}
	
	if($debug) echo "<br />";
	
	//print_r( $server);
	$server->handle();
}

function loadModule($component) {
	global $debug, $server;
	
	if (strcmp($component['role'], "Controller") == 0) {
		$controllerFile = "".$component[0];
		$controllerName = "".$component['name'];
		
		if($debug) echo "Loading ".$controllerName;
		
		require $controllerFile;
		$server->addClass($controllerName);
	}
}

?>
