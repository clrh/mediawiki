<?php
/**
 * @author Jean-Lou Dupont
 * @package ViewsourceRight
 * @version $Id$
 */
//<source lang=php>*/
class ViewsourceRight
{
	const thisName = 'ViewsourceRight';
	const thisType = 'other';  // must use this type in order to display useful info in Special:Version
	
	// Our class defines magic words: tell it to our helper class.
	public function __construct() {} 

	public function hSpecialVersionExtensionTypes( &$sp, &$extensionTypes )
	// setup of this hook occurs in 'ExtensionClass' base class.
	{
		global $wgExtensionCredits;

		if (class_exists('HNP'))
			$result = '<b>operational</b>';
		else
			$result = '<b>not operational: missing HNP extension </b>';
		
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if (isset($el['name']))		
				if ($el['name']==self::thisName)
					$el['description'].=$result;
				
		return true; // continue hook-chain.
	}
	
	public function hAlternateEdit( &$ep )
	{
		global $wgUser;
		
		$title =  $ep->mTitle;
		$new   = !$title->exists();		
		$save  =  $ep->save;
		
		if (!$new && !$save)
		{
			if ( ! $title->userCanEdit() ) 
			{
				$ns    = $title->getNamespace();
				$titre = $title->mDbkeyform;
				
				if (!$wgUser->isAllowed('viewsource'))
				{
					global $wgOut;
				
					$skin = $wgUser->getSkin();
					$wgOut->setPageTitle( wfMsg( 'viewsource' ) );
					$wgOut->setSubtitle( wfMsg( 'viewsourcefor', $skin->makeKnownLinkObj( $title ) ) );
					$wgOut->addWikiText( wfMsg( 'badaccess' ) );
					
					return false; // stop normal processing flow.
				}
			}
		}
		// if the user can't 'edit',
		// the normal processing flow will catch this.
		return true;		
	}

	public function hSkinTemplateTabs( &$st , &$content_actions )
	{
		$ns    = $st->mTitle->getNamespace();
		$titre = $st->mTitle->mDbkeyform;
		
		global $wgUser;
		global $action;

		// if the user can 'edit' the title, don't bother with 'viewsource' then.
		if ($st->mTitle->userCan('edit') ) return true;

		if ($wgUser->isAllowed( 'viewsource') )
		{
			$content_actions['viewsource'] = array(
				'class' => ($action == 'edit') ? 'selected' : false,
				'text' => wfMsg('viewsource'),
				'href' => $st->mTitle->getLocalUrl( $st->editUrlOptions() )
			);
		}
		else 
			unset( $content_actions['viewsource'] );

		return true;
	}
} // end class definition.
//</source>