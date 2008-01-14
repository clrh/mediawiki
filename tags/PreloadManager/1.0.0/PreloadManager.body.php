<?php
/**
 * @author Jean-Lou Dupont
 * @package PreloadManager
 * @version $Id$
 */
//<source lang=php>
class PreloadManager
{
	const basePage = 'PreloadManager/';
	
	/**
	 * Preload hook.
	 */
	public function hEditFormPreloadText( &$text, &$title )
	{
		$text = $this->loadTemplate( $title );
		
		return true;
	}
	/**
	 * Loads a 'template' based on the title's namespace
	 */
	protected function loadTemplate( &$title )
	{
		$ns = $title->getNsText();
		
		$tpl_page = self::basePage.$ns;
		
		return $this->getPageContents( NS_MEDIAWIKI, $tpl_page );
	}
	/**
	 *
	 */
	protected function getPageContents( $ns, $page )
	{
		$title = Title::newFromText( $page, $ns );
		if (!is_object( $title ))		
			return null;
			
		$contents = null;

		$rev = Revision::newFromTitle( $title );
		if( is_object( $rev ) )
		    $contents = $rev->getText();		
			
		return $contents;		
	}	

} // end class
//</source>