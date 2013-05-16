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

class repository_mytube extends repository {
	protected $component = 'repository_youtube';
	protected $ytconfig = null;

    /**
     * MyTube plugin constructor
     * @param int $repositoryid
     * @param object $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
    }

	public static function get_instance_option_names() {
    	return array('allow_uploads','allow_webcam','allow_manual','authtype','devkey',
		'youtube_masteruser','youtube_masterpass','youtube_clientid','youtube_secret',
		'videoprivacy','videocategory','allow_ytcomment','allow_ytrate','allow_ytrespond');
    }
    

    public static function instance_config_form($mform) {

		
// Section for setting tab visibility
//$settings->add(new admin_setting_heading('visibletabsheading', '', get_string('visibletabsheading', 'repository_mytube')));
	
		// Allow uploads
		$mform->addElement('checkbox','allow_uploads',
						   get_string('allowuploads', 'repository_mytube'));
		$mform->setDefault('allow_uploads', 1);
						   
		// Allow webcam
		$mform->addElement('checkbox','allow_webcam',
						   get_string('allowwebcam', 'repository_mytube'));
		$mform->setDefault('allow_webcam', 1);

		// Allow manual
		$mform->addElement('checkbox','allow_manual',
						   get_string('allowmanual', 'repository_mytube'));
		$mform->setDefault('allow_manual', 1);
						   
		// Section for authentication keys and settings
		//$settings->add(new admin_setting_heading('keysauthheading', '', get_string('keysauthheading', 'repository_mytube')));

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

		$mform->addElement('text', 'youtube_masteruser',
								get_string('youtubemasteruser', 'repository_mytube'));
		$mform->disabledIf('youtube_masteruser', 'authtype', 'eq', 'byuser');
								
		$mform->addElement('password', 'youtube_masterpass',
								get_string('youtubemasterpass', 'repository_mytube'));
		$mform->disabledIf('youtube_masterpass', 'authtype', 'eq', 'byuser');

		$mform->addElement('text', 'youtube_clientid',
								get_string('youtubeclientid', 'repository_mytube'));
		$mform->disabledIf('youtube_clientid', 'authtype', 'eq', 'bymaster');
								
		$mform->addElement('text', 'youtube_secret',
								get_string('youtubesecret', 'repository_mytube'));
		$mform->disabledIf('youtube_secret', 'authtype', 'eq', 'bymaster');
	
	
		// Video Default Settings
		//$settings->add(new admin_setting_heading('videoinfoheading', '', get_string('videoinfoheading', 'repository_mytube')));
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
	
    private function get_recorder() {
        global $CFG;
        $recorder = "this is a recorder";
        return $recorder;
    }
	
	public function get_upload_template(){
		$template = '';
		
		//init youtube api
		$this->ytconfig = $this->get_ytconfig();
		$ytargs = Array('component'=>$this->component,'config'=>$this->ytconfig);
		$yt = new repository_mytube_youtube_api($ytargs);
		
		//pass tabset html onto where JS can get at it
		/*
		$params['youtube_tabset'] = $yt->get_youtube_tabset();
		$params['uploader_html'] = get_string('uploadavideodetails', $this->component) 
					. $yt->get_uploader_iframe_html();
		$params['browselist_html'] = get_string('browsevideosdetails', $this->component) 
					. $yt->get_browser_iframe_html();
		$params['browselist_button_html'] = get_string('browsevideosdetails', $this->component) 
					. $yt->get_youtube_browselist_displaybutton();
					*/
		$template .= $yt->get_youtube_tabset();
		
		
		return $template;
	}

    public function old_get_upload_template() {
        $template = '
<div class="fp-upload-form mdl-align">
    <div class="fp-content-center">
        <form enctype="multipart/form-data" method="POST">
            <table >
                <tr class="{!}fp-recordaudio-recorder">
                    <td class="mdl-right"><label>recorder:</label>:</td>
                    <td class="mdl-left">'.$this->get_recorder().'</td></tr>
                </tr>
                <tr class="{!}fp-file">
                    <td class="mdl-right"></td>
                    <td class="mdl-left"><input type="file"/></td>
                </tr>
                <tr class="{!}fp-saveas">
                    <td class="mdl-right"></td>
                    <td class="mdl-left"><input type="text"/></td>
                </tr>
            </table>
        </form>
        <div><button class="{!}fp-upload-btn">UPLOAD</button></div>
    </div>
</div> ';
        return preg_replace('/\{\!\}/', '', $template);
    }

    public function check_login() {
        // Needs to return false so that the "login" form is displayed (print_login())
        return false;
    }

    public function global_search() {
        // Plugin doesn't support global search, since we don't have anything to search
        return false;
    }

    public function get_listing($path='', $page = '') {
        return array();
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
		$config->set('allow_manual',$this->options['allow_manual']);
		$config->set('allow_browse',$this->options['allow_browse']);
		$config->set('allow_webcam',$this->options['allow_webcam']);
		$config->set('allow_uploads',$this->options['allow_uploads']);
		$config->set('videoprivacy',$this->options['videoprivacy']);
		$config->set('videocategory',$this->options['videocategory']);
		$config->set('allow_ytcomment',$this->options['allow_ytcomment']);
		$config->set('allow_ytrate',$this->options['allow_ytrate']);
		$config->set('allow_ytrespond',$this->options['allow_ytrespond']);
	
		//eg /mod/assign/submission/youtube
		$config->set('modroot','/lib/editor/tinymce/plugins/youtube');
		//eg /mod/assign/view.php
		//$config->set('returnurl',$PAGE->url);
		$config->set('returnurl',$CFG->httpswwwroot . '/repository/mytube_callback.php');
		$config->set('shortdesc','MyTube Repository');
		
		return $config;
	}

 

    /**
     * Generate upload form
     */
    public function print_login($ajax = true) {
        $ret = array('nosearch'=>true, 'norefresh'=>true, 'nologin'=>true);
        $ret['upload'] = array('id'=>'repo-form');
        return $ret;
    }

    /**
     * supported return types
     * @return int
     */
    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }
}