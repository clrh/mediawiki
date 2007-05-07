<?php
/*
 * ExtensionClass.php
 * 
 * MediaWiki extension
 * @author: Jean-Lou Dupont (http://www.bluecortex.com)
 *
 * Purpose:  Provides a toolkit for easier Mediawiki
 *           extension development.
 *
 * FEATURES:
 * - 'singleton' implementation suited for extensions that require single instance
 * - 'magic word' helper functionality
 * - limited pollution of global namespace
 *
 * Tested Compatibility: MW 1.8.2 (PHP5), 1.9.3
 *
 * History:
 * v1.0		Initial availability
 * v1.01    Small enhancement in processArgList
 * v1.02    Corrected minor bug
 * v1.1     Added function 'checkPageEditRestriction'
 * v1.2     Added 'getArticle' function
 * ----     Moved to SVN management
 * v1.3     Added wgExtensionCredits updating upon Special:Version viewing
 * v1.4     Fixed broken singleton functionality
 * v1.5		Added automatic registration of hook functions based
 *          on the definition of an handler in the derived class
 *          (e.g. if handler 'hArticleSave' exists, then the appropriate
 *           'ArticleSave' hook is registered)
 * v1.51    Fixed '$passingStyle' bug (thanks to Joshua C. Lerner)
 * v1.6     Added 'updateCreditsDescription' helper method.
 * v1.7		Added 'depth' parameter support: more than 1 class depth can be created.
 *          Added 'setupTags' method (support for parser tags)
 *
 */
$wgExtensionCredits['other'][] = array( 
	'name'    => 'ExtensionClass',
	'version' => 'v1.7 $LastChangedRevision$',
	'author'  => 'Jean-Lou Dupont', 
	'url'     => 'http://www.bluecortex.com',
);

class ExtensionClass
{
	static $gObj; // singleton instance

// List up-to-date with MW 1.10 SVN 21828
static $hookList = array(
'ArticlePageDataBefore', 
'ArticlePageDataAfter', 
'ArticleAfterFetchContent',
'ArticleViewRedirect', 
'ArticleViewHeader',
'ArticlePurge',
'ArticleSave', 
'ArticleInsertComplete',
'ArticleSaveComplete',
'MarkPatrolled', 
'MarkPatrolledComplete', 
'WatchArticle', 
'WatchArticleComplete',
'UnwatchArticle', 
'UnwatchArticleComplete', 
'ArticleProtect', 
'ArticleProtectComplete',
'ArticleDelete', 
'ArticleDeleteComplete', 
'ArticleEditUpdatesDeleteFromRecentchanges',
'ArticleEditUpdateNewTalk',
'DisplayOldSubtitle',
'IsFileCacheable',
'CategoryPageView',
'FetchChangesList',
'DiffViewHeader',
'AlternateEdit', 
'EditFormPreloadText', 
'EditPage::attemptSave', 
'EditFilter', 
'EditPage::showEditForm:initial',
'EditPage::showEditForm:fields',
'SiteNoticeBefore',
'SiteNoticeAfter',
'FileUpload',
'BadImage', 
'MagicWordMagicWords', 
'MagicWordwgVariableIDs',
'MathAfterTexvc',
'MessagesPreLoad',
'LoadAllMessages',
'OutputPageParserOutput',
'OutputPageBeforeHTML',
'AjaxAddScript', 
'PageHistoryBeforeList',
'PageHistoryLineEnding',
'ParserClearState', 
'ParserBeforeStrip',
'ParserAfterStrip',
'ParserBeforeTidy',
'ParserAfterTidy',
'ParserBeforeStrip',
'ParserAfterStrip', 
'ParserBeforeStrip',
'ParserAfterStrip', 
'ParserBeforeInternalParse',
'InternalParseBeforeLinks', 
'ParserGetVariableValueVarCache',
'ParserGetVariableValueTs', 
'ParserGetVariableValueSwitch',
'IsTrustedProxy',
'wgQueryPages', 
'RawPageViewBeforeOutput', 
'RecentChange_save',
'SearchUpdate', 
'AuthPluginSetup', 
'LogPageValidTypes',
'LogPageLogName', 
'LogPageLogHeader', 
'LogPageActionText',
'SkinTemplateTabs', 
'BeforePageDisplay', 
'SkinTemplateOutputPageBeforeExec', 
'PersonalUrls', 
'SkinTemplatePreventOtherActiveTabs',
'SkinTemplateTabs', 
'SkinTemplateBuildContentActionUrlsAfterSpecialPage',
'SkinTemplateContentActions', 
'SkinTemplateBuildNavUrlsNav_urlsAfterPermalink',
'SkinTemplateSetupPageCss',
'BlockIp', 
'BlockIpComplete', 
'BookInformation', 
'SpecialContributionsBeforeMainOutput',
'EmailUser', 
'EmailUserComplete',
'SpecialMovepageAfterMove',
'SpecialMovepageAfterMove',
'SpecialPage_initList',
'SpecialPageExecuteBeforeHeader',
'SpecialPageExecuteBeforePage',
'SpecialPageExecuteAfterPage',
'PreferencesUserInformationPanel',
'SpecialSearchNogomatch',
'ArticleUndelete',
'UndeleteShowRevision',
'UploadForm:BeforeProcessing',
'UploadVerification',
'UploadComplete',
'UploadForm:initial',
'AddNewAccount',
'AbortNewAccount',
'UserLoginComplete',
'UserCreateForm',
'UserLoginForm',
'UserLogout',
'UserLogoutComplete',
'UserRights',
/*'SpecialVersionExtensionTypes',*/ // reserved special treatment
'UnwatchArticle',
'AutoAuthenticate', 
'GetFullURL',
'GetLocalURL',
'GetInternalURL',
'userCan',
'TitleMoveComplete',
'isValidPassword',
'UserToggles',
'GetBlockedStatus',
'PingLimiter',
'UserRetrieveNewTalks',
'UserClearNewTalkNotification',
'PageRenderingHash',
'EmailConfirmed',
'ArticleFromTitle',
'CustomEditor',
'UnknownAction',
/*'LanguageGetMagic', */ // reserved a special treatment in this class 
'LangugeGetSpecialPageAliases',
'MonoBookTemplateToolboxEnd',
'SkinTemplateSetupPageCss',
'SkinTemplatePreventOtherActiveTabs'
);

