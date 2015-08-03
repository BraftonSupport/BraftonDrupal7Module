<?php 
/*
 *
 **********************************************************************************************
 *
 * This page is for Drupal Hooks that are required for creating pages that may be needed as well as 
 * helper functions for the Content Importer.
 *
 **********************************************************************************************
 */

/*
 **********************************************************************************************
 *	Implementation of hook_menu() to create the blog pages and the admin settings page.
 **********************************************************************************************
 */
function brafton_menu()	{

	$items = array();
    if( variable_get( 'brafton_video_page' ) == 1 )	{
		$items['video'] = array(
			'title' => t( 'Video News' ),
			'page callback' => 'brafton_blog_landing',
			'page arguments' => array( 'b_video' ),
			'access arguments' => array( 'view brafton blog' ),
			'type' => MENU_CALLBACK,
		);
	}
	if( variable_get( 'brafton_blog_page' ) == 1 )	{
		$items['news'] = array(
			'title' => t( 'Latest News' ),
			'page callback' => 'brafton_blog_landing',
			'page arguments' => array( 'b_news' ),
			'access arguments' => array( 'view brafton blog' ),
			'type' => MENU_CALLBACK,
		);
	}
	if( variable_get( 'brafton_blog_archives' ) == 1 )	{
		$items['news/archive'] = array(
			//'title' => t( 'News Archives' ),
			//'title callback' => 'brafton_archives_title', */ I could not get this to work for some reason.  It always shows December 1999.  I opted to programatically add the title through the brafton_archives_load function */
			'page callback' => 'brafton_archives_load',
			'page arguments' => array( 'b_news' ),
			'title arguments' => array( 'b_news',2,3 ),
			'access arguments' => array( 'view brafton blog' ),
			'type' => MENU_CALLBACK,
		);
	}
	if( variable_get( 'brafton_video_archives' ) == 1 )	{
		$items['video/archive'] = array(
			//'title' => t( 'News Archives' ),
			//'title callback' => 'brafton_archives_title', */ I could not get this to work for some reason.  It always shows December 1999.  I opted to programatically add the title through the brafton_archives_load function */
			'page callback' => 'brafton_archives_load',
			'page arguments' => array( 'b_video' ),
			'title arguments' => array( 2,3 ),
			'access arguments' => array( 'view brafton blog' ),
			'type' => MENU_CALLBACK,
		);
	}
	$items['admin/config/content/brafton-settings'] = array(
		'title' => t( 'All in One Brafton Content Integrator' ),
		'description' => t( 'The settings for the All in One Brafton Content Integrator.' ),
		'page callback' => 'drupal_get_form',
		'page arguments' => array( 'brafton_admin_form' ),
		'access arguments' => array( 'administer comments' ),
	);
	return $items;
}

/*
 **************************************************************************************
 *	Implementation of hook_block_info() to register the blog blocks.
 **************************************************************************************
 */
function brafton_block_info()	{

	$blocks = array();
		if( variable_get( 'brafton_blog_headlines' ) == 1 )	{
			$blocks['headlines'] = array(
				'info' => t( 'Brafton Blog Headlines' ),
				'cache' => DRUPAL_NO_CACHE,
			);
		}
		if( variable_get( 'brafton_video_headlines' ) == 1 )	{
			$blocks['headlines'] = array(
				'info' => t( 'Brafton Video Headlines' ),
				'cache' => DRUPAL_NO_CACHE,
			);
		}
		if( variable_get( 'brafton_blog_categories' ) == 1 )	{
			$blocks['categories'] = array(
				'info' => t( 'Brafton Blog Categories' ),
				'cache' => DRUPAL_NO_CACHE,
			);
		}
		if( variable_get( 'brafton_video_categories' ) == 1 )	{
			$blocks['video_categories'] = array(
				'info' => t( 'Brafton Video Categories' ),
				'cache' => DRUPAL_NO_CACHE,
			);
		}
		if( variable_get( 'brafton_blog_archives' ) == 1 )	{
			$blocks['archives'] = array(
				'info' => t( 'Brafton Blog Archives' ),
				'cache' => DRUPAL_NO_CACHE,
			);
		}
		if( variable_get( 'brafton_video_archives' ) == 1 )	{
			$blocks['video_archives'] = array(
				'info' => t( 'Brafton Video Archives' ),
				'cache' => DRUPAL_NO_CACHE,
			);
		}
	return $blocks;
}
/*
 *****************************************************************************************
 *	Implementation of hook_block_view() to render the blog blocks.
 *****************************************************************************************
 */
