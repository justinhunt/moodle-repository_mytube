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
 * This is the callback file, YouTube calls after login or video submission
 * 
 *
 * @package    repository_mytube
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');
require_once('youtubelib.php');
require_once($CFG->dirroot . '/repository/mytube/lib.php');
require_login();

$oauth2code   = required_param('oauth2code', PARAM_RAW);
$parentid   = required_param('returnparam', PARAM_RAW);

/// Headers to make it not cacheable
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

/// Wait as long as it takes for this script to finish
set_time_limit(0);

/*attempt to load youtube api */
// load the youtube submission plugin
	require_once($CFG->dirroot . '/repository/mytube/lib.php');
	//$ytplugin = repository_mytube::get("youtube");
	$ytplugin = repository::get_instance($parentid);
	$ytconfig = $ytplugin->get_ytconfig();
	$ytargs = Array('component'=>'repository_mytube','config'=>$ytconfig);
	
	$ytapi = new repository_mytube_youtube_api($ytargs);
	if(empty($ytapi)) {
		$loggedin = false;
	}else{
		$yt = $ytapi->init_youtube_api();
		$loggedin = $ytapi->is_yt_logged_in();
	}

//if we are logged in, show the upload iframe, if not do nothing
if($loggedin){
	$doOnClose = "window.opener.repository_mytube_initTabsAfterLogin();";
}else{
	$doOnClose = "";
}

//we may need to show messages to user if things don't work out
$strhttpsbug = get_string('youtubecallbackfailed', 'repository_mytube');
$strrefreshnonjs = get_string('youtubecallbackjsfailed', 'repository_mytube');

//close popup and return to dialog
$js =<<<EOD
<html>
<head>
    <script type="text/javascript">
    if(window.opener){
        {$doOnClose}
		//close self...
		window.close();
    } else {
        alert("{$strhttpsbug }");
    }
    </script>
</head>
<body>
    <noscript>
    {$strrefreshnonjs}
    </noscript>
</body>
</html>
EOD;

die($js);
