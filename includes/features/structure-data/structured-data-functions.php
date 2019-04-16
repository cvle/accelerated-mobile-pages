<?php

// 12. Add Logo URL in the structured metadata
	    add_filter( 'amp_post_template_metadata', 'ampforwp_update_metadata', 10, 2 );
	    function ampforwp_update_metadata( $metadata, $post ) {
	        global $redux_builder_amp;
	        $structured_data_logo = '';
	        $structured_data_main_logo = '';
	        $ampforwp_sd_height = '';
	        $ampforwp_sd_width = '';
	        $ampforwp_sd_height = ampforwp_get_setting('ampforwp-sd-logo-height');
	        $ampforwp_sd_width = ampforwp_get_setting('ampforwp-sd-logo-width');
	        if (! empty( $redux_builder_amp['opt-media']['url'] ) ) {
	          $structured_data_main_logo = $redux_builder_amp['opt-media']['url'];
	        }
	        if (! empty( $redux_builder_amp['amp-structured-data-logo']['url'] ) ) {
	          $structured_data_logo = __($redux_builder_amp['amp-structured-data-logo']['url'], 'accelerated-mobile-pages');
	        }
	        if ( $structured_data_logo ) {
	          $structured_data_logo = $structured_data_logo;
	        } else {
	          $structured_data_logo = $structured_data_main_logo;
	        }
	        $metadata['publisher']['logo'] = array(
	          '@type'   => 'ImageObject',
	          'url'     =>  $structured_data_logo ,
	          'height'  => $ampforwp_sd_height,
	          'width'   => $ampforwp_sd_width,
	        );

	        //code for adding 'description' meta from Yoast SEO

	        if($redux_builder_amp['ampforwp-seo-yoast-description']){
	         if ( class_exists('WPSEO_Frontend') ) {
	           $front = WPSEO_Frontend::get_instance();
	           $desc = $front->metadesc( false );
	           if ( $desc ) {
	             $metadata['description'] = $desc;
	           }

	           // Code for Custom Frontpage Yoast SEO Description
	           $post_id = ampforwp_get_frontpage_id();
	           if ( class_exists('WPSEO_Meta') ) {
	             $custom_fp_desc = WPSEO_Meta::get_value('metadesc', $post_id );
	             if ( is_home() && $redux_builder_amp['amp-frontpage-select-option'] ) {
	               if ( $custom_fp_desc ) {
	                 $metadata['description'] = $custom_fp_desc;
	               } else {
	                 unset( $metadata['description'] );
	               }
	             }
	           }
	         }
	        }
	        //End of code for adding 'description' meta from Yoast SEO
	        return $metadata;
	    }


	// 13. Add Custom Placeholder Image for Structured Data.
	// if there is no image in the post, then use this image to validate Structured Data.
	add_filter( 'amp_post_template_metadata', 'ampforwp_update_metadata_featured_image', 10, 2 );
	function ampforwp_update_metadata_featured_image( $metadata, $post ) {
			global $redux_builder_amp;
			global $post;
			$post_id = get_the_ID() ;
			$post_image_id = get_post_thumbnail_id( $post_id );
			$structured_data_image = wp_get_attachment_image_src( $post_image_id, 'full' );
			$post_image_check = $structured_data_image;
			$structured_data_image_url = '';

			if ( $post_image_check == false) {

				if (! empty( $redux_builder_amp['amp-structured-data-placeholder-image']['url'] ) ) {
					$structured_data_image_url = __($redux_builder_amp['amp-structured-data-placeholder-image']['url'], 'accelerated-mobile-pages');
				}
					$structured_data_image = $structured_data_image_url;
					$structured_data_height = intval(ampforwp_get_setting('amp-structured-data-placeholder-image-height'));
					$structured_data_width = intval($redux_builder_amp['amp-structured-data-placeholder-image-width']);

					$metadata['image'] = array(
						'@type' 	=> 'ImageObject',
						'url' 		=> $structured_data_image ,
						'height' 	=> $structured_data_height,
						'width' 	=> $structured_data_width,
					);
			}
			// Custom Structured Data information for Archive, Categories and tag pages.
			if ( is_archive() ) {
					$structured_data_image = __($redux_builder_amp['amp-structured-data-placeholder-image']['url'], 'accelerated-mobile-pages');
					$structured_data_height = intval(ampforwp_get_setting('amp-structured-data-placeholder-image-height'));
					$structured_data_width = intval(ampforwp_get_setting('amp-structured-data-placeholder-image-width'));
					$structured_data_archive_title 	= "Archived Posts";
					$structured_data_author				=  get_userdata( 1 );
							if ( $structured_data_author ) {
								$structured_data_author 		= $structured_data_author->display_name ;
							} else {
								$structured_data_author 		= "admin";
							}

					$metadata['image'] = array(
						'@type' 	=> 'ImageObject',
						'url' 		=> $structured_data_image ,
						'height' 	=> $structured_data_height,
						'width' 	=> $structured_data_width,
					);
					$metadata['author'] = array(
						'@type' 	=> 'Person',
						'name' 		=> $structured_data_author ,
					);
					$metadata['headline'] = $structured_data_archive_title;
			}

			// Get Image metadata from the Custom Field
			if(ampforwp_is_custom_field_featured_image() && ampforwp_cf_featured_image_src()){
				$metadata['image'] = array(
						'@type' 	=> 'ImageObject',
						'url' 		=> ampforwp_cf_featured_image_src('url') ,
						'width' 	=> ampforwp_cf_featured_image_src('width'),
						'height' 	=> ampforwp_cf_featured_image_src('height'),
				);	
			}

			// Get image metadata from The Content
			if( true == $redux_builder_amp['ampforwp-featured-image-from-content'] && ampforwp_get_featured_image_from_content() ){
				$metadata['image'] = array(
						'@type' 	=> 'ImageObject',
						'url' 		=> ampforwp_get_featured_image_from_content('url') ,
						'width' 	=> ampforwp_get_featured_image_from_content('width'),
						'height' 	=> ampforwp_get_featured_image_from_content('height'),
				);
			}

			if( in_array( "image" , $metadata )  ) {
				if ( $metadata['image']['width'] < 696 ) {
		 			$metadata['image']['width'] = 700 ;
	     		}
			}
		return $metadata;
	}




	// 45. searchpage, frontpage, homepage structured data
