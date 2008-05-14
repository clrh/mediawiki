<?php
/**
 * @package SecureWidgets
 * @category Widgets
 * @author Jean-Lou Dupont
 * @version @@package-version@@ 
 * @Id $Id$
 */

class Widget {

	/**
	 * Widget name (unique identifier)
	 */
	var $name = null;

	/**
	 * Widget Version information
	 */
	var $version = null;

	/**
	 * Code (e.g. HTML/css/js)
	 */
	var $code   = null;
	
	/**
	 * Input parameters
	 */
	var $params = array();

	/**
	 * Constructor
	 */
	public function __construct( $name, $code ) {
	
		$this->name    = $name;
		$this->code    = $code;
		
	}
	/**
	 * Renders a Widget based on the input parameters
	 * 
	 * @param $params Array
	 * @return $code  String
	 */
	public function render( &$params ) {
	
	}
	
	protected function extractVersion() {
	
	}
	
} //Widget: end class definition