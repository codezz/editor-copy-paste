<?php

namespace ECCP;

class Plugin {
	public function __construct() {

		add_action( 'plugins_loaded', [ $this, 'init' ] );

	}

	public function init() {
		add_filter( 'tiny_mce_before_init', [ $this, 'enable_tmce_paste' ], 1, 2 );
		add_filter( 'teeny_mce_before_init', [ $this, 'enable_tmce_paste' ], 1, 2 );

		add_filter( 'teeny_mce_plugins', function ( $args ) {
			$args[] = 'paste';

			return $args;
		} );
		add_filter( 'wp_insert_post_data', [ $this, 'change_post_data_before_save' ], 1, 2 );
		add_action( 'elementor/editor/after_save', [ $this, 'save_elementor_image' ], 10, 2 );
	}

	public function enable_tmce_paste( $mceInit, $editor_id ) {

		$mceInit['paste_data_images']             = true;
		$mceInit['paste_enable_default_filters']  = false;
		$mceInit['paste_word_valid_elements']     = "sub,sup,span,b,strong,i,em,h1,h2,h3,h4,h5,h6,p,ol,ul,li,a,br,img,table,tbody,td,tfoot,th,thead,tr,del,ins,dl,dt,dd";
		$mceInit['paste_webkit_styles']           = "all";
		$mceInit['paste_retain_style_properties'] = "all";

		$mceInit['paste_preprocess'] = "function(pl,o){
             
              // remove the following tags completely:
                o.content = o.content.replace(/<\/*(div|applet|area|article|aside|base|basefont|bdi|bdo|body|button|canvas|command|datalist|details|font|footer|frame|frameset|head|header|hgroup|hr|html|iframe|keygen|link|map|mark|menu|meta|meter|nav|noframes|noscript|object|optgroup|output|param|progress|rp|rt|ruby|script|section|source|style|summary|time|title|track|video|wbr)[^>]*>/gi,'');
              
              // remove all attributes from these tags:
               o.content = o.content.replace(/<(table|tbody|tr|td|th|b|font|strong|i|em|h1|h2|h3|h4|h5|h6|hr|ul|li|ol|code|blockquote|address|dir|dt|dd|dl|big|cite|del|dfn|ins|kbd|q|samp|small|s|strike|sub|sup|tt|u|var|caption) [^>]*>/gi,'<$1>');
                
              // replace br tag with p tag:
                //if (o.content.match(/<br[\/\s]*>/gi)) {
                //  o.content = o.content.replace(/<br[\s\/]*>/gi,'</p><p>');
                //}
              
              // replace div tag with p tag:
                o.content = o.content.replace(/<(\/)*div[^>]*>/gi,'<$1p>');
              
              // remove double paragraphs:
                o.content = o.content.replace(/<\/p>[\s\\r\\n]+<\/p>/gi,'</p></p>');
                o.content = o.content.replace(/<\<p>[\s\\r\\n]+<p>/gi,'<p><p>');
                o.content = o.content.replace(/<\/p>[\s\\r\\n]+<\/p>/gi,'</p></p>');
                o.content = o.content.replace(/<\<p>[\s\\r\\n]+<p>/gi,'<p><p>');
                o.content = o.content.replace(/(<\/p>)+/gi,'</p>');
                o.content = o.content.replace(/(<p>)+/gi,'<p>');
               
               //add subscript
               o.content = o.content.replace(/<span(?:[^<])*style=\"[^\"]*vertical-align: *sub[^\"]*\"(?: [\w-_]+=\"[^\"]+\")*>(.*?)<\/span>/gim, '<sub>$1</sub>');
  
               //add superscript
               o.content = o.content.replace(/<span(?:[^<])*style=\"[^\"]*vertical-align: *super[^\"]*\"(?: [\w-_]+=\"[^\"]+\")*>(.*?)<\/span>/gim, '<sup>$1</sup>');

               //add italic
               o.content = o.content.replace(/<span(?:[^<])*style=\"[^\"]*font-style: *italic[^\"]*\"(?: [\w-_]+=\"[^\"]+\")*>(.*?)<\/span>/gim, '<i>$1</i>');
  
                // Add headings
               o.content = o.content.replace(/<p(?:[^<])*aria-level=\"([1-6])\"(?:[^>])*>(.*?)<\/p>/gi,'<h$1>$2</h$1>');
               
                // get images
                var reg = /img(?:[^<])*src=\"([^\"]*)\"(?:[^>])*>/g;
                var result;
                
                var convertImgToDataURLviaCanvas = function(url, callback) {
                  var img = new Image();                  
                  img.crossOrigin = 'Anonymous';
                
                  jQuery(img).one('load', function() {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');
                    var dataURL;
                    canvas.height = this.height == 0 ? 600 : this.height;
                    canvas.width = this.width == 0 ? 600 : this.width;
                    ctx.drawImage(img, 0, 0);
                    dataURL = canvas.toDataURL();
                    callback(dataURL);
                    canvas = null;
                  }).each(function() {
                      if(this.complete) {
                          $(this).trigger('load'); // For jQuery >= 3.0 
                      }
                    });
                
                  img.src = url;
                }

                o.content.replace(reg, (matched, index, original) => {
                
                    //fix some img copy paste
                      if ( index.startsWith('image') ) {
                        return 'data:' + index;
                    }
                    
                    if ( index.startsWith('file') ) {
                        return convertImgToDataURLviaCanvas( index, function( base64_data ) {
                            return base64_data;
                        } );
                    }
                      return index;
                });
                
               //remove span tag
               o.content = o.content.replace(/<\/*(span)[^>]*>/gi,'');
                
                // keep text align
               o.content = o.content.replace(/<p(?:[^<])*text-align: *center(?:[^>]*)>(.*?)<\/p>/gim, '<center>$1</center>');
               
               o.content = o.content.replace(/<p(?:[^<])*text-align: *right(?:[^>]*)>(.*?)<\/p>/gim, '<right>$1</right>');
                
               // remove all attributes from these tags:
               o.content = o.content.replace(/<(p) [^>]*>/gi,'<$1>');
               
               //handle centering
               o.content = o.content.replace(/<center>(.*?)<\/center>/gi,'<p style=\"text-align: center;\">$1</p>');
               o.content = o.content.replace(/<right>(.*?)<\/right>/gi,'<span style=\"text-align: right;\">$1</span>');
                     
               //handle image style removing && remove anchor underline
              o.content = o.content.replace(/<a(?:[^<])*href=(?:\"|')(.*?)(?:\"|')(?:[^<])*>(.*?)<\/a>/gi, function (matched, first, second) {
                return '<a href=\"'+first+'\">'+second.replace('<u>','').replace('<\/u>','')+'</a>';
              });
               
               //remove html comments
               o.content = o.content.replace(/<\!--[\s\S]*?-->/gi, '');
        }";

		return $mceInit;
	}

	/**
	 * @param $data
	 * @param $postarr
	 *
	 * @return mixed
	 */
	public function change_post_data_before_save( $data, $postarr ) {

		// Stop anything from happening if revision
		if ( wp_is_post_revision( $postarr['ID'] ) ) {
			return $data;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			return $data;
		}

		$data['post_content'] = $this->replace_base64_image( $data['post_content'], $postarr['ID'] );

		return $data;
	}

	/**
	 * @param $post_id
	 * @param $editor_data
	 */
	public function save_elementor_image( $post_id, $editor_data ) {

		// Stop anything from happening if revision
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id );

		if ( $document ) {
			$elements = \Elementor\Plugin::$instance->db->iterate_data( $editor_data, function ( $element ) use ( $post_id ) {
				if ( empty( $element['widgetType'] ) ) {
					return $element;
				}

				if ( $element['widgetType'] === 'text-editor' && isset( $element['settings'], $element['settings']['editor'] ) ) {
					$element['settings']['editor'] = $this->replace_base64_image( $element['settings']['editor'], $post_id );
				}

				return $element;

			} );

			if ( is_array( $elements ) ) {
				$editor_data = $document->get_elements_raw_data( $elements );
				$json_value  = wp_slash( wp_json_encode( $editor_data ) );
				update_metadata( 'post', $post_id, '_elementor_data', $json_value );
			}
		}

	}

	private function replace_base64_image( $content, $post_id ) {

		return preg_replace_callback(
			'|(?:data:)?([a-zA-Z0-9\/]+);base64,([0-9a-zA-Z\/+=]*)|',
			function ( $matches ) use ( $post_id ) {

				if ( isset( $matches[1], $matches[2] ) && ! empty( $matches[1] ) && ! empty( $matches[2] ) ) {

					if ( $url = get_transient( 'base64img_' . md5( $matches[2] ) ) ) {
						return $url;
					}

					$decoded = base64_decode( $matches[2] );

					$upload_dir  = wp_upload_dir();
					$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

					$filename        = 'Image-' . mt_rand() . '.png';
					$hashed_filename = md5( $filename . microtime() ) . '_' . $filename;
					$tmp_name        = $upload_path . $hashed_filename;

					file_put_contents( $tmp_name, $decoded );

					if ( ! function_exists( 'wp_handle_sideload' ) ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}

					$file             = [];
					$file['error']    = '';
					$file['tmp_name'] = $tmp_name;
					$file['name']     = $filename;
					$file['type']     = 'image/png';
					$file['size']     = filesize( $upload_path . $hashed_filename );

					$file_return = wp_handle_sideload( $file, array( 'test_form' => false ) );
					// var_dump( $file_return );

					$filename   = $file_return['file'];
					$attachment = array(
						'post_mime_type' => $file_return['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
						'guid'           => $upload_dir['url'] . '/' . basename( $filename )
					);
					$attach_id  = wp_insert_attachment( $attachment, $filename, $post_id );

					// On REST requests.
					if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
						require_once ABSPATH . '/wp-admin/includes/image.php';
					}

					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id, $attach_data );

					unlink( $tmp_name );

					set_transient( 'base64img_' . md5( $matches[2] ), $file_return['url'] );

					return $file_return['url'];

				}

				return '';

			},
			$content
		);

	}
}