add_filter( 'amp_post_template_metadata', 'ampforwp_search_or_homepage_or_staticpage_metadata', 10, 2 );
function ampforwp_search_or_homepage_or_staticpage_metadata( $metadata, $post ) {
		global $redux_builder_amp,$wp;
		$desc = '';
		if( is_search() || is_home() || ( is_home() && $redux_builder_amp['amp-frontpage-select-option'] ) ) {

			if( is_home() || is_front_page() ){
				global $wp;
				$current_url = home_url( $wp->request );
				//$current_url = dirname( $current_url );
				$headline 	 =  get_bloginfo('name') . ' | ' . get_option( 'blogdescription' );
			} else {
				$current_url 	= trailingslashit(get_home_url())."?s=".get_search_query();
				$current_url 	= untrailingslashit( $current_url );
				$headline 		= ampforwp_translation($redux_builder_amp['amp-translator-search-text'], 'You searched for:') . '  ' . get_search_query();
			}
			// creating this to prevent errors
			$structured_data_image_url = '';
			$page = '';
			// placeholder Image area
			if (! empty( $redux_builder_amp['amp-structured-data-placeholder-image']['url'] ) ) {
				$structured_data_image_url = __($redux_builder_amp['amp-structured-data-placeholder-image']['url'], 'accelerated-mobile-pages');
			}
			$structured_data_image =  $structured_data_image_url; //  Placeholder Image URL
			$structured_data_height = intval(ampforwp_get_setting('amp-structured-data-placeholder-image-height')); //  Placeholder Image width
			$structured_data_width = intval(ampforwp_get_setting('amp-structured-data-placeholder-image-width')); //  Placeholder Image height
			$current_url_in_pieces = explode( '/', $current_url );
			if( ampforwp_is_front_page() ) {
				 // ID of slected front page
					$ID = ampforwp_get_frontpage_id();
					$headline =  get_the_title( $ID ) . ' | ' . get_option('blogname');
					$static_page_data = get_post( $ID );
					$datePublished = $static_page_data->post_date;
					$dateModified = $static_page_data->post_modified;
					$featured_image_array = wp_get_attachment_image_src( get_post_thumbnail_id($ID), 'full' ); 
					// Featured Image structured Data
					if( $featured_image_array ) {
						$structured_data_image = $featured_image_array[0];
						$structured_data_width  = $featured_image_array[1];
						$structured_data_height  = $featured_image_array[2];
					}
					// Frontpage Author
					$structured_data_author = '';
					$structured_data_author	= get_userdata($static_page_data->post_author );
					if ( $structured_data_author ) {
						$structured_data_author = $structured_data_author->display_name ;
					} else {
						$structured_data_author = "admin";
					}
					$metadata['author']['name'] = $structured_data_author;
				}
				else{
					if( ampforwp_get_blog_details() == true ) {
						$headline = ampforwp_get_blog_details('title') . ' | ' . get_option('blogname');
						$page_for_posts  =  get_option( 'page_for_posts' );
						$blog_data = get_post($page_for_posts); 
						if ( $post ) {
							$datePublished = $blog_data->post_date;
							$dateModified = $blog_data->post_modified;
						}
					}
					else {
						// To DO : check the entire else section .... time for search and homepage...wierd ???
						$datePublished = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) - 2 );
						// time difference is 2 minute between published and modified date
						$dateModified = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
					}
				}
			$metadata['image'] = array(
				'@type' 	=> 'ImageObject',
				'url' 		=> $structured_data_image ,
				'height' 	=> $structured_data_height,
				'width' 	=> $structured_data_width,
			);
			$metadata['datePublished'] = $datePublished; // proper published date added
			$metadata['dateModified'] = $dateModified; // proper modified date
			$remove 	= '/'. AMPFORWP_AMP_QUERY_VAR;
			$current_url 	= str_replace($remove, '', $current_url);
		  	$query_arg_array = $wp->query_vars;
		  	if( array_key_exists( "page" , $query_arg_array  ) ) {
			   $page = $wp->query_vars['page'];
		  	}
		  	if ( $page >= '2') { 
				$current_url = trailingslashit( $current_url  . '?page=' . $page);
			}
			$metadata['mainEntityOfPage'] = trailingslashit($current_url); // proper URL added
			$metadata['headline'] = $headline; // proper headline added
	}
	// Description for Structured Data
	$desc =   esc_attr( convert_chars( stripslashes( ampforwp_generate_meta_desc('json'))) );
	$metadata['description'] = $desc;
	return $metadata;
}

