# BraftonDrupal7Module
Drupal 7 Module for Importing content from out XML feed.

##First Steps##

1. Install the Module: 
    1. Go to Modules in your Drupal 7 Backend.
    2. Click _"Install new Module"_
    3. Click _"Choose File"_ and browse your computer for the zip file you downloaded containing the importer.
    4. Click _"Install"_ 
    5. Return to the Modules page.
    6. Find _"Brafton Content Integrator"_ section and check the box next to _"All in One Brafton Content Integrator"_
    7. Scroll down to the bottom of the page and click _"Save Configuration"_
2. Quick Configuration:
    1. General Options:
        - Type of Content : Select Articles.
        - API Root : Select the Brand you are working with
        - Content Author : Select your name
    2. Article Options:
        - API Key : Enter the API key provided to your by your Account manager
        - _(optional)_ Check off **_Create a News Page at "mydomain.com/news"_**
    3. Click _"Save Configuration"_ at the bottom of the page
    4. Run your sites Cron or Click _"Run Article Importer"_ under **_"Manual Control & Archive Uploads"_** to import your first articles.
    
##General Options

**Choose Content Types**

- Type of Content
  - Articles : Select this option if *only* importing articles (will require an API key)
  - Videos : Select this option if *only* importing videos (will require a Public and Private key)
  - Both : Select this option if receiving *both* types of content
  
- API Root (We provide content for the below 3 Brands.  Simply select which Brand you are receiving your content from)
  - Brafton
  - ContentLEAD
  - Castleford
  
**Import Options**

- Content Author : Select a single User to attribute your Imported content to
  - **You may also select Get Author From Article but only if you are receving the _"byline"_ element in your feed.  You may check with your Account Manager
- Import Date
  - Published Date: Import your articles usin the _"published date"_ from your feed **_recommended_**
  - Created Date: Import your articles usin the _"created date"_ from your feed
  - Modified Date: Import your articles usin the _"modified date"_ from your feed
- Enable Comments: Choose if you would like your audience to interact with your articles
- Overwrite any changes made to existing content : check this box to update your content from the feed
  - *Know that this will overwrite any and all changes you may have made through your CMS.  In addtion articles remain in your XML feed for only a period of 30 days _Will also download fresh copies of images since we are updating content.  Images are part of content_*
- Import Content as unpublished : Check this option to import your articles as _unpublished_ allowing you to review or change them before the public is able to view them.
  - _Leaving this unchecked will cause articles to appear on your public facing blog as soon as they are imported_

##Article Options

Note that options here will apply to **_ONLY_** Articles as Videos have different settings Below

- API Key : Your unique Key to access your XML feed of content

**Integration Options for Articles**

- Create a news Page at "mydomain.com/news" : This will create a url to access your blog by the public 
  - It is recommended to set up a view instead if you have that capability
- Create archives pages at "mydomain.com/news/archive/year/month" and an archives block : Creates a page to view your article archives as well as a block to use to display your archive links
- Create a categories block : Creates a Block to use to display the categories available for view
- Create a headlines Block : Creates a Block best placed on your home page to display the latest articles
- Add related articles to Brafton Posts : Adds the latest articles matching the current articles category to the bottom of the page. *Only works if using default content type*
- Content Type - **_For Advanced Users_** Allows you to import your Articles as a pre-existing Content Type rather than the default _News Articles_ Content type created by this module
  - If you choose a pre-existing Content Type you will need to Map your existing fields to the content
    - Content of the Article : This is the body of the article.  Select from the drop down the appropriate field for this content.
    - Image for Article : This is the featured image of the article. Select from the drop down your image field for your content type.
    - Taxonomy for Article : This is the category selected for your article. Select the taxonomy field for your content type.

##Video Options

Note that options here will apply to **_ONLY_** Videos as Articles have different settings Above

- Video Public Key : Your Public Key provided by your Account Manager
- Video Secret Key : Your Private/Secret Key provided by your Account Manager
- Video Feed Number : Your video Feed Id number *Leave this set to 0 unless you have more than one video feed*

**Integration Options for Videos**

- Create a Video Page at "mydomain.com/videos" : This will create a url to access your Video blog by the public 
  - It is recommended to set up a view instead if you have that capability
- Create video archives pages at "mydomain.com/videos/archive/year/month" and an archives block : Creates a page to view your video archives as well as a block to use to display your video archive links
- Create a video categories block : Creates a Block to use to display the categories available for view
- Create a video headlines Block : Creates a Block best placed on your home page to display the latest videos
- Add related videos to Brafton Posts : Adds the latest videos matching the current video category to the bottom of the page. 

##Brafton Video CTA's**

- Use Video CTA's : Check this option if you would like to take advantage of our Custom Call To Actions.
- Atlantis Pause CTA Text : Text appearing at the top of the video when the pause button is pressed
- Atlantis Pause Link : URL to send visitors to when they click the *Atlantis Pause CTA Text*
- Pause Asset Gateway ID : Arch Id of your POP UP form **_Requires your enrollment in ARCH. See your Account Manager for details_**. *Overrides Pause Link*
- Atlantis End CTA Title : The text appearing near the top of the video once it has finished playing.
- Atlantis End CTA Subtitle : Text just below the title appearing in slightly smaller font at the end of the videos
- Atlantis End CTA Link : URL to send visitors to when they click the *Atlantis End CTA Text or Ending CTA Button image* 
- End Asset Gateway ID : Arch Id of your POP UP form **_Requires your enrollment in ARC. See your Account Manager for details_**. *Overrides End CTA Link*
- Atlantis End CTA Text : Clickable text at the end of your video sending visitors to your *Atlantis End CTA Link*
- Ending CTA Button Image : The Image you wish to use instead of the *Atlantis End CTA Text*
- Ending button image Placement : Select the position of your Button *Note: select a positon that doesn't interfere with your other elements* You can also further affect the position with CSS
  - Top Left
  - Top Right
  - Bottom Left
  - Bottom Right
- Ending Background Image : You may select a Background Image to use at the end of your video to provide further branding.

##Brafton Error Reporting##

- Error Report : provides a detailed list of errors that occur during the import process.  Only Errors that report in a failure to import content will be logged automatically.  If such an error occurs Debug mode is turned on.
- Debug Mode : Check the box to catch **_ALL_** Errors regardless of severity.  This option is turned on automatically if a **_Vital_** error is detected.
- Clear the Error Report Log : Clear the current list of errors.

##Manual Control & Archive Uploads##

- Run Article Importer : Allows you to run the Article Importer on Demand
- Run Video Importer : Allows you to run the Video Importer on Demand
- Run Archive Importer : **_Only shows AFTER uploading archive_** Allows you to import your provided Archive.
- Article Archive File : Uploads your Archive of content no longer on the live feed so you may import that content.

