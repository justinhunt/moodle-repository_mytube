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
 * This file is loaded in an iframe in the MyTube repository 
 *
 *
 * @package    repository_mytube
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('youtubelib.php');
require_once($CFG->dirroot.'/repository/lib.php');


require_login();

global $PAGE, $USER;

// we get the request parameters:
$parentid = optional_param('parentid', 0,PARAM_INT); // error code

// load the repository 
$repo = repository::get_instance($parentid);
if(empty($repo)) {
    die;
}


$PAGE->set_context(get_context_instance(CONTEXT_USER, $USER->id));
$PAGE->set_url($CFG->wwwroot.'/repository/mytube/recorder.php', 
        array('parentid' => $parentid));

echo "<link href=\"youtube.css\" rel=\"stylesheet\" type=\"text/css\" />";
echo "<script src=\"http://yui.yahooapis.com/3.9.0/build/yui/yui-min.js\"></script>"; 
echo "<script src=\"http://www.youtube.com/iframe_api\"></script>";
echo "<script type='text/javascript'>M={};</script>";
echo "<script src=\"{$CFG->wwwroot}/repository/mytube/module.js\"></script>"; 
echo "<script type='text/javascript'>M.repository_mytube.browselist_html=parent.M.repository_mytube.browselist_html;</script>";
echo "<script type='text/javascript'>M.repository_mytube.uploader_html=parent.M.repository_mytube.uploader_html;</script>";
echo "<div style=\"text-align: center;\">";
echo($repo->get_youtube_form());
?>
<script type="text/javascript">loadtabs('youtubetabset_id'); </script>

<?php
echo "</div>";