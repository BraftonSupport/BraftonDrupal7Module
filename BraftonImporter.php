<?php
function BraftonArticleImporter(){
    $errors = new BraftonErrorReport(variable_get('brafton_api_key'), variable_get( 'brafton_api_root' ) );
	//Gathers feed type, Api and Video Keys, and archive file information from the Brafton module settings page.
    $import_list = array();
	$feed_type = variable_get( 'brafton_feed_type' );
	$is_api = variable_get( 'brafton_api_key' );
	$is_archive = variable_get( 'brafton_archive_file' );
	$overwrite = variable_get( 'brafton_overwrite' );
	$is_published = variable_get( 'brafton_published' );
    $counter = 0;
    //Define default fields for b_news type_url_form_media

		if( $is_api || $is_archive )	{

			//Loads the date and overwrite settings.
	
			$date = get_date_setting();
	
			//Loads the article objects from the feed into an array.
	
			$article_array = load_article_array( $is_archive );

			//Loops through the article array 

			foreach( $article_array as $value )	{
                
				//Checks to see if the article already exists.  If it does not, a new node is created of type b_news.  If it does, then depending upon the overwrite settings the existing node is either loaded, or we iterate to the next article in the feed
	
				$id = $value->getId();
				$check = check_if_article_exists( $id );
				if( !empty( $check ) && $overwrite == 1 )	{
					$nid = key($check['node']);
					$node = node_load( $nid );
				}
				elseif( empty( $check ) )	{
					$node = new stdClass();
				}
				else	{
					continue;
				}
		
				//Gets an array of image information from the feed.
		
				$image = get_image_attributes( $value );
		
				//Gets the article categories as an array of valid and unique term ids.
		          
				$categories = set_article_categories( $value,'b_news' );
		
				//Instantiation of each article component as a field in the node object.
                $node->status = $is_published == 1? 0: 1;
                
                $types = array(
                    'body'  => 'body',
                    'image' => 'field_brafton_image',
                    'tax'   => 'field_brafton_term'
                );
                if(variable_get('brafton_existing_type') != 'b_news'){
                    $types['body'] = variable_get('brafton_custom_body');
                    $types['image'] = variable_get('brafton_custom_image');
                    $types['tax']   = variable_get('brafton_custom_taxonomy');
                }
				$node->type = variable_get('brafton_existing_type');
				$node->language = LANGUAGE_NONE;
				$node->title = $value->getHeadline();
                $import_list['title'][] = $node->title;
				$node->uid = checkAuthor(variable_get( 'brafton_author' ), $value->getByLine());
				//$nodestatus = 1;
				$node->created = strtotime( $value->$date() );
                $node->updated = $node->created;
				$node->promote = 0;
				$node->sticky = 0;
				$node->comment = variable_get( 'brafton_comments' );
                $node->{$types['body']}[ $node->language ][0] = array(
					'value' => $value->getText(),
					'summary' => $value->getExtract(),
					'format' => 'full_html',
				);
				if ( $image ) {
                    $node->{$types['image']}[ $node->language ][0] = ( array ) system_retrieve_file( $image['url'],NULL,TRUE,FILE_EXISTS_REPLACE );
                            $node->{$types['image']}[ $node->language ][0]['alt'] = $image['alt'];
                            $node->{$types['image']}[ $node->language ][0]['title'] = $image['title'];
				}
                if(field_info_instance('node', 'field_brafton_id', $node->type) == NULL){
                    $brafton_id_field = array(
                                        'field_name' => 'field_brafton_id',
                                        'entity_type' => 'node',
                                        'bundle' => 'b_news',
                                        'display' => array(
                                            'default' => array(
                                                'label' => 'hidden',
                                                'type' => 'hidden',
                                            )
                                        )
                                    );
                    field_create_instance($brafton_id_field);
                }
				$node->field_brafton_id[ $node->language ][0]['value'] = $id;
				//Setting the article pause cta text
				//ensure categories don't get added twice
				
				$cats=false;
				$oldcats;
                if($overwrite && isset($node->{$types['tax']}[$node->language])){
					$oldcats = $node->{$types['tax']}[$node->language];
					$cats=true;
				}
				
				foreach( $categories as $category )	{
					if($cats){
						foreach( $oldcats as $oldcat )	{
							if($oldcat['tid']!=$category){
								$node->{$types['tax']}[ $node->language ][]['tid'] = $category;
							}
						}
					} else $node->{$types['tax']}[ $node->language ][]['tid'] = $category;
				}
				
				//end category code
				
				node_save( $node );
                var_dump($node);
				taxonomy_node_insert( $node );
				$nid=$node->nid;
				$alias = drupal_get_path_alias("node/" . $nid);
                $counter++;
                ++$errors->level;
			}
		}
    $import_list['counter'] = $counter;
    return $import_list;
}
function BraftonVideoImporter(){
    
    if(!isset($errors)){
        $errors = new BraftonErrorReport(variable_get('brafton_api_key'), variable_get( 'brafton_api_root' ) );
    }else{
        $errors->level = 1;   
    }
	//Gathers feed type, Api and Video Keys, and archive file information from the Brafton module settings page.
    $import_list = array();
	$feed_type = variable_get( 'brafton_feed_type' );
	$is_video_public = variable_get( 'brafton_video_public_key' );
	$is_video_secret = variable_get( 'brafton_video_secret_key' );
	$overwrite = variable_get( 'brafton_overwrite' );
	$is_published = variable_get( 'brafton_published' );
    
		if( $is_video_public && $is_video_secret )	{
			$domain = variable_get( 'brafton_api_root' );
			switch ($domain) {
				case 'http://api.brafton.com':
					$baseURL = 'http://livevideo.api.brafton.com/v2/';
					$photoURI = "http://pictures.brafton.com/v2/";
					break;
				case 'http://api.contentlead.com':
					$baseURL = 'http://livevideo.api.contentlead.com/v2/';
					$photoURI = "http://pictures.contentlead.com/v2/";
					break;
				 case 'http://api.castleford.com.au':
				 	$baseURL = 'http://livevideo.api.castleford.com.au/v2/';
					$photoURI = "http://pictures.castleford.com.au/v2/";
					break;
				default:
					$baseURL = 'http://livevideo.api.brafton.com/v2/';
					$photoURI = "http://pictures.brafton.com/v2/";
					break;
			}
			$videoClient = new AdferoVideoClient( $baseURL,$is_video_public,$is_video_secret );
			$videoOutClient = $videoClient->videoOutputs();
			$client = new AdferoClient( $baseURL,$is_video_public,$is_video_secret );
			$photoClient = new AdferoPhotoClient( $photoURI );
			$photos = $client->ArticlePhotos();
			$feeds = $client->Feeds();
			$feedList = $feeds->ListFeeds( 0,10 );
			$feedNum = variable_get( 'brafton_video_feednum' );
			$articles = $client->Articles();
			$articleList = $articles->ListForFeed( $feedList->items[ $feedNum ]->id,'live',0,100 );
			$sitemap=array();
			foreach( $articleList->items as $value )	{
				$id = $value->id;
				$categories = $client->Categories(); 
				$check = check_if_article_exists( $id,'b_video' );
				if( !empty( $check ) && $overwrite == 1 )	{
					$nid = key($check['node']);
					$node = node_load( $nid );
				}
				elseif( empty( $check ) )	{
					$node = new stdClass();
				}
				else	{
					continue;
				}
				$thisArticle = $client->Articles()->Get( $id );
				if( $categories->ListForFeed( $feedList->items[ $feedNum ]->id,0,100 )->items )	{
					$categoryId = $categories->ListForArticle( $id,0,100 )->items[0]->id;
					$category = $categories->Get( $categoryId );
					$categories = set_article_categories( $value,'b_video',$category );
				}

				$presplash = $thisArticle->fields['preSplash'];
				$postsplash = $thisArticle->fields['postSplash'];
				
				$cta_option = variable_get( 'brafton_video_ctas' );
				$pause_cta_text = variable_get( 'brafton_video_pause_cta_text' );
				$pause_cta_link = variable_get( 'brafton_video_pause_cta_link' );
				$end_cta_title = variable_get( 'brafton_video_end_cta_title' );
				$end_cta_subtitle = variable_get( 'brafton_video_end_cta_subtitle' );
				$end_cta_link = variable_get( 'brafton_video_end_cta_link' );
				$end_cta_text = variable_get( 'brafton_video_end_cta_text' );


				$videoList=$videoOutClient->ListForArticle($id,0,10);
				$list=$videoList->items;
				$embedCode = sprintf( "<video id='video-%s' class=\"ajs-default-skin atlantis-js\" controls preload=\"auto\" width='512' height='288' poster='%s' >", $id, $presplash );

				foreach($list as $listItem){
					$output=$videoOutClient->Get($listItem->id);
					$type = $output->type;
					$path = $output->path; 
					$resolution = $output->height; 
					$source = generate_source_tag( $path, $resolution );
					$embedCode .= $source; 
				}		
				$embedCode .= '</video>';

				$script = '<script type="text/javascript">';
	            $script .=  'var atlantisVideo = AtlantisJS.Init({';
	            $script .=  'videos: [{';
	            $script .='id: "video-' . $id . '"';
	            if($cta_option){
                    $marpro = '';
                    $pause_asset_id = variable_get('brafton_video_pause_cta_asset_gateway_id');
                    if($pause_asset_id != ''){
                        $marpro = "assetGateway: { id: '$pause_asset_id' },";
                    }
                    $endingBackground = '';
                    $end_background_image = variable_get('brafton_video_end_cta_background_url');
                    if($end_background_image != ''){
                        $end_background_image = file_create_url($end_background_image);
                        $endingBackground = "background: '$end_background_image',";
                    }
                    $end_asset_id = variable_get('brafton_video_end_cta_asset_gateway_id');
                    if($end_asset_id != ''){
                        $endingBackground .= "assetGateway: { id: '$end_asset_id' },";
                    }
                    $buttonImage = '';
                    $button_image_url = variable_get('brafton_video_end_cta_button_image_url');
                    if($button_image_url != ''){
                        $button_image_url = file_create_url($button_image_url);
                        $buttonImage = "image: '$button_image_url',";
                    }
                    $button_image_postition = variable_get('brafton_video_end_cta_button_placement');
                    if($button_image_postition){
                        switch($button_image_postition){
                            case 'tl':
                            $postion = '{pos: "top", val: "15px"},{pos: "left", val: "15px"}';
                            break;
                            case 'tr':
                            $postion = '{pos: "top", val: "15px"},{pos: "right", val: "15px"}';
                            break;
                            case 'br':
                            $postion = '{pos: "bottom", val: "15px"},{pos: "right", val: "15px"}';
                            break;
                            case 'bl':
                            $postion = '{pos: "bottom", val: "15px"},{pos: "lelft", val: "15px"}';
                        }
                        $buttonImage .= "position: [ " . $postion . " ]";
                    }
                    
	            $script .=',';
	            $script	.= <<<EOT
					        pauseCallToAction: {
                                $marpro
                                link: "$pause_cta_link",
					            text: "$pause_cta_text"
					        },
					        endOfVideoOptions: {
                                $endingBackground
					            callToAction: {
					                title: "$end_cta_title",
					                subtitle: "$end_cta_subtitle",
					                button: {
					                    link: "$end_cta_link",
					                    text: "$end_cta_text",
                                        $buttonImage
					                }
					            }
					        }
EOT;
				}
	            $script .= '}]';
	            $script .= '});';
	            $script .=  '</script>';
				$embedCode .= $script;  

				//Wraps a Div around the embed code

				$embed_code = "<div id='post-single-video'>" . $embedCode . "</div>";

				//Gets the image data from the feed
                $photoCheckId = $photos->ListForArticle( $id,0,100 );
                if($photoCheckId->items[0]->id){
				    $image = get_image_attributes( NULL,'video',$photoClient,$photos,$id );
                }else{
                    $image = array('url' => '','alt' => '','title' => '',);
                }

				//Creates the video node and inserts the values from the feed

				$node->type = 'b_video';
				$node->language = LANGUAGE_NONE;
				$node->title = $thisArticle->fields['title'];
                $import_list[]['title'] = $node->title;
				$node->uid = variable_get( 'brafton_author' );
				$node->status = $is_published == 1? 0: 1;;
				$node->created = strtotime( $thisArticle->fields['lastModifiedDate'] );
				$node->promote = 0;
				$node->sticky = 0;
				$node->comment = variable_get( 'brafton_comments' );
				$node->body[ $node->language ][0] = array(
					'value' => $thisArticle->fields['content'],
					'summary' => $thisArticle->fields['extract'],
					'format' => 'full_html',
				);
				$node->field_brafton_video[ $node->language ][0] = array(
					'value' => $embed_code,
					'format' => 'full_html',
				);
				if ( $image ) {
					$node->field_brafton_image[ $node->language ][0] = ( array ) system_retrieve_file( $image['url'],NULL,TRUE,FILE_EXISTS_REPLACE );
					$node->field_brafton_image[ $node->language ][0]['alt'] = $image['alt'];
					$node->field_brafton_image[ $node->language ][0]['title'] = $image['title'];
				}
				$node->field_brafton_id[ $node->language ][0]['value'] = $id;
				if( $categories )	{
					foreach( $categories as $category )	{
						$node->field_brafton_video_term[ $node->language ][]['tid'] = $category;
					}
				}
				node_save( $node );
				taxonomy_node_insert( $node );
				$nid=$node->nid;
				$alias = drupal_get_path_alias("node/" . $nid);
				$sitemap_url = $GLOBALS['base_url'].'/'.$alias;
				
				$sitemapaddition = array(
					"url" => $sitemap_url,
					"location" => $path,
					"title" => $node->title,
					"thumbnail" => $presplash,
					"description" =>$thisArticle->fields['content'],
					"publication" =>$thisArticle->fields['lastModifiedDate'],
				);
				
				$sitemap[]=$sitemapaddition;
                ++$errors->level;
			}

		}
		brafton_add_URLs($sitemap);
    return $import_list;
}