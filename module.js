/**
 * Javascript for repository_youtube
 * 
 *
 * @copyright &copy; 2012 Justin Hunt
 * @author Justin Hunt
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package repository_mytube
 */

M.repository_mytube = {};

// Replace designated div with a YUI tab set
M.repository_mytube.init = function(Y,opts) {
    console.log('running init');
    mytube_repo_tabsetid=opts['tabsetid'];
    M.repository_mytube.browselist_html=opts['browselist_html'];
    M.repository_mytube.uploader_html=opts['uploader_html'];
        /*
	Y.use('tabview', function(Y) {
		var tabview = new Y.TabView({
			srcNode: '#' + opts['tabsetid']
		});

		tabview.render();
	});
        */
	
}


function loadtabs(tabsetid){
        console.log('running loadtabs');
	YUI().use('tabview', function(Y) {
		var tabView = new Y.TabView({srcNode: '#' + tabsetid}); 
		tabView.render();
	});
}

// Replace designated div with a YUI tab set
M.repository_mytube.loadyuitabs = function(Y, opts) {
        console.log('running loadyuitabs');
	YUI().use('tabview', function(Y) {
		var tabview = new Y.TabView({
			srcNode: '#' + opts['tabsetid']
		});
		tabView.render();
	});
}

// Replace youtube designated divs with youtube players
M.repository_mytube.loadytplayer = function(Y,opts) {

    //  function onYouTubeIframeAPIReady() {
	directLoadYTPlayer(opts['playerid'],
		opts['width'],
        opts['height'],      
        opts['videoid']);   
	  //}
}

M.repository_mytube.loadytrecorder = function(Y,opts) {

	directLoadYTRecorder(opts['recorderid'],
		opts['width']);   

}

		function repository_mytube_directLoadYTRecorder(recorderid,videoname,width) {
			videotitle = videoname;
			widget = new YT.UploadWidget(recorderid, {
			  width: width,
			  webcamOnly: true,
			  events: {
            'onUploadSuccess': repository_mytube_onUploadSuccess,
            'onProcessingComplete': repository_mytube_onProcessingComplete,
            'onApiReady': repository_mytube_onApiReady
			}
		});
		
		}
	 
         
         
// Show upload form and browse list
//this will be called after user has auth'ed with google in popup
function repository_mytube_initTabsAfterLogin() {
	repository_mytube_displayBrowseList();
	repository_mytube_displayUploadForm();
}

//show the upload form in upload tab
//only called from initTabsAfterLogin
function repository_mytube_displayUploadForm() {
	var uploadtab	= document.getElementById('tabupload');
	if(uploadtab){
		uploadtab.innerHTML = M.repository_mytube.uploader_html;
	}
}

//show the list of videos in browse list tab
//called from initTabsAfterLogin and onclick event of "browse list display" button
function repository_mytube_displayBrowseList() {
	var browsetab = document.getElementById('tablist');
	if(browsetab){
		browsetab.innerHTML = M.repository_mytube.browselist_html;
	}
}

function repository_mytube_onUploadSuccess(event) {
			document.getElementById('id_youtubeid').value=event.data.videoId;
	  }
function repository_mytube_onProcessingComplete(event) {
			//document.getElementById('id_youtubeid').value=event.data.videoId;
	  }
	  
function repository_mytube_onApiReady(event) {
		//var widget = event.target; //this might work, if global "widget" doesn't
			widget.setVideoTitle(videotitle);
			widget.setVideoDescription(videotitle);
			widget.setVideoPrivacy('unlisted'); 
	  }

  

	 
 
