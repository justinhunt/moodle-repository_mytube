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
	mytube_repo_tabsetid=opts['tabsetid'];
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
	YUI().use('tabview', function(Y) {
		var tabView = new Y.TabView({srcNode: '#' + mytube_repo_tabsetid}); 
		tabView.render();
	});
}

// Replace designated div with a YUI tab set
M.repository_mytube.loadyuitabs = function(tabsetid) {
	YUI().use('tabview', function(Y) {
		var tabView = new Y.TabView({srcNode:tabsetid}); 
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

		function directLoadYTRecorder(recorderid,videoname,width) {
			videotitle = videoname;
			widget = new YT.UploadWidget(recorderid, {
			  width: width,
			  webcamOnly: true,
			  events: {
            'onUploadSuccess': onUploadSuccess,
            'onProcessingComplete': onProcessingComplete,
            'onApiReady': onApiReady
			}
		});
		
		}
	 
	    function directLoadYTPlayer(playerid,width,height,videoid){
			new YT.Player(playerid, {
			width: width,
			height: height,      
			videoId: videoid,
			events: {
            'onReady': onYTPlayerReady,
            'onStateChange': onYTPlayerStateChange		
          }
        });
		
		}

	   function onYTPlayerReady(event) {
			//do something, eg event.target.playVideo();
	  }
	    function onYTPlayerStateChange(event) {
			//do something, eg event.target.playVideo();
	  }
	  
	     function onUploadSuccess(event) {
			document.getElementById('id_youtubeid').value=event.data.videoId;
	  }
	    function onProcessingComplete(event) {
			//document.getElementById('id_youtubeid').value=event.data.videoId;
	  }
	  
	   function onApiReady(event) {
			//var widget = event.target; //this might work, if global "widget" doesn't
			widget.setVideoTitle(videotitle);
			widget.setVideoDescription(videotitle);
			widget.setVideoPrivacy('unlisted'); 
	  }

  

	 
 