// Structured Data Type
add_filter( 'amp_post_template_metadata', 'ampforwp_structured_data_type', 20, 1 );
function ampforwp_structured_data_type( $metadata ) {
	if ( !is_array($metadata) ) {
		return $metadata;
	}
	global $redux_builder_amp, $post;
	$post_types 	= '';
	$set_sd_post 	= '';
	$set_sd_page 	= '';

	$set_sd_post 	= ampforwp_get_setting('ampforwp-sd-type-posts');
	$set_sd_page 	= ampforwp_get_setting('ampforwp-sd-type-pages');
 	$post_types 	= ampforwp_get_all_post_types();

	if ( $post_types ) { // If there are any custom public post types.
    	foreach ( $post_types  as $post_type ) {

        	if ( isset( $post->post_type ) && ('page' == $post->post_type || 'post' == $post->post_type) ) {
        		continue;
        	}
        	
	       	if ( isset( $post->post_type ) && $post->post_type == $post_type ) {
        		if ( empty( $redux_builder_amp['ampforwp-sd-type-'.$post_type.''] ) && $redux_builder_amp['ampforwp-seo-yoast-description'] == 0 ) {
					return;
				}
				if(isset($metadata['@type']) && $metadata['@type']){
        			$metadata['@type'] = $redux_builder_amp['ampforwp-sd-type-'.$post_type.''];
        		}
        		return $metadata;
        	}
        }
    }

	if ( empty( $set_sd_post ) && is_single() && $redux_builder_amp['ampforwp-seo-yoast-description'] == 0 ) {;
		return;
	}

	if ( empty( $set_sd_page ) && is_singular( $post_type = 'page' ) && $redux_builder_amp['ampforwp-seo-yoast-description'] == 0 ) {
			return;
	}
	if ( isset( $post->post_type ) && 'post' == $post->post_type ) {
		if(isset($metadata['@type']) && $metadata['@type']){
			$metadata['@type'] = $set_sd_post;
		}
	}

	if ( (isset( $post->post_type ) && 'page' == $post->post_type) || ampforwp_is_front_page() || ampforwp_is_blog()) {
		if ( empty( $set_sd_page )){
			return;
		}
		if(isset($metadata['@type']) && $metadata['@type']){
			$metadata['@type'] = $set_sd_page;
		}
	} 
	if(isset($metadata['@type']) && $metadata['@type'] == 'NewsArticle'){
	$post_id = ampforwp_get_the_ID();
	$content = $post->post_content;
	$metadata['articleBody'] = esc_html($content);
	}
	return $metadata;
}
// VideoObject
add_filter( 'amp_post_template_metadata', 'ampforwp_structured_data_video_thumb', 20, 1 );
if ( ! function_exists('ampforwp_structured_data_video_thumb') ) {
	function ampforwp_structured_data_video_thumb( $metadata ) {
		if ( !is_array($metadata) ) {
    		return $metadata;
    	}
		global $redux_builder_amp, $post;
		// VideoObject
		if ( isset($metadata['@type']) && 'VideoObject' == $metadata['@type'] ) {
			$post_image_id = '';
			$post_image_id = get_post_thumbnail_id( get_the_ID() );
			$post_image = wp_get_attachment_image_src( $post_image_id, 'full' );
			$structured_data_video_thumb_url = '';
			// If there's no featured image, take default from settings
			if ( false == $post_image ) {
				if ( ! empty( $redux_builder_amp['amporwp-structured-data-video-thumb-url']['url'] ) ) {
						$structured_data_video_thumb_url = __($redux_builder_amp['amporwp-structured-data-video-thumb-url']['url'], 'accelerated-mobile-pages');
					}
			}
			// If featured image is present, take it as thumbnail
			else {
				$structured_data_video_thumb_url = $post_image[0];
			}
			$metadata['name'] = $metadata['headline'];
			$metadata['uploadDate'] = $metadata['datePublished'];
			$metadata['thumbnailUrl'] = $structured_data_video_thumb_url;
			$desc = $post->post_content;
			if($desc){	
				$desc = addslashes( wp_trim_words( strip_tags( $desc ) , 30 ) );
				$metadata['description'] = $desc;	
			}	       
		}
		// Recipe
		if ( isset($metadata['@type']) && 'Recipe' == $metadata['@type'] ) {
			$metadata['name'] = $metadata['headline'];
		}
		return $metadata;
	}
}
// #1975 Product
add_filter( 'amp_post_template_metadata', 'ampforwp_structured_data_product', 20, 1 );
if ( ! function_exists('ampforwp_structured_data_product') ) {
	function ampforwp_structured_data_product( $metadata ) {
		global $redux_builder_amp, $post;
		// Adding Product's Name and unsetting the Google unrecognized data for type Product
		if ( isset($metadata['@type']) && 'Product' == $metadata['@type'] ) {
			$metadata['name'] = $metadata['headline'];
			unset($metadata['dateModified']);
			unset($metadata['datePublished']);
			unset($metadata['publisher']);
			unset($metadata['author']);
			unset($metadata['headline']);
		}
		
		return $metadata;
	}
}
// Multiple Images #2259
add_filter( 'amp_post_template_metadata', 'ampforwp_sd_multiple_images', 20, 1 );
if ( ! function_exists('ampforwp_sd_multiple_images') ) {
	function ampforwp_sd_multiple_images($metadata){
		if ( ampforwp_get_setting('ampforwp-sd-multiple-images') ) {
			if ( isset($metadata['image']['width']) && 1200 <= $metadata['image']['width'] ){
				// 16x9
				$image1_width = 1280;
				$image1_height = 720;
				$image1 = ampforwp_aq_resize( $metadata['image']['url'], $image1_width, $image1_height, true, false, true );
				$image1_url = $image1[0];
				// 4x3
				$image2_width = 640;
				$image2_height = 480;
				$image2 = ampforwp_aq_resize( $metadata['image']['url'], $image2_width, $image2_height, true, false, true );
				$image2_url = $image2[0];
				// 1x1
				$image3_width = 300;
				$image3_height = 300;
				$image3 = ampforwp_aq_resize( $metadata['image']['url'], $image3_width, $image3_height, true, false, true );
				$image3_url = $image3[0];
				$metadata['image'] = array($image3_url, $image2_url, $image1_url); 
			}
		}
		return $metadata;
	}
}
// schema.org/SiteNavigationElement missing from menus #1229 & #2952
add_action('amp_post_template_footer','ampforwp_sd_sitenavigation');
function ampforwp_sd_sitenavigation(){
    if ( ! class_exists('saswp_fields_generator') ) {
	    $input = array();           
	    $navObj = array();       
	    $menuLocations = get_nav_menu_locations();        
	    if(!empty($menuLocations) ){ 
	        foreach($menuLocations as $type => $id){
	            $menuItems = wp_get_nav_menu_items($id);
	            if($menuItems){
	                if($type == 'amp-menu' || $type == 'amp-footer-menu' ){                
	                    foreach($menuItems as $items){
	                      $navObj[] = array(
	                             "@context"  => "https://schema.org",
	                             "@type"     => "SiteNavigationElement",
	                             "@id"       => trailingslashit(get_home_url()).$type,
	                             "name"      => $items->title,
	                             "url"       => $items->url
	                      );

	                    }
	           		}                                                           
	            }
	        }
	        if($navObj){  
	            $input['@context'] = 'https://schema.org'; 
	            $input['@graph']   = $navObj; ?>       
	    		<script type="application/ld+json"><?php echo wp_json_encode( $input ); ?></script>
	        <?php }
	    }
	}
} 