	var $className;
	
	var $paramPassingStyle;
	var $ext_mgwords;	
	
	// Parameter passing style.
	const mw_style = 1;
	const tk_style = 2;
	
	public static function &singleton( $mwlist=null ,$globalObjName=null, $passingStyle = self::mw_style, $depth = 1 )
	{
		// Let's first extract the callee's classname
		$trace = debug_backtrace();
		$cname = $trace[$depth]['class'];

		// If no globalObjName was given, create a unique one.
		if ($globalObjName === null)
			$globalObjName = substr(create_function('',''), 1 );
		
		// Since there can only be one extension with a given child class name,
		// Let's store the $globalObjName in a static array.
		if (!isset(self::$gObj[$cname]) )
			self::$gObj[$cname] = $globalObjName; 
				
		if ( !isset( $GLOBALS[self::$gObj[$cname]] ) )
			$GLOBALS[self::$gObj[$cname]] = new $cname( $mwlist, $passingStyle );
			
		return $GLOBALS[self::$gObj[$cname]];
	}
	public function ExtensionClass( $mgwords=null, $passingStyle = self::mw_style, $depth = 1 )
	/*
	 *  $mgwords: array of 'magic words' to subscribe to *if* required.
	 */
	{
		global $wgHooks;
			
		$this->paramPassingStyle = $passingStyle;
		
		// Let's first extract the callee's classname
		$trace = debug_backtrace();
		$this->className= $cname = $trace[$depth]['class'];
		// And let's retrieve the global object's name
		$n = self::$gObj[$cname];
		
		global $wgExtensionFunctions;
		$wgExtensionFunctions[] = create_function('',"global $".$n."; $".$n."->setup();");
		$this->ext_mgwords = $mgwords;		
		if (is_array($this->ext_mgwords) )
			$wgHooks['LanguageGetMagic'][] = array($this, 'getMagic');

		// v1.3 feature
		if ( in_array( 'hUpdateExtensionCredits', get_class_methods($this->className) ) )
			$wgHooks['SpecialVersionExtensionTypes'][] = array( &$this, 'hUpdateExtensionCredits' );				

		// v1.5 feature
		foreach (self::$hookList as $index => $hookName)
			if ( in_array( 'h'.$hookName, get_class_methods($this->className) ) )
				$wgHooks[$hookName][] = array( &$this, 'h'.$hookName );
	}
	public function getParamPassingStyle() { return $this->passingStyle; }
	public function setup()
	{
		if (is_array($this->ext_mgwords))
			$this->setupMagic();
	}
	// ================== MAGIC WORD HELPER FUNCTIONS ===========================
	public function getMagic( &$magicwords, $langCode )
	{
		foreach($this->ext_mgwords as $index => $key)
			$magicwords [$key] = array( 0, $key );
		return true;
	}
	public function setupMagic( )
	{
		global $wgParser;
		foreach($this->ext_mgwords as $index => $key)
			$wgParser->setFunctionHook( "$key", array( $this, "mg_$key" ) );
	}
	public function setupTags( $tagList )
	{
		global $wgParser;
		foreach($tagList as $index => $key)
			$wgParser->setHook( "$key", array( $this, "tag_$key" ) );
	}
	// ================== GENERAL PURPOSE HELPER FUNCTIONS ===========================
	public function processArgList( $list, $getridoffirstparam=false )
	/*
	 * The resulting list contains:
	 * - The parameters extracted by 'key=value' whereby (key => value) entries in the list
	 * - The parameters extracted by 'index' whereby ( index = > value) entries in the list
	 */
	{
		if ($getridoffirstparam)   
			array_shift( $list );
			
		// the parser sometimes includes a boggie
		// null parameter. get rid of it.
		if (count($list) >0 )
			if (empty( $list[count($list)-1] ))
				unset( $list[count($list)-1] );
		
		$result = array();
		foreach ($list as $index => $el )
		{
			$t = explode("=", $el);
			if (!isset($t[1])) 
				continue;
			$result[ "{$t[0]}" ] = $t[1];
			unset( $list[$index] );
		}
		if (empty($result)) 
			return $list;
		return array_merge( $result, $list );	
	}
	public function getParam( &$alist, $key, $index, $default )
	/*
	 *  Gets a parameter by 'key' if present
	 *  or fallback on getting the value by 'index' and
	 *  ultimately fallback on default if both previous attempts fail.
	 */
	{
		if (array_key_exists($key, $alist) )
			return $alist[$key];
		elseif (array_key_exists($index, $alist) )
			return $alist[$index];
		else
			return $default;
	}
	public function initParams( &$alist, &$templateElements )
	{
		foreach( $templateElements as $index => &$el )
			$alist[$el['key']] = $this->getParam( $alist, $el['key'], $el['index'], $el['default'] );
	}
	public function checkPageEditRestriction( &$title )
	// v1.1 feature
	// where $title is a Mediawiki Title class object instance
	{
		$proceed = false;
  
		$state = $title->getRestrictions('edit');
		foreach ($state as $index => $group )
			if ( $group == 'sysop' )
				$proceed = true;

		return $proceed;		
	} 
	public function getArticle( $article_title )
	{
		$title = Title::newFromText( $article_title );
		  
		// Can't load page if title is invalid.
		if ($title == null)	return null;
		$article = new Article($title);

		return $article;	
	}
	
	function isSysop( $user = null ) // v1.5 feature
	{
		if ($user == null)
		{
			global $wgUser;
			$user = $wgUser;
		}	
		return in_array( 'sysop', $user->getGroups() );
	}
	
	function updateCreditsDescription( &$text ) // v1.6 feature.
	{
		global $wgExtensionCredits;
	
		foreach ( $wgExtensionCredits[self::thisType] as $index => &$el )
			if ($el['name']==self::thisName)
				$el['description'].=$text;	
	}
} // end class definition.
?>