function brafton_block_view( $delta = '' )	{

	$block = array();
	switch( $delta )	{
		case 'headlines':
			$block['subject'] = '<h3>' . l( t( 'Latest News' ),'news' ) . '</h3>';
			$block['content'] = brafton_headlines( 'b_news' );
			break;
		case 'video_headlines':
			$block['subject'] = '<h3>' . l( t( 'Latest Video News' ),'video' ) . '</h3>';
			$block['content'] = brafton_headlines( 'b_video' );
			break;
		case 'categories':
			$block['subject'] = '<h3>Categories</h3>';
			$block['content'] = brafton_categories( 'b_news' );
			break;
		case 'video_categories':
			$block['subject'] = '<h3>Video Categories</h3>';
			$block['content'] = brafton_categories( 'b_video' );
			break;
		case 'archives':
			$block['subject'] = '<h3>Archives</h3>';
			$block['content'] = brafton_archives( 'b_news' );
			break;
		case 'video_archives':
			$block['subject'] = '<h3>Video Archives</h3>';
			$block['content'] = brafton_archives( 'b_video' );
			break;
	}
	return $block;
}

/*
 ********************************************************************************************
 *	Implementation of hook_node_info() to create the News and Video Article content creation forms.
 ********************************************************************************************
 */
function brafton_node_info() {

  $brafton_nodes =  array(
        'b_news' => array(
			'name' => t( 'News Article' ),
			'base' => 'node_content',
			'description' => t( 'Use <em>news articles</em> for your Brafton, ContentLEAD, or Castleford blog.' ),
        ),
        'b_video' => array(
			'name' => t( 'Video Article' ),
			'base' => 'node_content',
			'description' => t( 'Use <em>Video articles</em> for your Brafton, ContentLEAD, or Castleford video blog.' ),
        ),
    );
  return $brafton_nodes;
}

/*
 *******************************************************************************************
 *	Implementation of hook_node_view_alter to add related posts to the News Article node view.
 *******************************************************************************************
 */
function brafton_node_view_alter( &$build )	{

	if( variable_get( 'brafton_related_articles' ) == 1 )	{
		if( $build['#bundle'] == 'b_news' && $build['#view_mode'] == 'full' ){
			$build['#post_render'] = array( 'brafton_related_posts' );
		}
	}
	if( variable_get( 'brafton_related_videos' ) == 1 )	{
		if( $build['#bundle'] == 'b_video' && $build['#view_mode'] == 'full' ){
			$build['#post_render'] = array( 'brafton_related_posts' );
		}
	}
}

/*
 *******************************************************************************************
 *	Implementation of hook_permission to create toggleable access permissions for the blog page.
 *******************************************************************************************
 */
function brafton_permission()	{

	$permissions = array(
		'view brafton blog' => array(
			'title' => t( 'View the Brafton blog' ),
			'description' => t( 'Allow/Disallow the user to view the Brafton blog.' ),
		),
	);
	return $permissions;
}
/*
 *******************************************************************************************
 *   Helper Functions for the Importer
 *******************************************************************************************
 */
/*
 ***************************************
 * Get the Date Settings from the stored importer settings
 ***************************************
 */
function get_date_setting()	{	
	$date_setting = variable_get( 'brafton_import_date' );
	switch( $date_setting )	{
		case 'published':
			$date = 'getPublishDate';
			break;
		case 'created':
			$date = 'getCreatedDate';
			break;
		case 'lastmodified':
			$date = 'getLastModifiedDate';
			break;
		default:
			$date = 'getPublishDate';
	}
	return $date;

}
/*
 ***************************************
 * Get the Image information for use in the importer
 ***************************************
 */
