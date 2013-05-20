<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The YouTube and general logic for the YouTube Anywhere plugin for TinyMCE on Moodle
 * Crowdfunced by many cool people
 *
 * @package    repository_mytube
 * @copyright 2012 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
define('MYTUBE_COMPONENT','repository_mytube');
define('MYTUBE_APPID','moodle_repository_mytube');

$clientLibraryPath = $CFG->dirroot  . '/repository/mytube/ZendGdata-1.12.1/library';
$oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $clientLibraryPath);
require_once 'Zend/Loader.php';

//Added Justin 20120115 For OAUTH, 
require_once($CFG->libdir.'/googleapi.php');



/**
 * library class for youtube submission plugin extending submission plugin base class
 *
 * @package    _youtube
 * @copyright 2012 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_mytube_youtube_api {
	private $youtubeoauth = null;
	private $config= null;
	private $component= '';
	
	
	   /**
     * Contructor for Youtube Helper Class
     * Subclass can override construct to build its own $this->http
     *
     * @param array $args requires at least two keys, 
	 *						config  - config options
     *                    	component - component name
     *                    
     */
    function __construct($args) {
        if (!empty($args['config'])) {
            $this->config = $args['config'];
        }
		
		if (!empty($args['component'])) {
            $this->component= $args['component'];
        }
  
    }
	
	public function is_yt_logged_in(){
		if($this->youtubeoauth){
			return $this->youtubeoauth->is_logged_in();
		}else{
			return false;
		}
	}

	/**
     * Initialize and return Youtube api object
     * This is for uploading and is only called by the uploader.php in an iframe
	 * User should already be authenticated by the time they get here.
	 *
     * @return youtube api object 
     */
	public function init_youtube_api(){
		global $CFG,$USER;
		$devkey = $this->config->get('devkey');
		//looks like $devkey = "fghfghgsi56prjLHMrIUKTyZaG9KuWOmJ4ifBo3432432453jHnA3-8xFIBwPvxhgr4J6So08E76762767623232";
		Zend_Loader::loadClass('Zend_Gdata_YouTube');
		//get our httpclient
		//oauth2 for authorizing student by student
		//clientlogin for authorizing by masteruser
		switch ($this->config->get('authtype')){
			case 'byuser':
					$returnurl = new moodle_url($this->config->get('returnurl'));		
					$this->initialize_oauth($returnurl);
					$httpclient = $this->get_youtube_httpclient("oauth2");
					break;
			case 'bymaster':
			default:
					$httpclient = $this->get_youtube_httpclient("clientlogin");
		}
		
		// create our youtube object.
		$yt = new Zend_Gdata_YouTube($httpclient,MYTUBE_APPID,fullname($USER),$devkey);

		return $yt;

		
		
	}
	
	/**
     * Initialize and return Youtube api object
     *
     * @param string authentication method (clientlogin, authsub, oauth2)
     * @return youtube httpclient object 
     */
	public function get_youtube_httpclient($authmethod){
		
		switch ($authmethod){
			case "authsub":
				$httpclient=null;
				break;
			case "oauth2":
				//We have hijacked the AuthSub class, to use OAUTH2. I know, I know ...
				//But its the best way till API V3 is stable
				Zend_Loader::loadClass('Zend_Gdata_AuthSub');
				$httpclient = Zend_Gdata_AuthSub::getHttpClient($this->youtubeoauth->fetch_accesstoken());
				break;
			case "clientlogin":
			default:
				$username = $this->config->get('masteruser');
				$userpass = $this->config->get('masterpass');
			
				Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
				$authenticationURL= 'https://www.google.com/accounts/ClientLogin';
				$httpclient = Zend_Gdata_ClientLogin::getHttpClient(
					$username = $username,
					$password = $userpass,
					$service = 'youtube',
					$client = null,
					$source = 'Moodle ' . $this->config->get('shortdesc'), // a short string identifying your application
					$loginToken = null,
					$loginCaptcha = null,
					$authenticationURL);
				
					
		}
		return $httpclient;
	}
	
	/**
     * Get html that makes up the iframe for uploading
     *
     * @return the html for the iframe
     */
	public function get_uploader_iframe_html(){
		global $CFG;
		//get the default video title
		$videotitle = $this->get_video_title();
		
		//prepare the URL to our uploader page that will be loaded in an iframe
		$src = $CFG->httpswwwroot . $this->config->get('modroot') . '/uploader.php?showform=1';
		$src .= '&videotitle=' . urlencode($videotitle);
		$src .= '&parentid=' . $this->config->get('parentid');
		
		//Here we make up the HTML for the upload iframe. This will also be called by javascript after oauth authentication
		$uploaderhtml = "<iframe src='$src' width='500' height='110' frameborder='0'></iframe>";
		
		return $uploaderhtml;
	}
	
	
	/**
     * Get html that makes up the iframe for browsing videos
     *
     * @return the html for the iframe
     */
	public function get_browser_iframe_html(){
		global $CFG;
		//get the default video title
		$videotitle = $this->get_video_title();
		
		
		//prepare the URL to our uploader page that will be loaded in an iframe
		$src = $CFG->httpswwwroot . $this->config->get('modroot') . '/browser.php';
		$src .= '?videotitle=' . urlencode($videotitle);
		$src .= '&parentid=' . $this->config->get('parentid');
		
		//Here we make up the HTML for the browser iframe.
		$browserhtml = "<div class='scroller'>";
		$browserhtml .= "<iframe src='$src' width='540' height='350' frameborder='0'></iframe>";
		$browserhtml .= "</div>"; 
		
		return $browserhtml;
	}
	
	/**
     * Get teh default title of the recorded/upload video
     *
     * @return the video title
     */
	private function get_video_title(){
		//determine a video title
		$videotitle = $this->config->get('videotitle');
		if(strlen($videotitle >90)){$videotitle = substr($videotitle,90);}
		
		return $videotitle;
	}

    /**
     * Get YouTubeTabSet
     *
     * @return the html for the tabset
     */
    public function get_youtube_tabset() {
		 global $CFG, $USER, $PAGE;
		 
		//determine the video title
		$videotitle = $this->get_video_title();

		//UPLOADER And BROWSE Tabs
		//these have identical requirements in terms of config and authentication
		
		//we flag browseuploadok false if admin settings are empty(ie YT api keys etc)
		$browseuploadok=true;
		
		//get the header text for our upload and browse tabs
		$upload = get_string('uploadavideodetails', $this->component);
		$browse = get_string('browsevideosdetails', $this->component);
		
		//check if we have all the credentials we need
		$devkey = $this->config->get('devkey');
		$authtype = $this->config->get('authtype');
		$masteru = $this->config->get('masteruser');
		$masterp = $this->config->get('masterpass');;
		$clientid = $this->config->get('clientid');;
		$secret = $this->config->get('secret');
		
		
		//check for incomplete config
		$errortext = "";
		if(empty($devkey)){
				$browseuploadok=false;
				$errortext .= '<i>' . get_string('nodevkey', $this->component) . '</i>';
		}elseif($authtype=='byuser' && 
			(empty($clientid)||empty($secret))){
				$browseuploadok=false;
				$errortext .=  '<i>' . get_string('nooauth2', $this->component). '</i>';
		}elseif($authtype=='bymaster' && 
			(empty($masteru)||empty($masterp))){
				$browseuploadok=false;
				$errortext .=  '<i>' . get_string('nomaster', $this->component). '</i>';
		}
		
		//if config is incomplete, show message on the tab
		$upload .= $errortext;
		$browse .= $errortext;
		

		
		//If we need to log in to google(OAUTH2) we simply show a button with that URL
		//otherwise we load the uploader.php in an iframe .
		$loginbutton = "";
		if($authtype=='byuser' && $browseuploadok){

			$returnurl = new moodle_url($this->config->get('returnurl'));
			$returnurl->param('sesskey', sesskey());
	
			//get our youtube object
			$this->initialize_oauth($returnurl);
			
			if (!$this->youtubeoauth->is_logged_in()) {
					$loginurl = $this->youtubeoauth->get_login_url();
					$logintext =  get_string('logintext', $this->component);
					//we use a JS  button, but a simple link would be as good
					$loginbutton = "<input type='button' value='" . $logintext  
						. "' onclick='window.open(\"" . $loginurl. 
						"\",\"YouTube Auth\",\"location=0,status=0,width=500,height=300,scrollbars=yes\")' />";
					$upload .= $loginbutton;
			}else{
				$upload .= $this->get_uploader_iframe_html()  ;
			}
		}elseif($browseuploadok){
			$upload .= $this->get_uploader_iframe_html();
		}
		
		//Browse Tab
		/* browse and select URL submission */
		if($loginbutton !=""){
			$browse .= $loginbutton;
		}else{
			//$browse .= $this->get_browser_iframe_html();
			$browse .=  $this->get_youtube_browselist_displaybutton();
		}

		
		//WEBCAM Tab
		//get our youtube recorder
		/* */
		$webcam = get_string('recordavideodetails', $this->component);
		$webcam .= $this->fetch_youtube_recorder($videotitle);
		

		//ENTER URL Tab. This is useful for testing, but not useful for users.
		//To use comment here and just below, when setting mediadivs under "allow manual"
		/* manual URL submission */
		/*
		$manual = get_string('linkavideodetails', $this->component);
		$manual .= "<input type=\"text\" id=\"manualinputid\" size=\"65\" onchange=\"document.getElementById('youtubevidid').value=this.value;\"/>";
		*/
		
		

		//set up the html list elements that will get styled as tabs
		$medialist="";
		$mediadivs="";
		if($this->config->get('allow_uploads')){ 
				$medialist .= '<li><a href="#tabupload">' . get_string('uploadavideo', $this->component) . '</a></li>';
				$mediadivs .= '<div id="tabupload">' . $upload . '</div>';
		}
		
		if($this->config->get('allow_webcam')){ 
				$medialist .= '<li><a href="#tabwebcam">' . get_string('recordavideo', $this->component) . '</a></li>';
				$mediadivs .= '<div id="tabwebcam">' . $webcam . '</div>';
		}
		
		if($this->config->get('allow_manual')){ 
				/*
				$medialist .= '<li><a href="#tabmanual">' . get_string('linkavideo', $this->component) . '</a></li>';
				$mediadivs .= '<div id="tabmanual">' . $manual . '</div>';
				*/
				//The Browse and Insert Tabset
				$medialist .= '<li><a href="#tablist">' . get_string('browsevideos', $this->component) . '</a></li>';
				$mediadivs .= '<div id="tablist">' . $browse . '</div>';
		}
	
	
		//form the list
		$mediadata ='<div id="' . $this->config->get('tabsetid') . '"  class="yui3-skin-sam"><ul>';
		$mediadata .= $medialist; //$dummylist;			
		$mediadata .= '</ul><div>';
		$mediadata .= $mediadivs; //$dummydivs;
		$mediadata .= '</div></div>';
		
		return $mediadata;
		

    }
	
	//Here we init the auth, which will set up stuff for google
	  public function initialize_oauth($returnurl) {
		//the realm is always the same for YouTube api calls
		//and the clientid and secret are set in the admin settings for this plugin
        $clientid = $this->config->get('clientid');
        $secret = $this->config->get('secret');
		$realm = "http://gdata.youtube.com";
		//create and store our YouTube oauth client
        $this->youtubeoauth = new repository_mytube_oauth($clientid, $secret, $returnurl, $realm);
    }

	  /**
     * fetch html for youtube recorder
     *
     * @param string $videotitle The title to assign the video
     * @return string containing html to embed a recorder on a page
     */
    public function fetch_youtube_recorder($videotitle) {
		global $PAGE;
		
		$PAGE->requires->js(new moodle_url('http://www.youtube.com/iframe_api'));
		$recorderid = "youtuberecorder_id";
		$width=500;
		$ret="";
	
		//set our video privacy setting flag
		//slight possibility of javascript injection here, so sanitize it.
		switch($this->config->get('videoprivacy')){
			case 'public':	$videoprivacy = 'public'; break;
			case 'private': $videoprivacy = 'private'; break;
			case 'unlisted':
			default: $videoprivacy = 'unlisted';
		}
	
				
		//The JS init call does not work well in a tab, so we defer load of recorder 
		//to when the button is clicked
		//$PAGE->requires->js_init_call('M.assignsubmission_youtube.loadytrecorder', array($opts),false,$jsmodule);
		$ret .= "<input type='button' value='" . 
				get_string('clicktorecordvideo', $this->component) . 
			"' onclick='" . $this->component . "_directLoadYTRecorder(\"" . $recorderid. "\",\"" . $videotitle. "\",\"" . $videoprivacy. "\", " . $width . ");this.style.display=\"none\";' >";
		
		//
		$ret .= "<div id='$recorderid'></div>";
		
		return $ret;
	}
	
	public function get_youtube_browselist_displaybutton(){
		$button = "<button type='button' onclick='" . $this->component . "_displayBrowseList()' >Display List of Youtube Videos</button>";
		return $button;
	}
	
	  /**
     * fetch html for list of videos to choose from
     *
     * @param string $titlestub of videos to fetch
     * @return string containing html to embed a recorder on a page
     */
    public function fetch_youtube_browselist($yt = null) {
		
		
		$ret = "";
		$lis ="";

		$entryarray =  Array();
	  // set the version to 2 to receive a version 2 feed of entries
	  if(!$yt){
	  	$yt = $this->init_youtube_api();
	  }
	  $yt->setMajorProtocolVersion(2);
	  //This video feed, defaults to the currently logged in user
	  $vidfeed= "https://gdata.youtube.com/feeds/api/users/default/uploads";
	  $videoFeed = $yt->getVideoFeed($vidfeed);
	  
	  //loop through the videos and build our return data
		$count = 0;	
		foreach ($videoFeed as $videoEntry) {
			$title  = $videoEntry->getVideoTitle();
	
			//if we are authenticating by master user, ensure that only
			//this users videos appear in the list
			if($this->config->get('authtype') == 'bymaster'){
				if($title != $this->config->get('videotitle')){
					continue;
				}
			}
			//Prepare the output
			$ent = 'Video ' . $title . "|";
			$ent .= 'ID ' . $videoEntry->getVideoId();
			$entryarray[]=$ent;
			$vid= $videoEntry->getVideoId() ;
			//the recorded date is unreliable, and uploaded not available
			//so we make do with the updated date
			$recdate = $videoEntry->getUpdated();
			//The date YouTube returns is on what?? format ..
			//So we just massage it as best we can
			$showdate = str_replace('T',' ', $recdate);
			$showdate = str_replace('.000Z',' ', $showdate);

			
			$length = $videoEntry->getVideoDuration();
			$showlength = gmdate("i:s", $length);

			
			$thumburls =$videoEntry->getVideoThumbnails();
			$thumburl = $thumburls[0]['url'];
			
			$item = "<div><table class='repository_mytube_result_row' width='500'><tr><td class='repository_mytube_result_thumbcell'><img src='". $thumburl 
					. "' height='50'/></td><td class='repository_mytube_result_infocell'><strong>$title</strong></br>Length: $showlength Date: $showdate" 
					. "<br /><button type='button' onclick='window.parent.repository_mytube_insertYoutubeLink(\"$vid\")' class='repository_mytube_result_insert'>INSERT</button>"
					. "</td></tr></table> </div><hr />";			
				
			$lis .= $item;					
			$count++;

  		}

		return $lis;
}
	
	public function fetch_youtube_uploadform($yt,$videotitle){
		global $CFG, $USER;
		
		// create a new VideoEntry object
		$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();

		//Set the title and description
		$myVideoEntry->setVideoTitle($videotitle);
		$myVideoEntry->setVideoDescription($videotitle);
		
		//Set the category
		// The category must be a valid YouTube category!
		$myVideoEntry->setVideoCategory($this->config->get('videocategory'));
		//$myVideoEntry->setVideoCategory('Education');
		
		//Set the videos privacy setting as per the user settings
		//unlisted is a special case "public"(default) + no listability
		switch ($this->config->get('videoprivacy')){
			case 'private': $myVideoEntry->setVideoPrivate();
							$listed_per = 'denied';
							break;
			case 'public': $myVideoEntry->setVideoPublic();
							$listed_per = 'allowed';
							break;
			case 'unlisted':
			default: 
				//Set the videos commentability
				$listed_per = 'denied';
				
		}
		
		//Set the video will show up in searches
		$listed_ext = new Zend_Gdata_App_Extension_Element( 'yt:accessControl', 'yt',
										'http://gdata.youtube.com/schemas/2007', '' );
		$listed_ext->setExtensionAttributes(array(
			array('namespaceUri' => '', 'name' => 'action', 'value' => 'list'),
			array('namespaceUri' => '', 'name' => 'permission', 'value' => $listed_per)
		));
		
		//Set if the video can be commented on at YouTube
		if($this->config->get('allow_ytcomment')){
			$comment_per = 'allowed';
		}else{
			$comment_per = 'denied';
		}
		$comment_ext = new Zend_Gdata_App_Extension_Element( 'yt:accessControl', 'yt',
												'http://gdata.youtube.com/schemas/2007', '' );
		$comment_ext->setExtensionAttributes(array(
					array('namespaceUri' => '', 'name' => 'action', 'value' => 'comment'),
					array('namespaceUri' => '', 'name' => 'permission', 'value' => $comment_per)
				));
				
		//Set if the video can be rated on YouTube
		if($this->config->get('allow_ytrate')){
			$rate_per = 'allowed';
		}else{
			$rate_per = 'denied';
		}
		$rate_ext = new Zend_Gdata_App_Extension_Element( 'yt:accessControl', 'yt',
												'http://gdata.youtube.com/schemas/2007', '' );
		$rate_ext->setExtensionAttributes(array(
					array('namespaceUri' => '', 'name' => 'action', 'value' => 'rate'),
					array('namespaceUri' => '', 'name' => 'permission', 'value' => $rate_per)
				));
		
		//Set the video can be responded to by video on YouTube
		if($this->config->get('allow_ytrespond')){
			$respond_per = 'allowed';
		}else{
			$respond_per = 'denied';
		}
		$respond_ext = new Zend_Gdata_App_Extension_Element( 'yt:accessControl', 'yt',
												'http://gdata.youtube.com/schemas/2007', '' );
		$respond_ext->setExtensionAttributes(array(
					array('namespaceUri' => '', 'name' => 'action', 'value' => 'videoRespond'),
					array('namespaceUri' => '', 'name' => 'permission', 'value' => $respond_per)
				));
		
	
		//commit the various extension settings to the myvideoentry object
		$myVideoEntry->setExtensionElements(array($listed_ext,$comment_ext,$rate_ext,$respond_ext));
		

		// Set keywords. This must be a comma-separated string
		// Individual keywords cannot contain whitespace
		// We are not doing this, but it would be possible
		//$myVideoEntry->SetVideoTags('cars, funny');

		//data is all set, so we get our upload token from google
		$tokenHandlerUrl = 'http://gdata.youtube.com/action/GetUploadToken';
		$tokenArray = $yt->getFormUploadToken($myVideoEntry, $tokenHandlerUrl);
		$tokenValue = $tokenArray['token'];
		$postUrl = $tokenArray['url'];
		
		//Set the URL YouTube should redirect user to after upload
		//This will will be the same as the uploader form, ie uploader.php, and shown in same iframe
		$nextUrl =  $CFG->httpswwwroot . $this->config->get('modroot') . '/uploader.php';

		// Now that we have the token, we build the form
		$form = '<form action="'. $postUrl .'?nexturl='. $nextUrl .
        '" method="post" enctype="multipart/form-data">'. 
        '<input name="file" type="file"/>'. 
        '<input name="token" type="hidden" value="'. $tokenValue .'"/>'.
        '<input value="Upload Video File" type="submit" onclick="document.getElementById(\'id_uploadanim\').style.display=\'block\';" />'. 
        '</form>';
        
        // We tag on a hidden uploading icon. YouTube gives us no progress events, sigh.
        // So its the best we can do to show an animated gif.
        // But if it fails, user will wait forever.
        $form .= '<img id="id_uploadanim" style="display: none;margin-left: auto;margin-right: auto;" src="' . $CFG->httpswwwroot . $this->config->get('modroot') . '/pix/uploading.gif"/>';
		
		return $form;
	
	}//end of function fetch_youtube_uploadform


}//end of class YouTube API

class repository_mytube_youtube_settings {

	private $config= Array();
	
	public function get($key){
		return $this->config[$key];
	}

	public function set($key, $value){
		$this->config[$key] = $value;
	}
	


}

/**
 * OAuth 2.0 client for Youtube
 *
 * @package   repository_mytube
 * @copyright 2013 Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_mytube_oauth extends google_oauth {
	
	public function fetch_accesstoken(){
		//This should work, but doesn't. Why?
		//return $this->accesstoken->token;
		
		 // We have a token so we are logged in.
		 $at = $this->get_stored_token();
        if (isset($at->token)) {
            return $at->token;
        }else{
			return false;
		}
	}

}