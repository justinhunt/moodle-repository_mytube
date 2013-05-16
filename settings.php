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
 * YouTube TinyMCE Editor subplugin 
 * Crowdfunded by many cool people.
 *
 * @package   tinymce_youtube
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

// Section for setting tab visibility
$settings->add(new admin_setting_heading('visibletabsheading', '', get_string('visibletabsheading', 'tinymce_youtube')));
	
// Allow uploads
$settings->add(new admin_setting_configcheckbox('tinymce_youtube/allow_uploads',
                   new lang_string('allowuploads', 'tinymce_youtube'),
                   new lang_string('allowuploadsdetails', 'tinymce_youtube'), 1));
                   
// Allow webcam
$settings->add(new admin_setting_configcheckbox('tinymce_youtube/allow_webcam',
                   new lang_string('allowwebcam', 'tinymce_youtube'),
                   new lang_string('allowwebcamdetails', 'tinymce_youtube'), 1));

// Allow manual
$settings->add(new admin_setting_configcheckbox('tinymce_youtube/allow_manual',
                   new lang_string('allowmanual', 'tinymce_youtube'),
                   new lang_string('allowmanualdetails', 'tinymce_youtube'), 1));
				   
// Section for authentication keys and settings
$settings->add(new admin_setting_heading('keysauthheading', '', get_string('keysauthheading', 'tinymce_youtube')));

//The authentication type, master user or student by student
$authoptions = array('byuser' => new lang_string('byuser', 'tinymce_youtube'),
			'bymaster' => new lang_string('bymaster', 'tinymce_youtube'));
$settings->add(new admin_setting_configselect('tinymce_youtube/authtype', 
				new lang_string('authtype', 'tinymce_youtube'),  
				new lang_string('authtypedetails', 'tinymce_youtube'), 'bymaster', $authoptions));

// Developers Key			   
$settings->add(new admin_setting_configtext('tinymce_youtube/devkey',
                        new lang_string('youtubedevkey', 'tinymce_youtube'),
                        new lang_string('youtubedevkeydetails', 'tinymce_youtube'), '')); 

$settings->add(new admin_setting_configtext('tinymce_youtube/youtube_masteruser',
                        new lang_string('youtubemasteruser', 'tinymce_youtube'),
                        new lang_string('youtubemasteruserdetails', 'tinymce_youtube'), ''));
						
$settings->add(new admin_setting_configpasswordunmask('tinymce_youtube/youtube_masterpass',
                        new lang_string('youtubemasterpass', 'tinymce_youtube'),
                        new lang_string('youtubemasterpassdetails', 'tinymce_youtube'), ''));



$settings->add(new admin_setting_configtext('tinymce_youtube/youtube_clientid',
                        new lang_string('youtubeclientid', 'tinymce_youtube'),
                        new lang_string('youtubeclientiddetails', 'tinymce_youtube'), ''));
						
