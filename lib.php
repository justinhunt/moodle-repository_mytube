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
 * repository_mytube class
 *
 * @since 2.0
 * @package    repository
 * @subpackage mytube
 * @copyright  2013 Justin Hunt
 * @author     Juatin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->dirroot/repository/mytube/youtubelib.php");
require_once("$CFG->dirroot/repository/lib.php");


class repository_mytube extends repository {
	protected $component = 'repository_mytube';
	protected $ytconfig = null;

    /**
     * MyTube plugin constructor
     * @param int $repositoryid
     * @param object $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
		$this->initjs();
    }

	public static function get_instance_option_names() {
    	return array('allow_uploads','allow_webcam','allow_browse','authtype','devkey',
		'youtube_masteruser','youtube_masterpass','youtube_clientid','youtube_secret',
		'videoprivacy','videocategory','allow_ytcomment','allow_ytrate','allow_ytrespond');
    }
    

    public static function instance_config_form($mform) {

		
// Section for setting tab visibility
	
		// Allow uploads
		$mform->addElement('checkbox','allow_uploads',
						   get_string('allowuploads', 'repository_mytube'));
		$mform->setDefault('allow_uploads', 1);
						   
		// Allow webcam
		$mform->addElement('checkbox','allow_webcam',
						   get_string('allowwebcam', 'repository_mytube'));
		$mform->setDefault('allow_webcam', 1);

		// Allow manual
		$mform->addElement('checkbox','allow_browse',
						   get_string('allowbrowse', 'repository_mytube'));
		$mform->setDefault('allow_browse', 1);
						   
		// Section for authentication keys and settings
		//The authentication type, master user or student by student
		$authoptions = array('bymaster' => get_string('bymaster', 'repository_mytube'),
								'byuser' => get_string('byuser', 'repository_mytube'));
		$mform->addElement('select', 'authtype', 
						get_string('authtype', 'repository_mytube'),  
						$authoptions);
				
		// Developers Key			   
		$mform->addElement('text', 'devkey',
								get_string('youtubedevkey', 'repository_mytube'));
		$mform->addRule('devkey', get_string('required'), 'required', null, 'client');
		 $mform->setType('devkey', PARAM_RAW);

		$mform->addElement('text', 'youtube_masteruser',
								get_string('youtubemasteruser', 'repository_mytube'));
		//$mform->disabledIf('youtube_masteruser', 'authtype', 'eq', 'byuser');
		$mform->setType('youtube_masteruser', PARAM_RAW);
								
		$mform->addElement('password', 'youtube_masterpass',
								get_string('youtubemasterpass', 'repository_mytube'));
		//$mform->disabledIf('youtube_masterpass', 'authtype', 'eq', 'byuser');

		$mform->addElement('text', 'youtube_clientid',
								get_string('youtubeclientid', 'repository_mytube'));
		//$mform->disabledIf('youtube_clientid', 'authtype', 'eq', 'bymaster');
		$mform->setType('youtube_clientid', PARAM_RAW);
								
		$mform->addElement('text', 'youtube_secret',
								get_string('youtubesecret', 'repository_mytube'));
		//$mform->disabledIf('youtube_secret', 'authtype', 'eq', 'bymaster');
		$mform->setType('youtube_secret', PARAM_RAW);
	
	
		// Video Default Settings
		$privacyoptions = array('unlisted' => get_string('unlisted', 'repository_mytube'),
			'public' => get_string('public', 'repository_mytube'),
			'private' => get_string('private', 'repository_mytube'));
		
		$mform->addElement('select', 'videoprivacy',
					   get_string('videoprivacy', 'repository_mytube'),
					   $privacyoptions);
			   
		//Category settings
		$categoryoptions = array('Education' => get_string('cat_education', 'repository_mytube'),
					'Animals' => get_string('cat_animals', 'repository_mytube'),
					'Autos' => get_string('cat_autos', 'repository_mytube'),
					'Comedy' => get_string('cat_comedy', 'repository_mytube'),
					'Film' => get_string('cat_film', 'repository_mytube'),
					'Games' => get_string('cat_games', 'repository_mytube'),
					'Howto' => get_string('cat_howto', 'repository_mytube'),
					'Music' => get_string('cat_music', 'repository_mytube'),
					'News' => get_string('cat_news', 'repository_mytube'),	
					'Nonprofit' => get_string('cat_nonprofit', 'repository_mytube'),
					'People' => get_string('cat_people', 'repository_mytube'),
					'Tech' => get_string('cat_tech', 'repository_mytube'),
					'Sports' => get_string('cat_sports', 'repository_mytube'),
					'Travel' => get_string('cat_travel', 'repository_mytube'));
					
		$mform->addElement('select', 'videocategory',
				   get_string('videocategory', 'repository_mytube'),
				   $categoryoptions);
				   
		//Comment on YouTube Ok
		$mform->addElement('checkbox','allow_ytcomment',
						   get_string('allowytcomment', 'repository_mytube'));
		//Rate on YouTube OK			   
		$mform->addElement('checkbox','allow_ytrate',
						   get_string('allowytrate', 'repository_mytube'));
		//Video Respond on YouTube OK			   
		$mform->addElement('checkbox','allow_ytrespond',
						   get_string('allowytrespond', 'repository_mytube'));
		
    }


    public function initjs(){
        
            global $PAGE;  
           //set up our javascript for the YUI tabs
            $jsmodule = array(
                    'name'     => 'repository_mytube',
                    'fullpath' => '/repository/mytube/module.js',
                    'requires' => array('tabview')
            );

            
            //init youtube api
            $this->ytconfig = $this->get_ytconfig();
            $ytargs = Array('component'=>$this->component,'config'=>$this->ytconfig);
            $yt = new repository_mytube_youtube_api($ytargs);

            //configure our options array for the javascript
            $uploader_html = get_string('uploadavideodetails', $this->component) 
					. $yt->get_uploader_iframe_html();
            $browselist_html = get_string('browsevideosdetails', $this->component) 
					. $yt->get_browser_iframe_html();
        //    $browselist_button_html = get_string('browsevideosdetails', $this->component) 
	//				. $yt->get_youtube_browselist_displaybutton();
            $opts = array(
                    "tabsetid"=> $this->ytconfig->get('tabsetid'),
                    "browselist_html"=>$browselist_html,
                    "uploader_html"=>$uploader_html
            );

            //request the javascript on the page
            $PAGE->requires->js_init_call('M.repository_mytube.init', array($opts),false,$jsmodule);
    }
    

    
	public function get_youtube_form($yt=null){
		global $CFG;
		
		$form = '';

		if(!$yt){
			//init youtube api
            $this->ytconfig = $this->get_ytconfig();
            $ytargs = Array('component'=>$this->component,'config'=>$this->ytconfig);
            $yt = new repository_mytube_youtube_api($ytargs);
		}
		
		//$form .= "<input type=\"hidden\" name=\"id_repository_mytube_youtubeid\" id=\"id_youtubeid\"/>";
		$form .= $yt->get_youtube_tabset();
		$form.= "<iframe scrolling=\"no\" frameBorder=\"0\" src=\"{$CFG->wwwroot}/repository/mytube/triggerjs.html\" height=\"1\" width=\"1\"></iframe>";

		return $form;
	}

	public function get_ytconfig(){
		global $CFG, $USER;
		
		//create our video title, and replace any suspicous chars that might mess up javascript
		$videotitle = fullname($USER) . ' using MyTube Repository';
		$videotitle = str_replace('\'','-',$videotitle);
		$videotitle = str_replace('\"','-',$videotitle);
		
		//Add youtube tabset
		$config = new repository_mytube_youtube_settings();
		$config->set('videotitle',$videotitle);
		$config->set('tabsetid','youtubetabset_id');
		$config->set('devkey',$this->options['devkey']);
		$config->set('authtype',$this->options['authtype']);
		$config->set('masteruser',$this->options['youtube_masteruser']);
		$config->set('masterpass',$this->options['youtube_masterpass']);
		$config->set('clientid',$this->options['youtube_clientid']);
		$config->set('secret',$this->options['youtube_secret']);
		$config->set('allow_manual',$this->options['allow_browse']); //this is just for testing url input
		$config->set('allow_browse',$this->options['allow_browse']);
		$config->set('allow_webcam',$this->options['allow_webcam']);
		$config->set('allow_uploads',$this->options['allow_uploads']);
		$config->set('videoprivacy',$this->options['videoprivacy']);
		$config->set('videocategory',$this->options['videocategory']);
		$config->set('allow_ytcomment',$this->options['allow_ytcomment']);
		$config->set('allow_ytrate',$this->options['allow_ytrate']);
		$config->set('allow_ytrespond',$this->options['allow_ytrespond']);
		$config->set('parentid',$this->id);
	
		//eg /mod/assign/submission/youtube
		$config->set('modroot','/repository/mytube');
		//eg /mod/assign/view.php
		//$config->set('returnurl',$PAGE->url);
		$config->set('returnurl','/repository/mytube/youtube_callback.php');
		$config->set('returnparam',$this->id);
		$config->set('shortdesc','MyTube Repository');
		
		return $config;
	}


   public function check_login() {
        // Needs to return false so that the "login" form is displayed (print_login())
        return false;
    }

    public function global_search() {
        // Plugin doesn't support global search, since we don't have anything to search
        return false;
    }
	
	 ///
     // Return search results
     // @param string $search_text
     // @return array
     //
     //added $page=0 param for 2.3 compat justin 20120524
    public function search($filename, $page=0) {
        $this->keyword = $filename;
        $ret  = array();
        $ret['nologin'] = true;
		$ret['nosearch'] = true;
        $ret['norefresh'] = true;
        //echo $filename;
		$ret['list'] = $this->fetch_newvid_url($filename);
		
        return $ret;
    }

    public function get_listing($path='', $page = '') {
        return array();
    }
	
    
	/**
	* Hijack the login form with our youtube widget
	* @return array|bool
	*/
	public function print_login($ajax = true) {
		global $CFG;
        //Init our array
        $ret = array();
        
		//If we are using an iframe based repo
        $search = new stdClass();
        $search->type = 'hidden';
        $search->id   = 'id_repository_mytube_youtubeid' ;
        $search->name = 's';
        
        //had to use an iframe to house the form, some sort of templating was 
        //combining our fields inserted into the label with other stuff. total disaster
		$src = $CFG->httpswwwroot .  '/repository/mytube/recorder.php';
		$src .= '?parentid=' . $this->id;
        $search->label ="<iframe src='$src' width='540' height='380' frameborder='0'></iframe>";
		//$search->label = $this->get_youtube_form();

        $ret['login'] = array($search);
        $ret['login_btn_label'] = 'Next >>>';
        $ret['login_btn_action'] = 'search';
	
        return $ret;
		
    }
    
	private function fetch_newvid_url($video_id){
		global $CFG;
		//if it looks like a youtube id, continue, otherwise do not continue
		if($video_id && strlen($video_id)>7){
			$list[] = array(
				'shorttitle'=>get_string('ayoutubevideo', 'repository_mytube'),
				'thumbnail_title'=>get_string('ayoutubevideo', 'repository_mytube'),
                'title'=> get_string('ayoutubevideo', 'repository_mytube'). '.avi', // this is a hack so we accept this file by extension
                'thumbnail'=>"{$CFG->wwwroot}/repository/mytube/pix/icon.png",
                'thumbnail_width'=>120,
                'thumbnail_height'=>80,
                'size'=>'',
                'date'=>'',
                'source'=>'http://youtu.be/' .$video_id
            );
		}else{
			$list[]= array();
		}
			return $list;
	}
	

 /**
     * file types supported by youtube plugin
     * @return array
     */
    public function supported_filetypes() {
        return array('video');
    }

    /**
     * Youtube plugin only return external links
     * @return int
     */
    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }
}