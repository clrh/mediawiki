<?php
/**
 * @author Jean-Lou Dupont
 * @package SecureHTML
 * @version 2.1.1
 * @Id $Id: SecureHTML.body.php 776 2007-12-19 01:20:37Z jeanlou.dupont $
 */
//<source lang=php>
class SecureHTML
{
	// constants.
	const thisName = 'SecureHTML';
	const thisType = 'other';
	  
	static $enableExemptNamespaces = true;
	static $exemptNamespaces = array();

	// Parser function {{ #html}} related
	const openVar  = '{@{';
	const closeVar = '}@}';

	public static function addExemptNamespaces( $list )
	{
		if (!is_array( $list ))	
			$list = array( $list );
			
		self::$exemptNamespaces = array_merge( self::$exemptNamespaces, $list );
	}

	function __construct( )
	{
		self::$exemptNamespaces[] = NS_MEDIAWIKI;
		
		// default exempt namespaces from the BizzWiki platform.
		// won't affect installs of the extension outside the BizzWiki platform.
		if (defined('NS_BIZZWIKI'))   self::$exemptNamespaces[] = NS_BIZZWIKI;
		if (defined('NS_FILESYSTEM')) self::$exemptNamespaces[] = NS_FILESYSTEM;
	}
	/**
		This hook is required for adapting to 'parser cache' article saving
	 */
	public function hArticleSave( &$article, &$user, &$text, &$summary, $minor, $dontcare1, $dontcare2, &$flags )
	{ return $this->process( $article ); }
	/**
		 This hook is required when 'parser caching' functionality is not used.	
	 */
	public function hArticleViewHeader( &$article )
	{ return $this->process( $article ); }

	/**
		Attempt article processing with 'raw html tags'.
	 */	
	private function process( &$article )
	{
		if (!$this->canProcess( $article ) ) 
			return true;
				
		// Now that we know we are on a protected page,
		// enable raw html for the benefit of the 'parser cache' saving process
		global $wgRawHtml;
		$wgRawHtml = true;
		
		return true; // continue hook-chain.
	}
	/**
		Verify's article protection status.
	 */
	private function canProcess( &$obj )
	{
		if (!is_object( $obj ))
			return false; // paranoia
			
		if (!is_a( $obj, 'Article'))
			return false;

		$title = $obj->mTitle;		
		
		if (self::$enableExemptNamespaces)
		{
			$ns = $title->getNamespace();
			if ( !empty(self::$exemptNamespaces) )
				if ( in_array( $ns, self::$exemptNamespaces) )
					return true;	
		}
		
		// check protection status
		return $title->isProtected( 'edit' );
	}

	/**
	 * Support for the parser function {{ #html: page name [|optional parameters] }}
	 * Where 'page name' is the target page to include.
	 */
	public function mg_html( &$parser, $page_name /* optional params */ )
	{
		$params = func_get_args();
		array_shift( $params ); // get rid of $parser
		array_shift( $params );	// get rid of $page_name
		
		$text = null;
		$text = $this->getAndProcessPage( $page_name, $params, $text );
		
		// Let MediaWiki do the heavy lifting.
		return $text;
	}
	/**
	 * Secure version of '#html' parser function
	 * The page where the parser function is used does not need to be protected,
	 * only the target page_name must be.
	 */
	public function mg_shtml( &$parser, $page_name /* optional params */ )
	{
		$params = func_get_args();
		array_shift( $params ); // get rid of $parser
		array_shift( $params );	// get rid of $page_name
		
		$text = $this->getAndProcessPage( $page_name, $params );

		// prepare for the call to [[Extension:ParserFunctionsHelper]]
		// public function hParserFunctionsHelperSet( $key, &$value, &$index, &$anchor )		
		$anchor = null;
		$index = null;
		wfRunHooks( 'ParserFunctionsHelperSet',
					array( 'shtml', &$text, &$index, &$anchor ) );
		
		if ( $anchor === null)
			return '[http://www.mediawiki.org/wiki/Extension:ParserFunctionsHelper Extension:ParserFunctionHelper] missing.';
		
		return $anchor;		
	}
	/**
	 *
	 */
	protected function getAndProcessPage( &$page_name, &$params )
	{
		// get a title object from the page_name given
		$title = null;
		$result = $this->getAndCheckTitle( $page_name, $title );
		
		// make sure that the page in question is protected
		if ($result === false)
			return 'SecureHTML: '.wfMsg('badaccess');
			
		$text = $this->getPageText( $title );
		
		$processed_params = $this->processParams( $params );
		
		$this->replaceVariables( $text, $processed_params );

		return $text;
	}	 
	/**
	 * Verifies if the target page is protected for 'edit'
	 */
	protected function getAndCheckTitle( &$page_name, &$title )
	{
		$title = Title::newFromText( $page_name );
		if (!is_object( $title ))
			return false;
		
		return $title->isProtected( 'edit' );
	}
	/**
	 * Retrieves the latest revision content of a page.
	 */
	protected function getPageText( &$title )
	{
		// no... that's too bad; go the long way then.
		$rev = Revision::newFromTitle( $title );
		if (!is_object( $rev ))
			return null;

		return $rev->getText();
	}	
	/**
	 * The parameters will be coming in an array of the form:
	 * k1 = v1 , k2 = v2 etc.
	 */
	protected function processParams( &$params )
	{
		$result = array();
		
		if (empty( $params ))	
			return $result;
		
		foreach( $params as $index => &$e )
		{
			$bits = explode( '=', $e );
			$result[ $bits[0] ] = $bits[1];	
		}
		
		return $result;
	}	
	/**
	 * Replaces the variables in the target page.
	 * The variables are of the form {@{var_x}@}
	 */
	protected function replaceVariables( &$text, &$params )
	{
		// nothing to do?
		if (empty( $params ))
			return null;
		
		foreach( $params as $key => &$value )
		{
			$pattern = self::openVar.$key.self::closeVar;
			$text = str_replace( $pattern, $value, $text );
		}
		
		return true;
	}	
	
} // END CLASS DEFINITION
//</source>