REQUIREMENTS
- PHP and MYSQL
- PHP Extensions: gettext, curl, gd, imagick, mysqli

SERVER SETTINGS
Adjust PHP settings to allow uploading large files:
- max_execution_time: max execution time of scripts (Suggested: > 300)
- max_input_time: max execution time of upload (Suggested: > 300)
- memory_limit: not equal to post_max_size or upload_max_filesize (Suggested: > 512M)
- post_max_size: the maximum size of the file you want to upload
- upload_max_filesize: the maximum size of the file you want to upload

INSTALLATION
1) unzip simple_virtual_tour.zip
2) copy all files and directories into your hosting server root directory or subdirectory
3) access to http://yourserver/directory/install/start.php
4) follow the wizard and complete the installation

UPDATE
0) Take backup of files and database of the previous version
1) Go to the update menu
2) Click UPGRADE NOW!
or
1) Unzip the new version of simple_virtual_tour.zip
2) Upload new versions of files by replacing them in the directory of your hosting server being careful not to delete existing contents
3) Logout from backend and re-login
N.B. no need to run the installer again

Changelog
6.3
- added PYG, THB currencies
- added Korean, Thai languages
- added Storage Quota to Plans to limit the upload total size of all user content
- added the ability to send email notifications when a form or lead request is used
- added USDZ file support for 3d objects
- added shortcuts in markers for faster addition
- added notification system for administrators
- added the ability to set a room to be displayed by default when the floor plan is changed
- added layer filter in the 3d view editor in the backend
- added the ability of not displaying the pointer over a room in the 3d view
- added the ability of not displaying a cube face of a room in the 3d view
- added the ability to set dragging on or off when device orientation is on
- added the ability to set the view as a list or grid on the lists of tours, rooms and maps
- added the ability to zoom the product images
- added user filter on tour list for administrators
- added in the settings for administrators the ability to set a tour as a template for creating new ones
- autorotation disabled when a poi is open
- optimized loading of 3D View
- fixed an issue with hfov on mobile
- fixed compatibility of import tour on some systems
- fixed an issue that prevented from inserting some rooms to the map
- fixed an issue of the menu items not hidden when not selected in the plans
- removed the ability to add products in POIs if the shop is disabled in plans
- fixed an issue that prevented video with background removal to be displayed on mobile
- fixed an issue that prevented editing the 3d view when a room was deleted
6.2
- added support for the 3d view in the viewer and introduced an editor in the backend to create the 3d view (known as dollhouse)
- added import/export tours functionality to backend (for administrators)
- added an option to POIs to auto close them after a defined time
- added an option to POIs to order them in Z Order when overlapping
- added POI type Embedded Video (with background removal)
- added a setting to the 3d models to be displayed in AR and placed on the floor or wall
- added the ability to show or hide certain menu items based on the plan
- added the ability to insert Custom Html code inside the tours
- added the ability to view and customize the contextual box of the right mouse button
- added the ability to use a recorded video as presentation
- added the ability to view hidden markers when approaching with the mouse when click anywhere is enabled
- added the ability to set sharing on the tour link or the exact position and view you are in before pressing the share button
- while a POI/Marker is dragged, the move controls window is hidden
- fixed some showcase display issues
- fixed an issue of products not included in the exported tour
- fixed an issue during the update
- fixed an issue in the display of the info modal
- fixed an issue that caused the Editor UI to not display with some characters in the room name
- fixed an issue that in vr mode does not display the markers correctly
- fixed an issue that prevented the POI "selection area + switch panorama" from working correctly
- fixed an issue of the save button on the editor UI that remains disabled when saving presets
- fixed an issue with click anywhere feature
- fixed an issue with resetting the statistics
6.1.1
- added TJS, ARS currencies
- fixed automatic orientation on Android
- fixed a display problem of the viewer when the font was changed
- fixed an issue that not saving switch panorama POI
- increased max hfov to 140
- added some animations to the viewer controls
- removed uppercase for annotation title
- added vertical align center on POI embed text
- multires check requirements moved to settings
6.1
- added paypal payment method for plans (only extended license)
- added the ability to view online visitors directly in the tour, what room they are in and what they are looking at
- added the "Video Stream" room type which allows you to add an hls url to broadcast live panoramas
- added the "Lottie" room type which allows you to add json lottie animation file as panorama
- added the ability to use multi-resolution creation tool on an external server if local requirements are not met
- added "Editor UI" as permission for editors
- added presets to Editor UI
- moved font picker inside Editor UI
- added Editor UI shortcut into tours list
- added the ability to edit Room's List menu colors into Editor UI
- restyled the Room's List menu
- added the ability to hide and put meetings in full screen
- added shortcut in dashboard tours to access their stats
- added an option to maps to set the default view, streets or satellite
- added default markers lookat setting
- added info button to floorplans to open a link in a new window or modal
- added timeout in second to skip video advertisements
- added CZK currency
- added Tajik language
- fixed a conversion problem for the RWF currency
- fixed an error on saving tours
- fixed an issue that prevented tours on mobile when sharing was disabled
- fixed an issue that prevented font to be saved into Editor UI
- fixed an issue that prevented the room logo from being deleted
- fixed some bugs into Editor UI
- hided "presented by" if author is not present
6.0.3
- fixed room's menu icon not viewing in editor
- fixed the automatic opening of some POIs that didn't work
- fixed transition effects not displaying in some browsers
- fixed autoclose items that not closing sometime
- fixed an issue where embedded content was not positioned correctly on panorama videos
- fixed am issue deleting product images
- fixed issue on panorama video sound
- added Arabic language
- added unique statistics
- added video 360 as plan's permission
- added the ability to change some icons into Editor UI
- added custom button to Editor UI customizable with html content
- moved main form settings to Editor UI
- moved default markers/pois style to Editor UI and added preview
- now you can also apply default styles directly from a single marker/poi editing window
- the edit box in markers/pois opens automatically when minimized and tabs are clicked
- repositioned the share bar on the viewer
6.0.2
- fixed an issue with tour creation wizard
- fixed some editor ui issues
- prevented negative number on "skippable after" setting in advertisements
- fixed drag map/floorplan points on mobile
- fixed an issue with invalid image coordinates that didn't allow adding the point on the map
- fixed nav control position and size
- fixed a bug on autorotate when flyin is enabled
- fixed play button not visible on some browser when auto start is disabled
- fixed floorplan position and menu overlapping
- fixed share buttons overlapping with the room's slider when open
6.0.1
- fixed an issue that didn't allow opening the info and landing editor
6.0
- added a visual Editor UI into backend
- restyled the viewer and the loading
- added POI type product
- added Snipcart Integration to sell products directly inside the tour
- added POIs/Markers animations
- added POI view type Box (hover)
- added tour creation wizard
- added TURN/STUN server settings for live session
- added show/hide passwords on login/register pages
- added _parent and _top as targets to external link POIs
- added video and embedded link into advertisements
- added the possibility to edit "Rooms List" directly on Rooms section
- added a shortcut to the user's page in the tour list for administrators
- added RWF and IDR currencies
- added Czech, Filipino, Persian and Kinyarwanda languages
- added the ability for administrators to export users
- added preview shortcut in the markers/pois sections
- fixed lookat option in markers not display correctly after changed it
- fixed a drag of library elements issue on safari browser
- fixed disappeared upload button on POI type video
- fixed an issue that caused the room to be inadvertently selected while scrolling the slider
- fixed a bug in automatic presentation
- fixed an issue when dragging the view was inadvertently clicked on the POI
- fixed position of embedded POI and Markers
- now the pois embedded remain visible during the presentation
- where possible url redirection is avoided when viewing external tours
- now you can enter title only or description only in room annotations
- improved security of the backend
- optimized performance and loading of the viewer
- moved the close button of the marker editing window / then next to the minimize button
- the exported tour file now has the tour name as the file name
- disabled download button for external tours
- automatically checked the option 'override initial position' when moving the view of the room
5.9
- added customizable items on backend's footer
- added Romanian language
- added Polish (PLN) currency
- added the possibility to directly upload custom icons in the media library popup
- added title and description to customize password protected tours
- added a prompt if device orientation not start automatically
- added the possibility to upload a logo for each rooms displayed instead of the room's name
- added the possibility to insert images and links into welcome message
- added the possibility to toggle 3d transform on embedded images and videos
- added the possibility to modify the interaction with the virtual tours by modifying the panning speed and the friction
- added an option to markers to control the movement of the view when clicked on them
- added a minimize button to hide the editing popup of markers and pois
- added border and background colors to POI embed type text
- reversed the order of media library files, newer first
- room's list on viewer is now responsive
- moved the tour creation form directly to the page instead of the modal
- fixed scroll on some pois / info box
- fixed a bug on some servers that shows invalid link on showcases
- fixed an issue that prevented the room from being edited when the French language was selected
- fixed a problem with nav control not being hided when in web vr mode
- fixed a bug that breaks some tour on mobile
- fixed a problem of positioning of the embedded pois/markers that moved out of position when the meeting was opened
- fixed a bug with poi embed text
- fixed an issue that caused the play button not to be clicked on the embedded video
5.8.1
- added the ability to update the application automatically (only for administrators)
- fixed VND price format
- fixed download tour
- use small logo as favicon when present
5.8
- added POI type "Object 3d" for showing GLB/GLTF models
- added POI style "embedded link"
- added POI style "embedded text"
- added POI style "selection area"
- added Marker style "selection area"
- added the ability to upload lottie files as icons
- added POI type "Lottie"
- added Swedish language
- added VND currency
- added helper cursor when adding rooms pointer to maps
- added presets for room's positions
- added an option to autoplay audio without the popup
- added the ability to set background audio volume when play room and poi audio
- added the ability to set device orientation automatically enabled
- added the ability to renames tour / rooms / maps directly in the list view
- added quality viewer to virtual tour performance settings
- added disk space usage to statistics and users
- added the ability to choose items to duplicate for tours and rooms
- added switches in virtual tours to automatically close menus and floorplan when clicking on items within them
- added the ability to change markers and pois content/style type
- added the ability to choose what apply into default markers/pois styles
. added small logo into backend settings
- added author and counter of total accesses to the tours in the showcase
- modifying the way to position the markers and pois, now you have to drag them
- automatically sets latitude / longitude on the map if GPS data is present in the image
- updated API to accept also coordinates
- fixed language dates in statistics
- fixed a webvr problem on some phones
- fixed a bug on generating multi-resolution
- fixed select not working properly on safari
- fixed background song with spaces restart after changing rooms
- increased name for audio files
- optimized icon's touch on mobile
- optimized backend performance
- reduced memory usage in the viewer
5.7.1
- fixed a bug on download tour
- fixed a bug that prevents save some old showcases
5.7
- added Polish language
- added the ability to download tours
- added download tours as permission for plans
- floorplan's maps now goes fullscreen when enlarged
- added an option to not show the floor plan minimap, but only the enlarged version
- added width setting to maps (for desktop and mobile)
- added some effects to rooms (snow,rain,fog,fireworks,confetti,sparkle)
- custom font applied also on login/register pages
- added custom css into showcases
- added search in the list of tours assigned to the showcase/advertisement
- added date to forms/leads collected data
- improved markers/pois editing section
- added date as form field type
- added embedded video with transparency POI
- added the ability to duplicate POIs
- added auto rotation toggle into the viewer
- added floating navigation control
- added music library
- added media and music library as permission for editors
- added to administrators the possibility to manage public libraries for icons, media and musics
- divided into tabs some sections of the backend
- updated demo sample data
- added compatibility to set Google Street View as external URL
- fixed a bug that prevents to preload duplicated rooms
- fixed a bug that prevents upload bulk maps
- fixed overlapping icons id editing map
5.6.1
- added slideshow embed POI
- added checkbox and select type to poi form fields
- speeded up the change of tours in the showcase
- fixed video embedded with some devices
- added close button to floor maps
- fixed a bug that prevents display thumbs in media library
5.6
- added "click anywhere" and "hide markers" settings in virtual tours that allow you to click near markers (even if not visible) to go to the corresponding room
- added POI to embed images and videos to rooms
- added the ability to choose POI mode view: modal or box
- added the ability to change the fonts for the backend and for each virtual tour
- added an option to virtual tours to enable or not the preload of the panoramas
- added an option to regenerate all panoramas after changing compress and width settings
- added HFOV mobile ratio to set a wider or narrower view when viewing on small screens
- added setting to virtual tours to customize initial loading and the ability to put a video as a background
- added an API sample code to publish link section
- added manual expiration date to users
- added the ability to auto open info box and gallery
- added checkbox and select type to form fields
- added reply message to forms submissions
- added the ability to choose to show video or only audio in live sessions
- added code highlighter in poi type html
- added media library to select previously loaded or existing content on some pois (images and videos)
- added preview to poi's contents
- added button to take a screenshot of the current view in room editing
- applied the map settings also where it is displayed in the backend as well as in the viewer
- removed toggle effects on north tab in edit room
5.5.1
- added an option to POI to auto open it on room's access
- fixed a bug on 360 video playback not showing
- scaled down markers and pois on mobiles
5.5
- fixed dates localization
- added the ability to change the registration image in the white label's settings
- added the ability to change the welcome message in the white label's settings
- added the ability to change theme color in the white label's settings
- added language switcher in the login/registration pages
- added the ability to set a password for meetings and live sessions for each virtual tour
- added screen sharing option for jitsi meeting
- moved north room settings to separate tab with preview in associated floorplan/map
- added the ability to enable and assign the sample data to an existing tour in the settings
- added a warning for the image not fully 360 degrees and some presets to try fix them
- added 'allow zoom' option in the room position's settings
- added title and description to show caption on POIs
- added POI type google maps to embed map and street view
- added POI type object 360 (images) to upload different angle version of an object to simulate 3d view
- added real-time visitor counter in the dashboard
- optimized multi resolution
- made some restyling of the backend
- added a shortcut for editing the tour next the dropdown selector
- resolved a bug with room's list slider not center correctly if some rooms as not visible into it
- added the ability to change background color for partial panoramas
- added arrows room's list slider to be set as go to next/prev rooms or pages
- added permissions to editors for each virtual tour
- extended forms to 10 possible fields
5.4
- added the possibility to create virtual tour with sample data
- added more transition effects
- added the possibility to choose which languages to enable
- added a language selector in the top bar of the backend
- added custom header and footer html to showcases
- added video playback to video-type rooms in markers/pois editing
- added custom icons library to default markers/pois style in virtual tour settings
- modified virtual tour top right selector on backend pages
- added Japanese language
- fixed a bug that prevents the live session's video circles to be dragged off the screen
- fixed a bug that permits on live session's receiver to click on poi/markers
- fixed a bug in the map not showing if the first room was not assigned
5.3
- added in settings the ability to change some server params for jitsi, peerjs and leaflet
- added geolocation to map
- added qrcode to all publishable links
- added advertisements
- added audio prompt if the audio not autoplay automatically
- added the possibility to add a custom css class to poi and markers
- added the possibility to add custom script javascript in settings
- added a button to switch between pages of markers and pois
- added the possibility to choose whether to display viewer or landings of virtual tours in the showcase
- reorganized backend menu
- redesigned some backend pages
- fixed a bug when starting the webvr the list of thumbnails was not minimized
- fixed a bug with virtual staging resize when map or meeting are open
- fixed a bug on update
5.2
- added the ability to change the hfov for each room
- added map type: floorplan (image) and map
5.1.1
- fixed a bug that prevent showing progress bar on upload contents
- added Philippine Pesos as currency
5.1
- add upload avatar to user's profile
- added personal informations on user's profile
- added in registration settings the ability to enable and set mandatory fields for user's personal informations
- automatically generating favicons from logos
- added PWA compatibility
- added e-mail notification on new registered users
- added possibility to autostart the presentation
- moved voice commands into settings
- added categories to virtual tours
- added filter by categories in showcases and virtual tour's list
- added the possibility to hide a plan from subscription page
- added an external link to plans (show if payment is not enabled)
- added the possibility to change keyboard mode in virtual tours
- added one time or recurring payment to plans
- added interval months in plans to make recurring subscription from 1 to 12 months
- added Mexican pesos as currency
- fixed bug on room's list menu
5.0
- added a view type in multiple room's view to split the screen with a slider (for virtual staging)
- added possibility to add a name to multiple room's view
- added Vietnamese language
- added possibility to set language for each virtual tour
- added in settings a link to refer to a help document page (visible in the user menu)
- added the ability to create external virtual tours that point to existing tours made with other systems
- added possibility to upload a custom thumbnail for rooms
- added highlighting on login page if user enters incorrect username / email or password
- added back to room button on blur's room page
- added search on the users page
- moved registration and payments settings from plans to settings page
- added in use count in plans page
- moved the reset password form in a separate page always accessible by link provided in the forgot mail
- added the possibility to change texts for activation and forgot mails
- organized preview room's section in tabs to better usability
- added an helper grid to better change position of rooms
- added a button to toggle preview effects of rooms
- fixed a problem on some installation by checking/creating missing directories
- fixed the change of view of the multiple rooms maintaining the exact position of the previous one
4.9
- optimizations and bugfix
- added social authentication for login and registration into backend
- added some badges into backend's lists to count rooms, markers, pois, etc
- moved thumbnail edit within room preview for better cropping
- added functions to reset statistics, leads and forms data
- audio poi is now displayed without blocking the tour
- redesigned pricing plan page
4.8
- added feature that allows you to blur parts of the panoramic image, such as faces and license plates
- added the showcase where you can show all your virtual tours in one place
- added the possibility to edit the crop size of the room's thumbnails
- removed yaw / pitch limitations on room preview in marker and pois sections
- added european portuguese language
- added annotations toggle in the viewer
- fixed a room audio issue not interrupting when exiting the room
4.7.1
- fixed an installation error
- fixed a bug uploading gif on custom icon's library
4.7
- added meeting feature (jitsi meet)
- added Hungarian Language
- added many more features that can be activated based on plans
- added custom features list to plans
- redesigned the select boxes for the contents of the virtual tour
- fixed generation of multi-resolution for multiple rooms view
- fixed currencies formats
- fixed video panorama on iOS
4.6
- added room preview as marker style
- added the possibility to schedule the pois (make them visible only on certain days and times)
- added filters to rooms to adjust brightness, contrast, saturate and grayscale
- added the possibility to change the owner of the virtual tour (only for administrators)
- fixed some audio issues
- fixed some missing translations
- added a check to avoid adding users with the same email / username
4.5
- optimized initial loading
- added leads form to room to protect its display until the form is filled
- added leads section on backend with the possibility to export them
- added more options to rooms for limit views (partial panorama)
- added the ability to load various versions of the room and switch them in the viewer
4.4
- added flyin animation to virtual tours
- added passcode to room to protect its display until the code is entered
- added possibility to duplicate rooms
- added possibility to override transition settings on rooms
- fixed a bug that not permits customers to view virtual tours on reaching plan limit
- fixed a bug that prevent duplicate virtual tours on some database
4.3
- added params to rooms to adjust horizontal pitch and roll for correcting non-leveled panoramas
- modified tooltip text of pois to show longest text on hover
- added possibility to enable or not live session in plans
- added possibility to duplicate virtual tours
- added possibility to delete users
- added possibility to enable validation email on new user registration
- added more language / currencies
- fix stripe init check webhook
4.2.1
- fix bug on adding plans
- fix bug on voice commands
- added more currencies to plans
4.2
- added payments stripe integration
- added possibility to set user language
- added a css editor to customize the backend in the backend settings
- added style option to show/hide virtual tour's name
- added possibility to export in csv the forms data
- added url param to force live session even if disabled - &live_session=1
- added possibility to apply default style settings to all existing markers/pois
- added search functionality on room's selects
- added image preview on selecting points when editing maps
- added possibility to change maps order
- added a marker's option to override the initial position of the next room belong to that marker
4.1
- added transition fadeout setting
- added a css editor to customize the viewer in the backend settings (general or related to the virtual tour)
- converting images to progressive (load faster from browser)
- minor improvement in multires mode
- added more options to plans
- added possibility to view/change plans to users (now manually contact by email for change it)
- added internal note for virtual tours (only visible to admins)
- added more preview styles of marker tooltips
4.0
- introduced multi resolution panoramas support (beta)
- added transition settings to virtual tours for control zoom and time before entering the next room
- added placement of pois in perspective
- added whatsapp chat support
- added possibility to set tooltip on markers (custom text, room preview, room name) and pois (custom text)
- added possibility to play embedded audio of video panoramas
- added controls to video panoramas
- added keyborad controls "z" to go prev room and "x" to go next room
3.9.1
- fixed a bug when uploading panorama image/videos sometimes not detect correct type
- fixed check license
3.9
- added multi language supports
- added in editing room the possibility to go edit next and previous room without go back to the list
- added cancel button on editing pois and markers
- added direct action on editing markers to go to next room and edit its markers
- added editor user's role to edit only assigned virtual tours
- added title and description to images in the Main and Pois galleries
3.8
- added search on virtual tours, rooms, maps backend's sections
- added to each room the link of the virtual tour with the relative starting room
- added expiring dates to virtual tours with redirect urls (only for admins)
- added map north setting
- added target (blank,self) for POI type link external
- added automatic mode in presentations
3.7
- added POI type images gallery
- added keyboard's navigation support
- added intro images for displaying instruction or something else
- when editing the room, the point on the map and its viewing angle are shown to better set the north
- detect hyperlinks in live session chat
- when uploading an image with the bulk function, the file name is retained as the map name
- fixed a bug with room's menu list creation
- fixed audio autoplay
3.6
- added Room's Annotations
- added Room's List Menu editable to show an organized textual list of rooms
- added POI type audio
- moved info box creation in a separated menu with a better content editor
- when uploading an image with the bulk function, the file name is retained as the room name
3.5
- added live sessions to invite people to join a shared virtual tour session with video call and chat
- added upload of audio files in the rooms that are played when entering them
- added more style settings to virtual tours for hide/show some viewer components
- fixed device orientation that was deactivated sometimes
- fixed some backend issues for iphone / ipad
3.4
- added support for 360 videos as room's panorama
- added POI type to play 360 videos
- added registration for customers with default plan assignment (useful for trial)
- added expires day for plans
- added landing page creation toggle for plans
- added friendly urls blacklist into settings to limit their use from customers
- fixed a bug with the room list slider
- fixed a bug that background sound not stop after open a video
3.3
- modified angle of view's direction to the map with the color of the pointer
- added an option to rooms to show or not into the slider list
- added editor for create landing page
- added facebook messenger chat
- cleaned viewer interface and redesigned the menu
3.2
- added angle of view's direction to the map
- added email field to users
- added edit profile for current logged-in user (change username, email, password)
- added mail server settings
- added forgot password to login
- added more POI styles
- added title and description to POI type image
- added possibility to upload local mp4 video to POI type video
3.1
- added POI form fields's type for better validation
- added settings to auto show room's list slider after virtual tour load
- added customizable main form for entire virtual tours
- added bulk upload map images (fast create multiple maps)
3.0
- added same azimuth in virtual tour settings to maintain the same direction with regard to north while navigate between rooms
- accept also PNG panorama files
- added maximum width setting in pixels of panoramic images. if they exceed this width the images will be resized
- added more detailed statistics
- added POI type form to create simple form and store collected data on database
- fixed a bug in editing presentation elements order
2.9.2
- added virtual tour's hyperlink setting for logo
- reusable content (logos and song) for new virtual tours creation
2.9.1
- added a POI type link (external), that open link in a new page instead of embed it
- cleaned up code that was detected as malware
2.9
- redesigned poi and markers backend sections
- added poi and markers individual style settings
- added possibility to resize poi and markers
- added icons library - custom images to use as poi and markers
2.8
- added possibility to change map points size
- added a compression settings for uploaded panorama images
- added possibility to limit upper and lower pitch degree of rooms
- added placement of markers in perspective
- added possibility to activate / deactivate virtual tours
- added white label settings
2.7
- added friendly url to publish link with a custom url
- added google analytics integration
- fixed meta tag for better share preview
2.6
- added possibility to auto start or not the virtual tour at loading
- added customizable background loading image
- added possibility to hide the compass
- added some shortcut on backend
- minor bugfix / improvements
2.5
- introduced voice commands support
- customizable poi's icon
- added poi types 'html' and 'download'
- added possibility to change markers color and background
- added possibility to change pois color and background
2.4
- introduced webvr support
2.3
- added bulk upload panorama images (fast create multiple rooms)
- sync room's list position when change room
2.2.1
- fixed a bug with whatsapp share
2.2
- added multi maps functionality
- added name of map and possibility to change color of points
- improved map visualization
2.1
- added possibility to change room's order
- added possibility to protect the virtual tour with a password
- fixed a bug in the gallery
2.0
- added POIs type content as custom html/text
- added navigation by next / prev arrows
- added a info box customizable via backend
- added an image's gallery customizable via backend
1.9
- added nadir logo (to hide tripod)
- added possibility to set autorotate on inactivity
- customizable marker's style / icon
1.8
- added a presentation feature customizable via backend
1.7
- added custom logo for each virtual tour
- cleaned loading screen
- speed up the first load with background preload of rooms
1.6.1
- improved compatibility / bugfix
1.6
- added users menu backend (only for administrator): manage customers and administrators who can use the application
- added plans menu backend (only for administrator): manage plans with limitation of creation of virtual tours, rooms, markers, pois
- added script that automatically clean unused images
1.5
- added control to show/hide map
- added control to show/hide icons
- added control to show/hide rooms list
- added control to share the virtual tour
- added control to play/stop song
1.4
- fixed room upload with resize if image is too large
- added possibility to allow or not vertical movement of rooms
- minor bugfix
1.3.1
- bugfix
1.3
- added a complete and intuitive backend for create virtual tours without editing code
1.2
- added pois for show image, video or external link
1.1
- correct some bug
- improved mobile resolution
1.0
- initial release