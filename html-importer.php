<?php

if ( !defined( 'WP_LOAD_IMPORTERS' ) )
	return;

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( !class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require_once $class_wp_importer;
}

if ( class_exists( 'WP_Importer' ) ) {
class HTML_Import extends WP_Importer {

	var $posts = array ();
	var $file;

	function header() {
		echo '<div class="wrap">';
		screen_icon();
		echo '<h2>'.__( 'HTML Importer', 'import-html-pages' ).'</h2>';
	}

	function footer() {
		echo '</div>';
	}
	
	function greet() {
		$options = get_option( 'html_import' );
		?>
		<div class="narrow">
		<?php 
		if ( $options['firstrun'] === true ) {
		echo '<p>'.sprintf( __( 'It looks like you have not yet visited the <a href="%s">HTML Import options page</a>. Please do so now! You need to specify which portions of your HTML files should be imported before you proceed.', 'import-html-pages' ), 'options-general.php?page=html-import.php' ).'</p>'; 
		} 
		else { ?>
		<h4><?php _e( 'What are you importing today?' ); ?></h4>
		<form enctype="multipart/form-data" method="post" action="admin.php?import=html&amp;step=1">
		<p>
		<label><input name="import_files" id="import_files" type="radio" value="directory" checked="checked"
		onclick="javascript: jQuery( '#single' ).hide( 'fast' ); jQuery( '#directory' ).show( 'fast' );"  />	
			<?php _e( 'a directory of files', 'import-html-pages' ); ?></label> &nbsp; &nbsp;	
		<label><input name="import_files" id="import_files" type="radio" value="file" 
		onclick="javascript: jQuery( '#directory' ).hide( 'fast' ); jQuery( '#single' ).show( 'fast' );" />
			<?php _e( 'a single file', 'import-html-pages' ); ?></label>
		</p>
		
		<p id="single">
		<label for="import"><?php _e( 'Choose an HTML file from your computer:', 'import-html-pages' ); ?></label>
		<input type="file" id="import" name="import" size="25" />
		</p>
		
		<p id="directory">
			<?php
			printf( __( 'Your files will be imported from <kbd>%s</kbd>. <a href="%s">Change directories</a>.', 'import-html-pages' ),
			esc_html( $options['root_directory'] ), 'options-general.php?page=html-import.php' ); ?>
		</p>
		
		<input type="hidden" name="action" value="save" />
		
		<p class="submit">
			<input type="submit" name="submit" class="button" value="<?php echo esc_attr( __( 'Submit', 'import-html-pages' ) ); ?>" />
		</p>
		<?php wp_nonce_field( 'html-import' ); ?>
		</form>
		</div>
	<?php } // else
	}
	
	function regenerate_redirects() {
		$newredirects = ''; 
		$imported = get_posts( array( 'meta_key' => 'URL_before_HTML_Import', 'post_type' => 'any', 'post_status' => 'any', 'numberposts' => '-1' ) );
		foreach( $imported as $post ) { 
			$old = get_post_meta( $post->ID, 'URL_before_HTML_Import', true );
			$newredirects .= "Redirect\t".$old."\t".get_permalink( $post->ID )."\t[R=301,NC,L]\n";
		}
		if ( !empty( $newredirects ) ) { ?>
		<h3><?php _e( '.htaccess Redirects', 'import-html-pages' ); ?></h3>
		<p><?php _e( 'Copy these lines into your <kbd>.htaccess</kbd> <em>above</em> the WordPress section.', 'import-html-pages' ); ?></p>
		<textarea id="import-result"><?php echo $newredirects; ?></textarea>
		<h3><?php printf( __( 'All done! You can <a href="%s">change your permalink structure</a> and <a href="%s">regenerate the redirects again</a>, or <a href="%s">start over</a>.', 'import-html-pages' ), 'options-permalink.php', wp_nonce_url( 'admin.php?import=html&step=2', 'html_import_regenerate' ), 'admin.php?import=html' ) ?></h3>
		<?php }
		else _e( 'No posts were found with the URL_before_HTML_Import custom field. Could not generate rewrite rules.', 'import-html-pages' );
	}

	function fix_hierarchy( $postid, $path ) {
		$options = get_option( 'html_import' );
		$parentdir = rtrim( $this->parent_directory( $path ), '/' );
		
		// create array of parent directories, starting with the index file's parent and moving up to the root directory
		while ( $parentdir != $options['root_directory'] ) {
			$parentarr[] = $parentdir;
			$parentdir = rtrim( $this->parent_directory( $parentdir ), '/' );
		}
		// reverse the array so we start at the root -- this way the parents can be found when we search in $this->get_post
		$parentarr = array_reverse( $parentarr );
		
		// DEBUG
		// echo '<pre>'.print_r( $parentarr, true ).'</pre>';
		
		foreach ( $parentarr as $parentdir ) {
			$parentID = array_search( $parentdir, $this->filearr );
			if ( $parentID === false )
				$this->get_post( $parentdir, true );
		}
		
		// now fix the parent ID of the original index file ( in $postid )
		// it's the next to last element in the array we want. ( The last one is the index file. ) If this doesn't exist, we don't need to fix the parent.
		$grandparent = count( $parentarr )-2;
		if ( isset( $parentarr[$grandparent] ) ) {
			$parentdir = $parentarr[$grandparent];
			$my_post['ID'] = $postid;
			$my_post['post_parent'] = array_search( $parentdir, $this->filearr );
		
			//echo "\n<pre>The parent of $postid should be ".$my_post['post_parent']."</pre>"; 
		
			if ( !empty( $my_post['post_parent'] ) )
				wp_update_post( $my_post );
		}
	}

	function parent_directory( $path ) {
		$win = false;
		if ( strpos( $path, '\\' ) !== FALSE ) {
			$win = true;
	    	$path = str_replace( '\\', '/', $path );
		}
	    if ( substr( $path, strlen( $path ) - 1 ) != '/' ) $path .= '/'; 
	    $path = substr( $path, 0, strlen( $path ) - 1 );
	    $path = substr( $path, 0, strrpos( $path, '/' ) ) . '/';
	    if ( $win ) $path = str_replace( '/', '\\', $path );
	    return $path;
	}
	
	function fix_internal_links( $content, $id ) {		
		// find all href attributes
		preg_match_all( '/<a[^>]* href=[\'"]?([^>\'" ]+ )/i', $content, $matches );
		for ( $i=0; $i<count( $matches[0] ); $i++ ) {
			$hrefs[] = $matches[1][$i];
		}
		if ( !empty( $hrefs ) ) {
			//echo '<p>Looking in '.get_permalink( $id ).'</p>';
			$options = get_option( 'html_import' );
			$site = $options['old_url'];
			$rootdir = $options['root_directory'];
			foreach ( $hrefs as $href ) {
				if ( '#' != substr( $href, 0, 1 ) && 'mailto:' != substr( $href, 0, 7 ) ) { // skip anchors and mailtos
					if ( preg_match( '/^http:\/\//', $href ) || preg_match( '/^https:\/\//', $href ) ) {
						// if it's an internal link, let's get a local file path
						$linkpath = str_replace( $site, $rootdir, $href );		
					}
					// href="/images/foo"
					elseif ( '/' == substr( $href, 0, 1 ) ) { 
						$linkpath = $rootdir . $href;
						$linkpath = $this->remove_dot_segments( $linkpath );
					}
					// href="../../images/foo" or href="images/foo"
					else {
						// we need to know where we are in the hierarchy 
						$oldpath = get_post_meta( $id, 'URL_before_HTML_Import', true );
						$oldpath = str_replace( $site, $rootdir, $oldpath );
						//echo '<p>Old path: '.$oldpath;
						$oldfile = strrchr( $oldpath, '/' );
						$linkpath = str_replace( $oldfile, '/'.$href, $oldpath );
						$linkpath = $this->remove_dot_segments( $linkpath );
						//echo ' Link path: '.$linkpath . '</p>';
					}
			
					$linkpath = rtrim( $linkpath, '/' );
					// DEBUG
					//echo '<p>Old link: '.$href.' Full path: '.$linkpath;
					
					// now replace the old URL with the new permalink
					$postkey = array_search( $linkpath, $this->filearr );
					
					// DEBUG
					//echo ' Post ID:'.$postkey.'.</p>';
					if ( !empty( $postkey ) ) {
						
						// DEBUG
						//echo '<p>I think '.$linkpath.' has moved to '.get_permalink( $postkey ).'.</p>';
						$content = str_replace( $href, get_permalink( $postkey ), $content );
					}
				} // if #/mailto
			} // foreach
		} // if empty
		return $content;
	}
	
	function remove_dot_segments( $path ) {
		$inSegs  = preg_split( '!/!u', $path );
		$outSegs = array();
		foreach ( $inSegs as $seg )
		{
		    if ( empty( $seg ) || $seg == '.' )
		        continue;
		    if ( $seg == '..' )
		        array_pop( $outSegs );
		    else
		        array_push( $outSegs, $seg );
		}
		$outPath = implode( '/', $outSegs );
		if ( isset( $path[0] ) && $path[0] == '/' )
		    $outPath = '/' . $outPath;
		if ( $outPath != '/' &&
		    ( mb_strlen( $path )-1 ) == mb_strrpos( $path, '/', 'UTF-8' ) )
		    $outPath .= '/';
		$outPath = str_replace( 'http:/', 'http://', $outPath );
		$outPath = str_replace( 'https:/', 'https://', $outPath );
		$outPath = str_replace( ':///', '://', $outPath );
		return rawurldecode( $outPath );
	}
	
	function clean_html( $string, $allowtags = NULL, $allowattributes = NULL ) {
		// from: http://us3.php.net/manual/en/function.strip-tags.php#91498
	    $string = strip_tags( $string,$allowtags );
	    if ( !is_null( $allowattributes ) ) {
	        if( !is_array( $allowattributes ) )
	            $allowattributes = explode( ",",$allowattributes );
	        if( is_array( $allowattributes ) )
	            $allowattributes = implode( " )( ?<!",$allowattributes );
	        if ( strlen( $allowattributes ) > 0 )
	            $allowattributes = "( ?<!".$allowattributes." )";
	        $string = preg_replace_callback( "/<[^>]*>/i",create_function( 
	            '$matches',
	            'return preg_replace( "/ [^ =]*'.$allowattributes.'=( \"[^\"]*\"|\'[^\']*\' )/i", "", $matches[0] );'   
	        ),$string );
	    }
		// reduce line breaks and remove empty tags
		$string = str_replace( array( "\n", "\r", "\t" ), '', $string );
		$string = preg_replace( "/<[^\/>]*>( [\s]? )*<\/[^>]*>/", ' ', $string );
		// get rid of remaining newlines; basic HTML cleanup
		$string = str_replace( '&#13;', ' ', $string ); 
		$string = preg_replace_callback( '|<( /?[A-Z]+ )|', create_function( '$match', 'return "<" . strtolower( $match[1] );' ), $string );
		$string = str_replace( '<br>', '<br />', $string );
		$string = str_replace( '<hr>', '<hr />', $string );
		return $string;
	}
	
	function handle_accents() {
		// from: http://www.php.net/manual/en/domdocument.loadhtml.php#91513
		$content = $this->file;
		if ( !empty( $content ) && function_exists( 'mb_convert_encoding' ) ) {
			mb_detect_order( "ASCII,UTF-8,ISO-8859-1,windows-1252,iso-8859-15" );
            if ( empty( $encod ) )
                $encod = mb_detect_encoding( $content );
            	$headpos = mb_strpos( $content,'<head>' );
            if ( FALSE === $headpos )
                $headpos= mb_strpos( $content,'<HEAD>' );
            if ( FALSE !== $headpos ) {
                $headpos+=6;
                $content = mb_substr( $content,0,$headpos ) . '<meta http-equiv="Content-Type" content="text/html; charset='.$encod.'">' .mb_substr( $content,$headpos );
            }
            $content = mb_convert_encoding( $content, 'HTML-ENTITIES', $encod );
        }
		return $content;
	}
	
	function get_single_file( $txt = false ) {
		$importfile = file( $this->file ); // Read the file into an array
		$importfile = implode( '', $importfile ); // squish it
		// this strips whitespace out of <pre>. Need to find a better way to handle that. For now, leave it alone.
		//$this->file = str_replace( array ( "\r\n", "\r" ), "\n", $importfile );
		$this->file = $importfile;
		$this->get_post( '', false );
	}

	function get_files_from_directory( $rootdir ) {
		$options = get_option( 'html_import' );
		$dir_content = scandir( $rootdir );
	    foreach( $dir_content as $key => $val ) {
	      set_time_limit( 30 );
	      $path = $rootdir.'/'.$val;
	      if( is_file( $path ) && is_readable( $path ) ) {
			$filename_parts = pathinfo( $path );
			$ext = '';
			if ( isset( $filename_parts['extension'] ) )
				$ext = strtolower( $filename_parts['extension'] );
			// allowed extensions only, please
			if ( !empty( $ext ) && in_array( $ext, $this->allowed ) ) {
				if ( filesize( $path ) > 0 ) {  // silently skip empty files
					// read the HTML file 
					$contents = @fopen( $path );  // read entire file
					if ( empty( $contents ) ) 
						$contents = @file_get_contents( $path ); 
					if ( !empty( $contents ) ) {	// silently skip files we can't open	
						$this->file = $contents;
						$this->get_post( $path, false ); // import the post
					}
				}
			}
	      }

	      elseif( is_dir( $path ) && is_readable( $path ) ) { 
	        if( !in_array( $val, $this->skip ) ) {
			  $createpage = array();
			  // get list of files in this directory only ( checking children )
				$files = scandir( $path );
				$exts = array();
				foreach ( $files as $file ) {
					$ext = '';
					$filename_parts = pathinfo( $file );
					if ( isset( $filename_parts['extension'] ) )
						$ext = strtolower( $filename_parts['extension'] );
					$ext = trim( $ext,'.' ); // dratted double dots
					if ( !empty( $ext ) ) $exts[] .= $ext;
				}

				// allowed extensions only, please. If there are files of the proper type, we should create a placeholder page
				$createpage = @array_intersect( $exts, $this->allowed ); // suppress warnings about not being an array

				if ( !empty( $createpage ) &&  is_post_type_hierarchical( $options['type'] ) ) { 
					$this->get_post( $path, true );
				}
				
				// handle the files in this directory -- recurse!
				$this->get_files_from_directory( $path ); 
	        }
	      }
	    } // end foreach
	}
	
	function get_post( $path = '', $placeholder = false ) {
		// this gets the content AND imports the post because we have to build $this->filearr as we go so we can find the new post IDs of files' parent directories
		set_time_limit( 540 );
		$options = get_option( 'html_import' );
		$updatepost = false;
		
		if ( $placeholder ) {
			$title = trim( strrchr( $path,'/' ),'/' );
			$title = str_replace( '_', ' ', $title );
			$title = str_replace( '-', ' ', $title );
			$my_post['post_title'] = ucwords( $title );
			
			if ( isset( $options['preserve_slugs'] ) && '1' == $options['preserve_slugs'] ) {
				$filename = basename( $path );
				$my_post['post_name'] = substr( $filename,0,strrpos( $filename,'.' ) );
			}
			
			if ( $options['timestamp'] == 'filemtime' )
				$date = filemtime( $path );
			else $date = time();
			$my_post['post_date'] = date( "Y-m-d H:i:s", $date );
			$my_post['post_date_gmt'] = date( "Y-m-d H:i:s", $date );

			$my_post['post_type'] = $options['type'];

			$parentdir = rtrim( $this->parent_directory( $path ), '/' );
			
			$my_post['post_parent'] = array_search( $parentdir, $this->filearr );
			if ( $my_post['post_parent'] === false )
				$my_post['post_parent'] = $options['root_parent'];

			$my_post['post_content'] = '<!-- placeholder -->';
			$my_post['post_status'] = $options['status'];
			$my_post['post_author'] = $options['user'];
		}
		else {
			$doc = new DOMDocument();
			$doc->strictErrorChecking = false; // ignore invalid HTML, we hope
			$doc->preserveWhiteSpace = false;  
			$doc->formatOutput = false;  // speed this up
			if ( !empty( $options['encode'] ) ) {  // we have to deal with character encoding BEFORE calling loadHTML() - eureka!
				$content = $this->handle_accents();
				@$doc->loadHTML( $content );
			}
			else
				@$doc->loadHTML( $this->file );
			$xml = @simplexml_import_dom( $doc );
			// bail out if we got no XML to work with
			if ( $xml === NULL )
			    return;
			// avoid asXML errors when it encounters character range issues
			libxml_clear_errors();
			libxml_use_internal_errors( false );
			
			// start building the WP post object to insert
			$my_post = array();
			
			// title
			if ( $options['import_title'] == "region" ) {
				// appending strings unnecessarily so this plugin can be edited in Dreamweaver if needed
				$titlematch = '/<'.'!-- InstanceBeginEditable name="'.$options['title_region'].'" --'.'>( .* )<'.'!-- InstanceEndEditable --'.'>/isU';
				preg_match( $titlematch, $this->file, $titlematches );
				$my_post['post_title'] = strip_tags( trim( $titlematches[1] ) );
			}
			else if ( $options['import_title'] == "filename" ) {
				$path_split = explode( '/',$path );
				$file_name = trim( end( $path_split ) );
				$file_name = preg_replace( '/\.[^.]*$/', '', $file_name ); // remove extension
				$parent_directory = trim( prev( $path_split ) );
				
				if( basename( $path ) == $options['index_file'] ) {
					$title = $parent_directory;
				} else {
					$title = $file_name;
				}
				$title = str_replace( '_', ' ', $title );
				$title = str_replace( '-', ' ', $title );
				$my_post['post_title'] = ucwords( $title );
			}
			else { // it's a tag
				$titletag = $options['title_tag'];
				$titletagatt = $options['title_tagatt'];
				$titleattval = $options['title_attval'];
				$titlequery = '//' . $titletag;
				if ( !empty( $titletagatt ) )
					$titlequery .= '[@'.$titletagatt.'="'.$titleattval.'"]';
				$title = $xml->xpath( $titlequery );
				if ( isset( $title[0] ) && is_object( $title[0] ) )
					$title = $title[0]->asXML(); // asXML() preserves HTML in content
				else { // fallback
					$title = $xml->xpath( '//title' );
					if ( isset( $title[0] ) )
						$title = $title[0];
					if ( empty( $title ) )
						$title = '';
					else
						$title = ( string )$title;
				}
				// last resort: filename
				if ( empty( $title ) ) {
					$path_split = explode( '/',$path );
					$title = trim( end( $path_split ) );
				}	
				// replace break tags with spaces before we strip tags, to avoid accidentally concatenating words
				$title = str_replace( '<br>', ' ', $title );
				$my_post['post_title'] = trim( strip_tags( $title ) );
			}
		
			$remove = $options['remove_from_title'];
			if ( !empty( $remove ) )
				$my_post['post_title'] = str_replace( $remove, '', $my_post['post_title'] );
			
			// DEBUG
			//echo '<pre>'.$my_post['post_title'].'</pre>'; exit;
			
			// slug
			if ( isset( $options['preserve_slugs'] ) && '1' == $options['preserve_slugs'] ) {
				// there is no path when we're working with a single uploaded file instead of a directory
				if ( '' == trim( $path ) )
					$filename = $this->filename;
				else
					$filename = basename( $path );
				$my_post['post_name'] = substr( $filename,0,strrpos( $filename,'.' ) );
			}
			
			// post type
			$my_post['post_type'] = $options['type'];
		
			if ( is_post_type_hierarchical( $my_post['post_type'] ) ) {
				if ( '' == trim( $path ) ) 
					$my_post['post_parent'] = $options['root_parent'];
				else {
					$parentdir = rtrim( $this->parent_directory( $path ), '/' );
					$my_post['post_parent'] = array_search( $parentdir, $this->filearr );
					if ( $my_post['post_parent'] === false )
						$my_post['post_parent'] = $options['root_parent'];
				}
			}
		
			// date
			if ( $options['timestamp'] == 'filemtime' && !empty( $path ) ) {
				$date = filemtime( $path );
				$my_post['post_date'] = date( "Y-m-d H:i:s", $date );
				$my_post['post_date_gmt'] = date( "Y-m-d H:i:s", $date );
			}
			else if ( $options['timestamp'] == 'customfield' ) {
				if ( $options['import_date'] == "region" ) {
					// appending strings unnecessarily so this plugin can be edited in Dreamweaver if needed
					$datematch = '/<'.'!-- InstanceBeginEditable name="'.$options['date_region'].'" --'.'>( .* )<'.'!-- InstanceEndEditable --'.'>/isU';
					preg_match( $datematch, $this->file, $datematches );
					$date = $datematches[1];
				}
				else { // it's a tag
					$tag = $options['date_tag'];
					$tagatt = $options['date_tagatt'];
					$attval = $options['date_attval'];
					$xquery = '//'.$tag;
					if ( !empty( $tagatt ) )
						$xquery .= '[@'.$tagatt.'="'.$attval.'"]';
					$date = $xml->xpath( $xquery );
					if ( is_array( $date ) && isset( $date[0] ) && is_object( $date[0] ) ) {
						if ( isset( $date[0] ) && is_object( $date[0] ) )
							$stripdate = $date[0]->asXML(); // asXML() preserves HTML in content
						$date = strip_tags( $date[0] );
						$date = strtotime( $date );
						//echo $date; exit;
					}
					else { // fallback 
						$date = time();
					}

				}
			}
			else {
			 	$date = time();
			}
			$my_post['post_date'] = date( "Y-m-d H:i:s", $date );
			$my_post['post_date_gmt'] = date( "Y-m-d H:i:s", $date );

			// content
			if ( $options['import_content'] == "region" ) {
				// appending strings unnecessarily so this plugin can be edited in Dreamweaver if needed
				$contentmatch = '/<'.'!-- InstanceBeginEditable name="'.$options['content_region'].'" --'.'>( .* )<'.'!-- InstanceEndEditable --'.'>/isU';
				preg_match( $contentmatch, $this->file, $contentmatches );
				$my_post['post_content'] = $contentmatches[1];
			}
			else if ( $options['import_content'] == "file" ) { // import entire file
				$my_post['post_content'] = $this->file;
			}
			else { // it's a tag
				$tag = $options['content_tag'];
				$tagatt = $options['content_tagatt'];
				$attval = $options['content_attval'];
				$xquery = '//'.$tag;
				if ( !empty( $tagatt ) )
					$xquery .= '[@'.$tagatt.'="'.$attval.'"]';
				$content = $xml->xpath( $xquery );
				if ( is_array( $content ) && isset( $content[0] ) && is_object( $content[0] ) ) {
					$my_post['post_content'] = $content[0]->asXML(); // asXML() preserves HTML in content
				}
				else {  // fallback
					$content = $xml->xpath( '//body' );
					if ( is_array( $content ) && isset( $content[0] ) && is_object( $content[0] ) )
						$my_post['post_content'] = $content[0]->asXML();
					else
						$my_post['post_content'] = '';
				}
			}
			
			// $my_post['post_content'] = (string) $my_post['post_content'];
			
			if ( $options['title_inside'] )
				$my_post['post_content'] = str_replace( $title, '', $my_post['post_content'] );
					
			if ( !empty( $my_post['post_content'] ) ) {
				if ( !empty( $options['clean_html'] ) )
					$my_post['post_content'] = $this->clean_html( $my_post['post_content'], $options['allow_tags'], $options['allow_attributes'] );
			}
			
			// custom fields
			$customfields = array();
			foreach ( $options['customfield_name'] as $index => $fieldname ) {
				if ( !empty( $fieldname ) ) {
					if ( $options['import_field'][$index] == "region" ) {
						// appending strings unnecessarily so this plugin can be edited in Dreamweaver if needed
						$custommatch = '/<'.'!-- InstanceBeginEditable name="'.$options['customfield_region'][$index].'" --'.'>( .* )<'.'!-- InstanceEndEditable --'.'>/isU';
						preg_match( $custommatch, $this->file, $custommatches );
						if ( isset( $custommatches[1] ) )
							$customfields[$fieldname] = $custommatches[1];
					}
					else { // it's a tag
						$tag = $options['customfield_tag'][$index];
						$tagatt = $options['customfield_tagatt'][$index];
						$attval = $options['customfield_attval'][$index];
						$xquery = '//'.$tag;
						if ( !empty( $tagatt ) )
							$xquery .= '[@'.$tagatt.'="'.$attval.'"]';
						$content = $xml->xpath( $xquery );
						
						if ( is_array( $content ) && isset( $content[0] ) && is_object( $content[0] ) ) {
							$fieldcontent = $content[0]->asXML();
							if ( !empty( $options['customfield_html'][$index] ) && !empty( $options['clean_html'] ) ) {
								$fieldcontent = $content[0]->asXML();
								$fieldcontent = $this->clean_html( $fieldcontent, $options['allow_tags'], $options['allow_attributes'] );
								if ( !empty( $fieldcontent ) )
									$customfields[$fieldname] = $fieldcontent;
							}
							else {
								$fieldcontent = trim( strip_tags( $fieldcontent ) );
								if ( !empty( $fieldcontent ) )
									$customfields[$fieldname] = $fieldcontent;
							}
						}
					}
				}
			}
			
			// excerpt
			$excerpt = $options['meta_desc'];
			if ( !empty( $excerpt ) ) {
				$my_post['post_excerpt'] = $xml->xpath( '//meta[@name="description"]' );
				if ( isset( $my_post['post_excerpt'][0] ) )
					$my_post['post_excerpt'] = $my_post['post_excerpt'][0]['content'];
				if ( is_array( $my_post['post_excerpt'] ) )
					$my_post['post_excerpt'] = implode( '',$my_post['post_excerpt'] );
				$my_post['post_excerpt'] = ( string )$my_post['post_excerpt'];
			}
			
			// status
			$my_post['post_status'] = $options['status'];
			
			// author
			$my_post['post_author'] = $options['user'];
		}
		
		// if it's a single file, we can use a substitute for $path from here on
		if ( '' == trim( $path ) )
		 	$handle = __( "the uploaded file", 'import-html-pages' );
		else 
			$handle = $path;
		
		// see if the post already exists
		// but don't bother printing this message if we're doing an index file; we know its parent already exists
		if ( $post_id = post_exists( $my_post['post_title'], $my_post['post_content'], $my_post['post_date'] ) && basename( $path ) != $options['index_file'] )
			$this->table[] = "<tr><th class='error'>--</th><td colspan='3' class='error'> " . sprintf( __( "%s ( %s ) has already been imported", 'html-import-pages' ), $my_post['post_title'], $handle ) . "</td></tr>";
		
		// if we're doing hierarchicals and this is an index file of a subdirectory, instead of importing this as a separate page, update the content of the placeholder page we created for the directory
		$index_files = explode( ',',$options['index_file'] );
		if ( is_post_type_hierarchical( $options['type'] ) && dirname( $path ) != $options['root_directory'] && in_array( basename( $path ), $index_files ) ) {
			$post_id = array_search( dirname( $path ), $this->filearr );
			if ( $post_id !== 0 )
				$updatepost = true;
		}
		
		// find old path
		if ( '' !== trim( $path ) && !$updatepost ) {
			$url = esc_url( $options['old_url'] );
			$url = rtrim( $url, '/' );
			if ( !empty( $url ) ) 
				$old_path = str_replace( $options['root_directory'], $url, $path );
			else $old_path = $path;
		}
		
		// see if this file has been previously imported based on path
		$previous_import = get_posts( 
			array (
				'post_type' => $my_post['post_type'],
				'meta_key' => 'URL_before_HTML_Import',
				'meta_value' => $old_path,
				'posts_per_page' => 1
			)
		);
		
		// if so, set to update instead of import
		if ( !is_wp_error( $previous_import ) && !empty( $previous_import ) 
		 		&& $previous_import->post_title = $my_post['post_title'] ) {
			$post_id = $previous_import->ID;
			$updatepost = true;
		}
		
		// insert or update post
		if ( $updatepost ) { 
			$my_post['ID'] = $post_id; 
			wp_update_post( $my_post );
		}
		else 
			$post_id = wp_insert_post( $my_post );
		
		// handle errors
		if ( is_wp_error( $post_id ) )
			$this->table[] = "<tr><th class='error'>--</th><td colspan='3' class='error'> " . $post_id /* error msg */ . "</td></tr>";
		if ( !$post_id ) 
			$this->table[] = "<tr><th class='error'>--</th><td colspan='3' class='error'> " . sprintf( __( "Could not import %s. You should copy its contents manually.", 'html-import-pages' ), $handle ) . "</td></tr>";
		
		// if no errors, handle custom fields
		if ( isset( $customfields ) ) {
			foreach ( $customfields as $fieldname => $fieldvalue ) {
				// allow user to set tags via custom field named 'post_tag'
				if ( $fieldname == 'post_tag' )
					$customfieldtags = $fieldvalue;
				else
					add_post_meta( $post_id, $fieldname, $fieldvalue, true );
			}
		}
				
		// ... and all the taxonomies...
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects', 'and' );
		foreach ( $taxonomies as $tax ) {
			if ( isset( $options[$tax->name] ) )
				wp_set_post_terms( $post_id, $options[$tax->name], $tax->name, false );
		}
		if ( isset( $customfieldtags ) )
			wp_set_post_terms( $post_id, $customfieldtags, 'post_tag', false );
		
		// ...and set the page template, if any
		if ( isset( $options['page_template'] ) && !empty( $options['page_template'] ) )
			add_post_meta( $post_id, '_wp_page_template', $options['page_template'], true ); 
		
		// add redirects from old to new path; store old path in custom field
		if ( '' !== trim( $old_path ) && !$updatepost ) {
			$this->redirects .= "Redirect\t".$old_path."\t".get_permalink( $post_id )."\t[R=301,NC,L]\n";
			add_post_meta( $post_id, 'URL_before_HTML_Import', $old_path, true );
		}
		
		// store path so we can check for parents later ( even if it's empty; need that info for image imports ). 
		// Don't store the index file updates; they'll screw up the parent search, and they can use their parents' path anyway
		if ( !$updatepost )
			$this->filearr[$post_id] = $path;
		else {  // index files will have an incomplete hierarchy if there were empty directories in their path
			$this->fix_hierarchy( $post_id, $path );
		}
		
		// create the results table row AFTER fixing hierarchy
		if ( '' !== trim($path ) ) {
			if ( empty( $my_post['post_title'] ) )
				$my_post['post_title'] = __( '( no title )', 'html-import' );
			$this->table[$post_id] = " <tr><th>".$post_id."</th><td>".$path."</td><td>".get_permalink( $post_id ).'</td><td>
				<a href="post.php?action=edit&post='.$post_id.'">'.esc_html( $my_post['post_title'] )."</a></td></tr>";
		}
		else {
			$this->single_result = sprintf( __( 'Imported the file as %s.', 'import-html-pages' ), '<a href="post.php?action=edit&post='.$post_id.'">'.$my_post['post_title'].'</a>' );
		}
	}
	
	//Handle an individual file import. Borrowed almost entirely from dd32's Add From Server plugin
	function handle_import_media_file( $file, $post_id = 0 ) {
		// see if the attachment already exists
		$id = array_search( $file, $this->filearr );	
		if ( $id === false ) { 
		
			set_time_limit( 120 );
			$post = get_post( $post_id );
			$time = $post->post_date_gmt;
	
			// A writable uploads dir will pass this test. Again, there's no point overriding this one.
			if ( ! ( ( $uploads = wp_upload_dir( $time ) ) && false === $uploads['error'] ) )
				return new WP_Error( 'upload_error', $uploads['error'] );

			$filename = wp_unique_filename( $uploads['path'], basename( $file ) );

			// copy the file to the uploads dir
			$new_file = $uploads['path'] . '/' . $filename;
			if ( false === @copy( $file, $new_file ) )
				return new WP_Error( 'upload_error', sprintf( __( 'Could not find the right path to %s ( tried %s ). It could not be imported. Please upload it manually.', 'html-import-pages' ), basename( $file ), $file ) );
		//  DEBUG
		//	else
		//	 	printf( __( '<br /><em>%s</em> is being copied to the uploads directory as <em>%s</em>.', 'html-import-pages' ), $file, $new_file );
	
			// Set correct file permissions
			$stat = stat( dirname( $new_file ) );
			$perms = $stat['mode'] & 0000666;
			@chmod( $new_file, $perms );
			// Compute the URL
			$url = $uploads['url'] . '/' . $filename;

			//Apply upload filters
			$return = apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 'url' => $url, 'type' => wp_check_filetype( $file, null ) ) );
			$new_file = $return['file'];
			$url = $return['url'];
			$type = $return['type'];

			$title = preg_replace( '!\.[^.]+$!', '', basename( $file ) );
			$content = '';

			// use image exif/iptc data for title and caption defaults if possible
			if ( $image_meta = @wp_read_image_metadata( $new_file ) ) {
				if ( '' != trim( $image_meta['title'] ) )
					$title = trim( $image_meta['title'] );
				if ( '' != trim( $image_meta['caption'] ) )
					$content = trim( $image_meta['caption'] );
			}

			if ( $time ) {
				$post_date_gmt = $time;
				$post_date = $time;
			} 
			else {
				$post_date = current_time( 'mysql' );
				$post_date_gmt = current_time( 'mysql', 1 );
			}

			// Construct the attachment array
			$wp_filetype = wp_check_filetype( basename( $filename ), null );
			$attachment = array( 
				'post_mime_type' => $wp_filetype['type'],
				'guid' => $url,
				'post_parent' => $post_id,
				'post_title' => $title,
				'post_name' => $title,
				'post_content' => $content,
				'post_date' => $post_date,
				'post_date_gmt' => $post_date_gmt
			 );

			//Win32 fix:
			$new_file = str_replace( strtolower( str_replace( '\\', '/', $uploads['basedir'] ) ), $uploads['basedir'], $new_file );

	
			// Insert attachment
			$id = wp_insert_attachment( $attachment, $new_file, $post_id );
			if ( !is_wp_error( $id ) ) {
				$data = wp_generate_attachment_metadata( $id, $new_file );
				wp_update_attachment_metadata( $id, $data );
				$this->filearr[$id] = $file; // $file contains the original, absolute path to the file
			}
			
		} // if attachment already exists
		return $id;
	}
	
	// largely borrowed from the Add Linked Images to Gallery plugin, except we do a simple str_replace at the end
	function import_images( $id, $path ) {
		$post = get_post( $id );
		$options = get_option( 'html_import' );
		$result = array();
		$srcs = array();
		$content = $post->post_content;
		$title = $post->post_title;
		if ( empty( $title ) ) $title = __( '( no title )', 'html-import' );
		$update = false;
		
		// find all src attributes
		preg_match_all( '/<img[^>]* src=[\'"]?([^>\'" ]+)/i', $post->post_content, $matches );
		for ( $i=0; $i<count( $matches[0] ); $i++ ) {
			$srcs[] = $matches[1][$i];
		}
		
		// also check custom fields
		$custom = get_post_meta( $id, '_ise_old_sidebar', true );
		preg_match_all( '/<img[^>]* src=[\'"]?([^>\'" ]+)/i', $custom, $matches );
		for ( $i=0; $i<count( $matches[0] ); $i++ ) {
			$srcs[] = $matches[1][$i];
		}
		
		if ( !empty( $srcs ) ) {
			$count = count( $srcs );
			
			echo "<p>";
			printf( _n( 'Found %d image in <a href="%s">%s</a>. Importing... ', 'Found %d images in <a href="%s">%s</a>. Importing... ', $count, 'html-import-pages' ), $count, get_permalink( $post->ID ), $title );
			foreach ( $srcs as $src ) {
				// src="http://foo.com/images/foo"
				if ( preg_match( '/^http:\/\//', $src ) || preg_match( '/^https:\/\//', $src ) ) { 
					$imgpath = $src;			
				}
				// src="/images/foo"
				elseif ( '/' == substr( $src, 0, 1 ) ) { 
					$imgpath = $options['root_directory'] . $src;
				}
				// src="../../images/foo" or src="images/foo" or no $path
				else { 
					if ( empty( $path ) ) 
						$imgpath = $options['root_directory']. '/' . $src;
					else
						$imgpath = ( is_file( $path ) ? dirname( $path ) : $path ) . '/' . $src;
				}
				// intersect base path and src, or just clean up junk
				$imgpath = $this->remove_dot_segments( $imgpath );
			 
				//  load the image from $imgpath
				$imgid = $this->handle_import_media_file( $imgpath, $id );
				if ( is_wp_error( $imgid ) )
					echo '<span class="attachment_error">'.$imgid->get_error_message().'</span>';
				else {
					$imgpath = wp_get_attachment_url( $imgid );
			
					//  replace paths in the content
					if ( !is_wp_error( $imgpath ) ) {			
						$content = str_replace( $src, $imgpath, $content );
						$custom = str_replace( $src, $imgpath, $custom );
						$update = true;
					}
					
				} // is_wp_error else
				
			} // foreach
			
			// update the post only once
			if ( $update == true ) {
				$my_post = array();
				$my_post['ID'] = $id;
				$my_post['post_content'] = $content;
				wp_update_post( $my_post );
			}
			
			_e( 'done.', 'html-import-images' );
			echo '</p>';
			flush();
		} // if empty
	}
	
	function import_documents( $id, $path ) {
		$post = get_post( $id );
		$options = get_option( 'html_import' );
		$result = $srcs = array();
		$content = $post->post_content;
		$title = $post->post_title;
		if ( empty( $title ) ) $title = __( '( no title )', 'html-import' );
		$update = false;
		$mimes = explode( ',', $options['document_mimes'] );
				
		// find all href attributes
		preg_match_all( '/<a[^>]* href=[\'"]?([^>\'" ]+)/i', $content, $matches );
		for ( $i=0; $i<count( $matches[0] ); $i++ ) {
			$hrefs[] = $matches[1][$i];
		}
		if ( !empty( $hrefs ) ) {
			$count = count( $hrefs );
			
			echo "<p>";
			printf( _n( 'Found %d link in <a href="%s">%s</a>. Checking file types... ', 'Found %d links in <a href="%s">%s</a>. Checking file types... ', $count, 'html-import-pages' ), $count, get_permalink( $post->ID ), $title );
			
			//echo '<p>Looking in '.get_permalink( $id ).'</p>';
			$options = get_option( 'html_import' );
			$site = $options['old_url'];
			$rootdir = $options['root_directory'];
			foreach ( $hrefs as $href ) {
				$linkpath = '';
				if ( '#' != substr( $href, 0, 1 ) && 'mailto:' != substr( $href, 0, 7 ) ) { // skip anchors and mailtos
					if ( preg_match( '/^http:\/\//', $href ) || preg_match( '/^https:\/\//', $href ) ) {
						// is it a link to something on this server?
						if ( stripos( $site, $href ) !== false )
							// if it's an internal link, let's get a local file path
							$linkpath = str_replace( $site, $rootdir, $href );		
					}
					// href="/images/foo"
					elseif ( '/' == substr( $href, 0, 1 ) ) { 
						$linkpath = $rootdir . $href;
						$linkpath = $this->remove_dot_segments( $linkpath );
					}
					// href="../../images/foo" or href="images/foo"
					else {
						// we need to know where we are in the hierarchy 
						$oldpath = get_post_meta( $id, 'URL_before_HTML_Import', true );
						$oldpath = str_replace( $site, $rootdir, $oldpath );
						// DEBUG
						//echo '<p>Old path: '.$oldpath;
						$oldfile = strrchr( $oldpath, '/' );
						$linkpath = str_replace( $oldfile, '/'.$href, $oldpath );
						$linkpath = $this->remove_dot_segments( $linkpath );
						// DEBUG
						//echo ' Link path: '.$linkpath . '</p>';
					}
			
					if ( !empty( $linkpath ) ) {  // then we found an internal link
						$linkpath = rtrim( $linkpath, '/' );
						// DEBUG
						//echo '<p>Old link: '.$href.' Full path: '.$linkpath;
						
						$filename_parts = explode( ".",$linkpath );
						$ext = strtolower( $filename_parts[count( $filename_parts ) - 1] );
						
						if ( in_array( $ext, $mimes ) ) {  // allowed upload types only
							echo '<br />Importing '.ltrim( strrchr( $linkpath, '/' ), '/' ).'... ';
							//  load the file from $linkpath
							$fileid = $this->handle_import_media_file( $linkpath, $id );
							if ( is_wp_error( $fileid ) )
								echo '<span class="attachment_error">'.$fileid->get_error_message().'</span>';
							else {
								$filepath = wp_get_attachment_url( $fileid );

								//  replace paths in the content
								if ( !is_wp_error( $filepath ) ) {			
									$content = str_replace( $href, $filepath, $content );
									$update = true;
								}

							} // is_wp_error $fileid
						} // if in array
					
						
					} // if empty linkpath
				} // if #/mailto
			} // foreach
			
			// update the post only once
			if ( $update == true ) {
				$my_post = array();
				$my_post['ID'] = $id;
				$my_post['post_content'] = $content;
				wp_update_post( $my_post );
			}
			
			_e( 'done.', 'html-import-images' );
			echo '</p>';
			flush();
		} // if empty $hrefs
	}
	
	function find_internal_links() {
		echo '<h2>'.__( 'Fixing relative links...', 'import-html-pages' ).'</h2>';
		echo '<p>'.__( 'The importer is searching your imported posts for links. This might take a few minutes.', 'import-html-pages' ).'</p>';
		
		$fixedlinks = array(); 
		foreach ( $this->filearr as $id => $path ) {
			$new_post = array();
			$post = get_post( $id );
			$new_post['ID'] = $post->ID;
			$new_post['post_content'] = $this->fix_internal_links( $post->post_content, $post->ID );
		
			if ( !empty( $new_post['post_content'] ) )
				wp_update_post( $new_post );
			$fixedlinks[] .= $post->ID;
		}
		if ( !empty( $fixedlinks ) ) { ?>
		<h3><?php _e( 'All done!', 'import-html-pages' ); ?></h3>
		<?php }
		else _e( 'No posts were found with the URL_before_HTML_Import custom field. Could not search for links.', 'import-html-pages' );
		// DEBUG
		//echo '<pre>'.print_r( $this->filearr, true ).'</pre>';
	}
	
	function find_images() {
		echo '<h2>'.__( 'Importing images...', 'import-html-pages' ).'</h2>';
		$results = '';
		foreach ( $this->filearr as $id => $path ) {
			$results .= $this->import_images( $id, $path );
		}
		if ( !empty( $results ) )
			echo $results;
		echo '<h3>';
		printf( __( 'All done. <a href="%s">Go to the Media Library.</a>' ), 'media.php' );
		echo '</h3>';
		// DEBUG
		//echo '<pre>'.print_r( $this->filearr, true ).'</pre>';
	}
	
	function find_documents() {
		echo '<h2>'.__( 'Importing media files...', 'import-html-pages' ).'</h2>';
		echo '<p>'.__( 'The importer is searching your imported posts for links to media files. This might take a few minutes.', 'import-html-pages' ).'</p>';
		
		$results = '';
		foreach ( $this->filearr as $id => $path ) {
			$results .= $this->import_documents( $id, $path );
		}
		if ( !empty( $results ) )
			echo $results;
		echo '<h3>';
		printf( __( 'All done. <a href="%s">Go to the Media Library.</a>' ), 'media.php' );
		echo '</h3>';
		// DEBUG
		//echo '<pre>'.print_r( $this->filearr, true ).'</pre>';
	}
	
	function print_results( $posttype ) {
		if ( !empty( $this->single_result ) )
			echo $this->single_result;
		else {
			?>
			<table class="widefat page fixed" id="importing" cellspacing="0">
			<thead><tr>
			<th id="id"><?php _e( 'ID', 'import-html-pages' ); ?></th>
			<th><?php _e( 'Old path', 'import-html-pages' ); ?></th>
			<th><?php _e( 'New path', 'import-html-pages' ); ?></th>
			<th><?php _e( 'Title', 'import-html-pages' ); ?></th>
			</tr></thead><tbody> <?php foreach ( $this->table as $row ) echo $row; ?> </tbody></table> 
		
			<?php
			flush();
			
			if ( !empty( $this->redirects ) ) { ?>
			<h3><?php _e( '.htaccess Redirects', 'import-html-pages' ); ?></h3>
			<textarea id="import-result"><?php echo $this->redirects; ?></textarea>
			<p><?php printf( __( 'If you need to <a href="%s">change your permalink structure</a>, you can <a href="%s">regenerate the redirects</a> ( or do it later from the <a href="%s">options screen</a> under Tools ).', 'import-html-pages' ), 'options-permalink.php', wp_nonce_url( 'admin.php?import=html&step=2', 'html_import_regenerate' ), 'options-general.php?page=html-import.php' ) ?></p>
			<?php }
		}
		echo '<h3>';
		printf( __( 'All done. <a href="%s">Have fun!</a>', 'import-html-pages' ),  'edit.php?post_type='.$posttype );
		echo '</h3>';
		flush();
		// DEBUG
//		echo '<pre>'.print_r( $this->filearr, true ).'</pre>';
	}
	
	function import() {
		$options = get_option( 'html_import' );
				
		if ( $_POST['import_files'] == 'file' ) {
			// preserve original file name so we can use it for slugs later ( maybe )
			$this->filename = $_FILES['import']['name'];
			
			// upload the file
			$file = wp_import_handle_upload();
			if ( isset( $file['error'] ) ) {
				echo $file['error'];
				return;
			}

			echo '<h2>'.__( 'Importing HTML file...', 'import-html-pages' ).'</h2>';
			$this->file = $file['file'];
			$this->get_single_file();
			$this->print_results( $options['type'] );
			wp_import_cleanup( $file['id'] );
			if ( $options['import_images'] )
				$this->find_images();
			if ( $options['import_documents'] )
				$this->find_documents();
			if ( $options['fix_links'] )
				$this->find_internal_links();
		}
		elseif ( $_POST['import_files'] == 'directory' ) {
			// in case they entered something dumb and didn't fix it when we showed an error on the options page...
			if ( validate_import_file( $options['root_directory'] ) > 0 )
				wp_die( __( "The beginning directory you entered is not an absolute path. Relative paths are not allowed here.", 'import-html-pages' ) );
			
			$this->table = '';
			$this->redirects = '';
			$this->filearr = array();
			$skipdirs = explode( ",", $options['skipdirs'] );
			$this->skip = array_merge( $skipdirs, array( '.', '..', '_vti_cnf', '_notes' ) );
			$this->allowed = explode( ",", $options['file_extensions'] );
			
			echo '<h2>'.__( 'Importing HTML files...', 'import-html-pages' ).'</h2>';
			$this->get_files_from_directory( $options['root_directory'] );
			$this->print_results( $options['type'] );
			if ( isset( $options['import_images'] ) && $options['import_images'] )
				$this->find_images();
			if ( isset( $options['import_documents'] ) && $options['import_documents'] )
				$this->find_documents();
			if ( isset( $options['fix_links'] ) && $options['fix_links'] )
				$this->find_internal_links();
		}
		else {
			_e( "Your file upload didn't work. Try again?", 'html-import-pages' );
		}

		do_action( 'import_done', 'html' );
	}
	
	function dispatch() {
		if ( empty ( $_GET['step'] ) )
			$step = 0;
		else
			$step = ( int ) $_GET['step'];

		$this->header();

		switch ( $step ) {
			case 0 :
				$this->greet();
				break;
			case 1 :
				check_admin_referer( 'html-import' );
				$result = $this->import();
				if ( is_wp_error( $result ) )
					echo $result->get_error_message();
				break;
			case 2 :
				$this->regenerate_redirects();
				break;
		}

		$this->footer();
	}
	
	function importer_styles() {
		?>
		<style type="text/css">
			textarea#import-result { height: 12em; width: 100%; }
			#importing th { width: 32% } 
			#importing th#id { width: 4% }
			#importing tbody tr:nth-child( odd ) { background: #f9f9f9; }
			span.attachment_error { display: block; padding-left: 2em; color: #d54e21; /* WP orange */ }
		</style>
		<?php
	}

	function __construct() {
		add_action( 'admin_head', array( &$this, 'importer_styles' ) );
	}
}

} // class_exists( 'WP_Importer' )

global $html_import;
$html_import = new HTML_Import();

register_importer( 'html', __( 'HTML', 'import-html-pages' ), sprintf( __( 'Import the contents of HTML files as posts, pages, or any custom post type. Visit <a href="%s">the options page</a> first to select which portions of your documents should be imported.', 'import-html-pages' ), 'options-general.php?page=html-import.php' ), array ( $html_import, 'dispatch' ) );


// in case this server doesn't have php_mbstring enabled in php.ini...
if ( !function_exists( 'mb_strlen' ) ) {
	function mb_strlen( $string ) {
		return strlen( utf8_decode( $string ) );
	}
}
if ( !function_exists( 'mb_strrpos' ) ) {
	function mb_strrpos( $haystack, $needle, $offset = 0 ) {
		return strrpos( utf8_decode( $haystack ), $needle, $offset );
	}
}