function get_image_attributes( $articleobj,$feedtype = NULL,$photoClient = NULL,$photos = NULL,$id = NULL )	{
		
	if( $feedtype == 'video' )	{
		$thisPhotos = $photos->ListForArticle( $id,0,100 );
		$photoId = $photos->Get( $thisPhotos->items[0]->id )->sourcePhotoId;
		$image_info = array(
			'url' => $photoClient->Photos()->GetLocationUrl( $photoId )->locationUri,
			'alt' => $photos->Get( $thisPhotos->items[0]->id )->fields['caption'],
			'title' => $photos->Get( $thisPhotos->items[0]->id )->fields['caption'],
		);
		return $image_info;
	}
	else {

		//Grabs the image attributes from the feed.
		
		$images = $articleobj->getPhotos();
		if( !empty( $images ) )	{
			$image_array = $images[0];
			if( $image_array )	{
				$image_large = $image_array->getLarge();
				$image_info = array(
					'url' => $image_large->getUrl(),
					'alt' => $image_array->getAlt(),
					'title' => $image_array->getCaption(),
				);
				return $image_info;
			}
			else {
				$image_info = NULL;
				return $image_info;
			}
		}
	}

}
/*
 ***************************************
 * Get the Category Name
 ***************************************
 */
function get_category( $categories, $i ){
	$name = $categories[$i]->getName();
	return $name;
}
/*
 ***************************************
 * Set the Categories for imported articles
 ***************************************
 */
function set_article_categories( $articleobj,$bundle,$categoryObj = NULL )	{
    $type = variable_get('brafton_existing_type');
	//Grabs the categories from the feed.
    if($categoryObj != NULL){
        $categories = array( $categoryObj );
        $video_loader = true;
        $type = 'b_video';
    }else{
        $categories = $articleobj->getCategories();
        $video_loader = false;
    }

    switch($type){
        case 'b_news':
        $vocab = 'b_news_t';
        break;
        case 'b_video':
        $vocab = 'b_news_v';
        break;
        default:
        $info = field_info_field(variable_get('brafton_custom_taxonomy'));
        $vocab = $info['settings']['allowed_values'][0]['vocabulary'];
        break;
    }

	//Checks to see if the terms already exist in the Brafton Taxonomy Vocabulary.  If they do not, new terms are created.
	$i = 0; 
	$brafton_vocabulary = taxonomy_vocabulary_machine_name_load( $vocab );
	$vid = $brafton_vocabulary->vid;
	$cat_array = array();
	foreach($categories as $category){
		if( $video_loader )	{
			$name = $categories[0]->name;
		}
		else {
			$name = get_category( $categories, $i );
			$i = $i + 1; 
		}
		$check_cat = taxonomy_get_term_by_name( $name );
		$found = 0;
		foreach( $check_cat as $term )	{
			if( $term->vid == $vid ){
				$tid = $term->tid;
				$found = 1;
			}
		}
		if($found == 0)	{
			$new_term = array(
				'vid' => $vid,
				'name' => $name,
			);
			$new_term = ( object ) $new_term;
			taxonomy_term_save( $new_term );
			$tid = $new_term->tid;
		}
		array_push( $cat_array,$tid );
	}
	
	//Returns an array of valid term ids for the given article.
	
	return $cat_array;

}
/*
 ***************************************
 * Check if the article already exsists in the database
 ***************************************
 */
function check_if_article_exists( $id)	{

	//Queries the brafto_id table and checks for the id
	
	$query = new EntityFieldQuery();
	$query->fieldCondition( 'field_brafton_id','value',$id,'=' );
	$result = $query->execute();
	return $result;
}
/*
 ***************************************
 * Dynamic Author Function
 ***************************************
 */
function checkAuthor($author, $byLine){
    /* if Get author from article is selected checks for an author in the element byLine of the feed.  If an author        exsists checks that user author name against list of users in the db and creates them if they do not exsist.        Defaults to anonymous if if no author in byline and get author from article is selected
    */
    $user_id = $author;
    if($user_id){
        return $user_id;
    }
    else{
        if(!empty($byLine)){
            if(!($user = user_load_by_name($byLine))){
                //create user programatically 
                $password = user_password(8);
                $fields = array(
                    'name' => $byLine,
                    'mail' => $byLine.rand().'@example.com',
                    'pass' => $password,
                    'status' => 1,
                    'init' => 'email address',
                    'roles' => array(
                      DRUPAL_AUTHENTICATED_RID => 'authenticated user',
                    ),
                  );
                $user = user_save('', $fields);
            }
            return $user->uid;
        }
        else{
            return $user_id;
        }
    }
}
/* video updates*/
function generate_source_tag($src, $resolution)
{
    $tag = ''; 
    $ext = pathinfo($src, PATHINFO_EXTENSION); 
    return sprintf('<source src="%s" type="video/%s" data-resolution="%s" />', $src, $ext, $resolution );
}