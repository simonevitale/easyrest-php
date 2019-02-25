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

libxml_use_internal_errors(true);

/*
* Settings singleton class
*/
class Settings {
	private static $_instance; // The only instance
	
	public $p;
	
	/*
	Get the instance of the Object
	@return Instance
	*/
	public static function getInstance() {
		if(!self::$_instance) { // If no instance then make one
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	// Constructor
	private function __construct() {
		$xml = simplexml_load_file("settings/Settings.xml") or die("Error: Cannot create object");
		
		if ($xml === false) {
			echo "Failed loading XML: ";
			foreach(libxml_get_errors() as $error) {
				echo "<br>", $error->message;
			}
		} else {
			$this->p = array();
			
			foreach ($xml->children() as $k => $v) {
				$this->p[$k] = $v;
			}
		}
	}
	
	// Magic method clone is empty to prevent the duplication of the object
	private function __clone() { }
}
?>
