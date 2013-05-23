MyTube repository plugin for Moodle 2.3

INTRODUCTION:
The MyTube repository allows users to record or upload video directly into YouTube, 
and to select videos directly from the authorised uses uploads for insertion into Moodle.

REQUIREMENTS:
*	Moodle 2.3.1+ (Build 20120816) or newer
*	Internet access
*	Multimedia plugins filter enabled (to turn YouTube links into YouTube players)
*	YouTube API keys (developer key, OAUTH2 client id, OAUTH2 secret). These are available free of charge from YouTube/Google. 

Installation
==============
The MyTube repository plugin is contained in the mytube folder. That folder should be placed in the following directory of a Moodle installation: 
[PATH TO MOODLE]/repository
Other folders in that directory will include, filesystem, flickr and wikimedia.

Once the folder is in place Moodle will be able to install the plugin. Login as the site administrator. 
Moodle should detect the MyTube repository plugin and present a page with plugin information and the option to proceed to install a new plugin. 
If Moodle does not automatically direct you to this page, you can go there from the Moodle menu:
Site Administration -> Notifications
 
Follow the prompts to install the plugin. 

Post Installation Settings
==========================
Before you can use the repository you will have to enable MyTube and create an instance. 

Go to: 
"Site Administration->Plugins->Repositories->Manage Repositories" 
and set the MyTube repository to "enabled and visible". 
Then a "Mytube" link will appear beneath "Manage Repositories" in the repositories menu. 
From that link create a repository instance. You can call it any name that you choose. 

You will need several keys to authorize access to the YouTube API.
A YouTube Developers Key can be obtained at https://code.google.com/apis/youtube/dashboard .

A Google OAUTH client id and OAUTH secret are also required. 
More information and explanation on how to get them is here:
http://docs.moodle.org/24/en/Google_OAuth_2.0_setup

There are two authentication methods possible, "master account" and "student account." 
If using master account authentication, you will need to enter a valid YouTube username and password.

The repository instance will then show in the file picker and you can use MyTube.

Justin Hunt 2013/05/23
poodllsupport@gmail.com