$settings->add(new admin_setting_configtext('tinymce_youtube/youtube_secret',
                        new lang_string('youtubesecret', 'tinymce_youtube'),
                        new lang_string('youtubesecretdetails', 'tinymce_youtube'), ''));


	// Access Settings
	$settings->add(new admin_setting_heading('evalcheckboxheading', '', get_string('evalcheckboxheading', 'tinymce_youtube')));

	//by roles or capabilities
   $accessoptions = array('0' => new lang_string('usecapabilities', 'tinymce_youtube'),
		'1' => new lang_string('usecheckboxes', 'tinymce_youtube'));
	
	$settings->add(new admin_setting_configselect('tinymce_youtube/role_eval',
			   get_string('roleeval', 'tinymce_youtube'),
			   get_string('roleevaldetails', 'tinymce_youtube'), 0,$accessoptions));
	  
	// Role settings
	$roleoptions = array('allow_guest' => new lang_string('allowguest', 'tinymce_youtube'),
					'allow_frontpage' => new lang_string('allowfrontpage', 'tinymce_youtube'),
					'allow_authuser' => new lang_string('allowauthuser', 'tinymce_youtube'),
					'allow_student' => new lang_string('allowstudent', 'tinymce_youtube'),
					'allow_noneditteacher' => new lang_string('allownoneditteacher', 'tinymce_youtube'),
					'allow_teacher' => new lang_string('allowteacher', 'tinymce_youtube'),
					'allow_manager' => new lang_string('allowmanager', 'tinymce_youtube'),
					'allow_coursecreator' => new lang_string('allowcoursecreator', 'tinymce_youtube'),
					'allow_admin' => new lang_string('allowadmin', 'tinymce_youtube'));
	$roleoptiondefaults = array('allow_admin' => 1,'allow_coursecreator' => 1,'allow_manager' => 1,'allow_teacher' => 1);
	$settings->add(new admin_setting_configmulticheckbox('tinymce_youtube/allowedroles',
						   get_string('allowedroles', 'tinymce_youtube'),
						   get_string('allowedrolesdetails', 'tinymce_youtube'), $roleoptiondefaults,$roleoptions));	
	
	// Video Default Settings
	$settings->add(new admin_setting_heading('videoinfoheading', '', get_string('videoinfoheading', 'tinymce_youtube')));
	$privacyoptions = array('unlisted' => new lang_string('unlisted', 'tinymce_youtube'),
		'public' => new lang_string('public', 'tinymce_youtube'),
		'private' => new lang_string('private', 'tinymce_youtube'));
	$settings->add(new admin_setting_configselect('tinymce_youtube/videoprivacy',
			   get_string('videoprivacy', 'tinymce_youtube'),
			   get_string('videoprivacydetails', 'tinymce_youtube'), 'unlisted',$privacyoptions));
			   
	//Category settings
	$categoryoptions = array('Education' => new lang_string('cat_education', 'tinymce_youtube'),
					'Animals' => new lang_string('cat_animals', 'tinymce_youtube'),
					'Autos' => new lang_string('cat_autos', 'tinymce_youtube'),
					'Comedy' => new lang_string('cat_comedy', 'tinymce_youtube'),
					'Film' => new lang_string('cat_film', 'tinymce_youtube'),
					'Games' => new lang_string('cat_games', 'tinymce_youtube'),
					'Howto' => new lang_string('cat_howto', 'tinymce_youtube'),
					'Music' => new lang_string('cat_music', 'tinymce_youtube'),
					'News' => new lang_string('cat_news', 'tinymce_youtube'),	
					'Nonprofit' => new lang_string('cat_nonprofit', 'tinymce_youtube'),
					'People' => new lang_string('cat_people', 'tinymce_youtube'),
					'Tech' => new lang_string('cat_tech', 'tinymce_youtube'),
					'Sports' => new lang_string('cat_sports', 'tinymce_youtube'),
					'Travel' => new lang_string('cat_travel', 'tinymce_youtube'));
					
	$settings->add(new admin_setting_configselect('tinymce_youtube/videocategory',
		   get_string('videocategory', 'tinymce_youtube'),
		   get_string('videocategorydetails', 'tinymce_youtube'), 'Education',$categoryoptions));
		   
	//Comment on YouTube Ok
	$settings->add(new admin_setting_configcheckbox('tinymce_youtube/allow_ytcomment',
                   new lang_string('allowytcomment', 'tinymce_youtube'),
                   new lang_string('allowytcommentdetails', 'tinymce_youtube'), 0));
	//Rate on YouTube OK			   
	$settings->add(new admin_setting_configcheckbox('tinymce_youtube/allow_ytrate',
                   new lang_string('allowytrate', 'tinymce_youtube'),
                   new lang_string('allowytratedetails', 'tinymce_youtube'), 0));
	//Video Respond on YouTube OK			   
	$settings->add(new admin_setting_configcheckbox('tinymce_youtube/allow_ytrespond',
                   new lang_string('allowytrespond', 'tinymce_youtube'),
                   new lang_string('allowytresponddetails', 'tinymce_youtube'), 0));

}
