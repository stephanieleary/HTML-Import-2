<?php

function html_import_get_options() {
	$defaults = array( 
		'root_directory' => ABSPATH.__( 'html-files-to-import', 'import-html-pages' ),
		'old_url' => '',
		'index_file' => 'index.html',
		'file_extensions' => 'html,htm,shtml',
		'skipdirs' => __( 'images,includes,Templates', 'import-html-pages' ),
		'preserve_slugs' => 0,
		'status' => 'publish',
		'root_parent' => 0,
		'type' => 'page',
		'timestamp' => 'filemtime',
		'import_content' => 0,
		'content_region' => '',
		'content_tag' => __( 'div', 'import-html-pages' ),
		'content_tagatt' => __( 'id', 'import-html-pages' ),
		'content_attval' => __( 'content', 'import-html-pages' ),
		'clean_html' => 0,
		'encode' => 1,
		'allow_tags' => '<p><br><img><a><ul><ol><li><dl><dt><dd><blockquote><cite><em><i><strong><b><h2><h3><h4><h5><h6><hr>',
		'allow_attributes' => 'href,alt,title,src',
		'import_images' => 0,
		'import_documents' => 0,
		'document_mimes' => 'rtf,doc,docx,xls,xlsx,csv,ppt,pps,pptx,ppsx,pdf,zip,wmv,avi,flv,mov,mpeg,mp3,m4a,wav',
		'fix_links' => 0,
		'import_title' => 0,
		'title_region' => '',
		'title_tag' => __( 'title', 'import-html-pages' ),
		'title_tagatt' => '',
		'title_attval' => '',
		'remove_from_title' => '',
		'title_inside' => 0,
		'meta_desc' => 1,
		'user' => 0,
		'page_template' => 0,
		'firstrun' => true,
		'import_date' => 0,
		'date_region' => '',
		'date_tag' => __( 'div', 'import-html-pages' ),
		'date_tagatt' => __( 'id', 'import-html-pages' ),
		'date_attval' => __( 'date', 'import-html-pages' ),
		'import_field' => array( '0' ),
		'customfield_name' => array( '' ),
		'customfield_region' => array( '' ),
		'customfield_tag' => array( __( 'div', 'import-html-pages' ) ),
		'customfield_tagatt' => array( __( 'class', 'import-html-pages' ) ),
		'customfield_attval' => array( __( 'fieldclass', 'import-html-pages' ) ),
		'customfield_html' => array( '' )
	 );
	$options = get_option( 'html_import' );
	if ( !is_array( $options ) ) $options = array();
	return array_merge( $defaults, $options );
}

function html_import_options_page() { ?>
	<div class="wrap">
	
		<form method="post" id="html_import" action="options.php">
			<?php 
			settings_fields( 'html_import' );
			get_settings_errors( 'html_import' );	
			$options = html_import_get_options();
			//$msg .= '<pre>'. print_r( $options, true ) .'</pre>';
			//echo esc_html( $msg );
			?>

	<div class="ui-tabs">
		
		
		<h2><?php _e( 'HTML Import Settings', 'import-html-pages' ); ?></h2>
		<?php
		if ( $options['firstrun'] === true ) {
		echo '<p>'.sprintf( __( 'Welcome to HTML Import! This is a complicated importer with many options. Please look through all the tabs on this page before running your import.', 'import-html-pages' ), 'options-general.php?page=html-import.php' ).'</p>'; 
		}
		?>
		<h2 class="nav-tab-wrapper">
		<ul class="ui-tabs-nav">
			<li><a class="nav-tab" href="#tabs-1"><?php _e( "Files", 'import-html-pages' ); ?></a></li>
			<li><a class="nav-tab" href="#tabs-2"><?php _e( "Content", 'import-html-pages' ); ?></a></li>
			<li><a class="nav-tab" href="#tabs-3"><?php _e( "Title &amp; Metadata", 'import-html-pages' ); ?></a></li>
			<li><a class="nav-tab" href="#tabs-4"><?php _e( "Custom Fields", 'import-html-pages' ); ?></a></li>
			<li><a class="nav-tab" href="#tabs-5"><?php _e( "Categories &amp; Tags", 'import-html-pages' ); ?></a></li>
			<li><a class="nav-tab" href="#tabs-6"><?php _e( "Tools", 'import-html-pages' ); ?></a></li>
		</ul>
		</h2>
		
		
		
		<!-- FILES -->
		<div id="tabs-1">
		<h3><?php _e( "Files", 'import-html-pages' ); ?></h3>				
			<table class="form-table ui-tabs-panel" id="files">
		        <tr valign="top">
			        <th scope="row"><?php _e( "Directory to import", 'import-html-pages' ); ?></th>
			        <td><p><label><input type="text" name="html_import[root_directory]" id="root_directory"
							 	value="<?php echo esc_attr( $options['root_directory'] ); ?>" class="widefloat" />
							</label><br />
							<span class="description">
								<?php _e( 'The absolute path to the files you want to import.', 'html-import-pages' ); ?><br />
								<?php printf( __( 'Hint: the absolute path to this WordPress installation is: %s', 'html-import-pages' ), '<kbd>'.rtrim( ABSPATH, '/' ).'</kbd>' ); ?>
							</span>
						</p></td>
		        </tr>
							
				<tr valign="top">
			        <th scope="row"><?php _e( "Old site URL", 'import-html-pages' ); ?></th>
			        <td><p><label><input type="text" name="html_import[old_url]" id="old_url" 
						value="<?php echo esc_attr( $options['old_url'] ); ?>" class="widefloat" /> </label><br />
						<span class="description">
							<?php _e( 'This will be used only to generate accurate <kbd>.htaccess</kbd> redirects. The importer will not search for files here.', 'html-import-pages' ); ?>
						</span>
					</p></td>
		        </tr>
		
				<tr valign="top">
			        <th scope="row"><?php _e( "Default file", 'import-html-pages' ); ?></th>
			        <td><p><label><input type="text" name="html_import[index_file]" id="index_file" 
						value="<?php echo esc_attr( $options['index_file'] ); ?>" class="widefloat" /> </label><br />
						<span class="description">
						<?php _e( "Enter the name of the default file ( index.html, default.htm ) for directories on this server.", 'import-html-pages' ); ?>
						</span>
					</p></td>
		        </tr>
				
				<tr valign="top">
			        <th scope="row"><?php _e( "File extensions to include", 'import-html-pages' ); ?></th>
			        <td><p><label><input type="text" name="html_import[file_extensions]" id="file_extensions" 
						value="<?php echo esc_attr( $options['file_extensions'] ); ?>" class="widefloat" /> </label><br />
						<span class="description">
						<?php _e( "File extensions, without periods, separated by commas. All other file types will 
							be ignored.", 'import-html-pages' ); ?>
						</span>
					</p></td>
		        </tr>
		
				<tr valign="top">
			        <th scope="row"><?php _e( "Directories to exclude", 'import-html-pages' ); ?></th>
			        <td><p><label><input type="text" name="html_import[skipdirs]" id="skipdirs" 
						value="<?php echo esc_attr( $options['skipdirs'] ); ?>" class="widefloat" />  </label><br />
						<span class="description">
						<?php _e( "Directory names, without slashes, separated by commas. All files in these directories 
							will be ignored.", 'import-html-pages' ); ?>
						</span>
					</p></td>
		        </tr>
		
				<tr valign="top">
			        <th scope="row"><?php _e( "Preserve file names", 'import-html-pages' ); ?></th>
			        <td><p>
				
						<label><input name="html_import[preserve_slugs]" id="preserve_slugs" value="1" type="checkbox" <?php checked( $options['preserve_slugs'] ); ?> /> 
							 <?php _e( "Use the file's name as the imported page's slug", 'import-html-pages' ); ?></label>
						<br />
						<span class="description">
						<?php _e( "The slug will not include the file extension. To completely mimic your old URLs, add the extension to your permalink structure.", 'import-html-pages' ); ?>
						</span>
					</p></td>
		        </tr>
		    </table>
		
		</div>

		<!-- CONTENT -->	
		<div id="tabs-2">
		<h3><?php _e( "Content", 'import-html-pages' ); ?></h3>				
			<table class="form-table ui-tabs-panel" id="content">
				<tr valign="top" id="contentselect">
			        <th scope="row"><?php _e( "Select content by", 'import-html-pages' ); ?></th>
			        <td><p><label>
						<input type="radio" name="html_import[import_content]"
							value="tag" <?php checked( $options['import_content'], 'tag' ); ?> class="showrow" title="content" />
  						<?php _e( 'HTML tag', 'import-html-pages' ); ?></label> 
						&nbsp;&nbsp;
						<label>
						<input type="radio" name="html_import[import_content]"
							value="region" <?php checked( $options['import_content'], 'region' ); ?> class="showrow" title="content" />
	  					<?php _e( 'Dreamweaver template region', 'import-html-pages' ); ?></label>
						&nbsp;&nbsp;
						<label>
						<input type="radio" name="html_import[import_content]"
							value="file" <?php checked( $options['import_content'], 'file' ); ?> class="showrow" title="content" />
	  					<?php _e( 'Import entire file', 'import-html-pages' ); ?></label>
					</p>
					
					
					<table>
						<tr id="content-tag" <?php if ( $options['import_content'] != 'tag' ) echo 'style="display: none;"'; ?>>
				     	<td class="taginput">
				            <label><?php _e( "Tag", 'import-html-pages' ); ?><br />
				            <input type="text" name="html_import[content_tag]" id="content_tag" value="<?php echo esc_attr( $options['content_tag'] ); ?>" />
				            </label>
				            <br />
				            <span class="description"><?php _e( "The HTML tag, without brackets", 'import-html-pages' ); ?></span>
						</td>
						<td class="taginput">
				            <label><?php _e( "Attribute", 'import-html-pages' ); ?><br />
				            <input type="text" name="html_import[content_tagatt]" id="content_tagatt" value="<?php echo esc_attr( $options['content_tagatt'] ); ?>" />
				            </label>
				            <br />
				            <span class="description"><?php _e( "Leave blank to use a tag without an attribute, or when the attributes don't matter, such as &lt;body&gt;", 'import-html-pages' ); ?></span>
						</td>
						<td class="taginput">
				            <label><?php _e( "= Value", 'import-html-pages' ); ?><br />
				            <input type="text" name="html_import[content_attval]" id="content_attval" value="<?php echo esc_attr( $options['content_attval'] ); ?>" />
				            </label>
				            <br />
				            <span class="description"><?php _e( "Enter the attribute's value ( such as width, ID, or class name ) without quotes", 'import-html-pages' ); ?></span>
				        </td>
				</tr>
				<tr id="content-region" <?php if ( $options['import_content'] != 'region' ) echo 'style="display: none;"'; ?>>
					<td colspan="3">
						<label><?php _e( "Dreamweaver template region", 'import-html-pages' ); ?><br />
				        <input type="text" name="html_import[content_region]" value="<?php echo esc_attr( $options['content_region'] ); ?>" />  
				        </label><br />
				        <span class="description"><?php _e( "The name of the editable region ( e.g. 'Main Content' )", 'import-html-pages' ); ?></span>
					</td>
				</tr>
				</table>
				
					</td>
		        </tr>

				<tr>
				<th><?php _e( "More content options", 'import-html-pages' ); ?></th>
				<td>
					<label><input name="html_import[import_images]" id="import_images"  type="checkbox" value="1" 
						<?php checked( $options['import_images'], '1' ); ?> /> <?php _e( "Import linked images", 'import-html-pages' ); ?></label>
				</td>
				</tr>
				<tr>
				<th></th>
				<td>
					<label><input name="html_import[import_documents]" id="import_documents" value="1" type="checkbox" <?php checked( $options['import_documents'] ); ?> class="toggle" /> 
						 <?php _e( "Import linked documents", 'import-html-pages' ); ?></label>
				</td>
				</tr>
				<tr class="import_documents" 
					<?php if ( isset( $options['import_documents'] ) && !$options['import_documents'] ) echo 'style="display:none;"'; ?>>
				<th><?php _e( "Allowed file types", 'import-html-pages' ); ?></th>
		            <td><label>
			 			<input type="text" name="html_import[document_mimes]" id="document_mimes" 
							value="<?php echo esc_attr( $options['document_mimes'] ); ?>" class="widefloat" />  </label><br />
		            <span class="description"><?php _e( "Enter file extensions without periods, separated by commas. File types not listed here will not be imported to the media library. <br />
		Suggested: rtf, doc, docx, xls, xlsx, csv, ppt, pps, pptx, ppsx, pdf, zip, wmv, avi, flv, mov, mpeg, mp3, m4a, wav<br />", 'import-html-pages' ); ?></span>
		            </td> 
		       </tr>
				<tr>
				<th></th>
				<td>
					<label><input name="html_import[fix_links]" id="fix_links" value="1" type="checkbox" <?php checked( $options['fix_links'] ); ?> /> 
						 <?php _e( "Update internal links", 'import-html-pages' ); ?></label>
				</td>
				</tr>
				<th></th>
				<td>
					<label><input name="html_import[meta_desc]" id="meta_desc" value="1" type="checkbox" <?php checked( $options['meta_desc'] ); ?> /> 
						 <?php _e( "Use meta description as excerpt", 'import-html-pages' ); ?></label>
				</td>
				</tr>
				<tr>
				<th></th>
				<td>
					<label><input name="html_import[encode]" id="encode"  type="checkbox" value="1" 
						<?php checked( $options['encode'], '1' ); ?> /> <?php _e( "Convert special characters ( accents and symbols )", 'import-html-pages' ); ?> </label>
				</td>
				</tr>
				<tr>
				<th></th>
				<td>
					<label><input name="html_import[clean_html]" id="clean_html"  type="checkbox" value="1" 
						<?php checked( $options['clean_html'], '1' ); ?> class="toggle" />
						<?php _e( "Clean up bad ( Word, Frontpage ) HTML", 'import-html-pages' ); ?> </label>
				</td>
				</tr>
				<tr class="clean_html" <?php if ( !$options['clean_html'] ) echo 'style="display:none;"'; ?>>
				 
			        	<th><?php _e( "Allowed HTML", 'import-html-pages' ); ?></th>
			            <td>    <label>
			                <input type="text" name="html_import[allow_tags]" id="allow_tags" 
								value="<?php echo esc_attr( $options['allow_tags'] ); ?>" class="widefloat" />  </label><br />
			                <span class="description"><?php _e( "Enter tags ( with brackets ) to be preserved. All tags not listed here will be removed. <br />Suggested: ", 'import-html-pages' ); ?> 
			                &lt;p&gt;
			                &lt;br&gt;
			                &lt;img&gt;
			                &lt;a&gt;
			                &lt;ul&gt;
			                &lt;ol&gt;
			                &lt;li&gt;
							&lt;dl&gt;
							&lt;dt&gt;
							&lt;dd&gt;
			                &lt;blockquote&gt;
			                &lt;cite&gt;
			                &lt;em&gt;
			                &lt;i&gt;
			                &lt;strong&gt;
			                &lt;b&gt;
			                &lt;h2&gt;
			                &lt;h3&gt;
			                &lt;h4&gt;
			                &lt;h5&gt;
			                &lt;h6&gt;
			                &lt;hr&gt;
			                <br />

			                <em><?php _e( "If you have data tables, also include:", 'import-html-pages' ); ?></em> 
			                &lt;table&gt;
			                &lt;tbody&gt;
			                &lt;thead&gt;
			                &lt;tfoot&gt;
			                &lt;tr&gt;
			                &lt;td&gt;
			                &lt;th&gt;
			                &lt;caption&gt;
			                &lt;colgroup&gt;
			                </span>
			            </td> 
					</tr>
					<tr class="clean_html" <?php if ( !$options['clean_html'] ) echo 'style="display:none;"'; ?>>
					<th><?php _e( "Allowed attributes", 'import-html-pages' ); ?></th>
			            <td><label>
				 			<input type="text" name="html_import[allow_attributes]" id="allow_attributes" 
								value="<?php echo esc_attr( $options['allow_attributes'] ); ?>" class="widefloat" />  </label><br />
			            <span class="description"><?php _e( "Enter attributes separated by commas. All attributes not listed here will be removed. <br />Suggested: href, src, alt, title<br />
			    			<em>If you have data tables, also include:</em> summary, rowspan, colspan, span", 'import-html-pages' ); ?></span>
			            </td> 
			       </tr>
			</table>
			
		</div>

		<!-- TITLE AND META -->
		<div id="tabs-3">
		<h3><?php _e( "Title &amp; Metadata", 'import-html-pages' ); ?></h3>				
		<table class="form-table ui-tabs-panel" id="title">
			<tr valign="top" id="titleselect">
		        <th scope="row"><?php _e( "Select title by", 'import-html-pages' ); ?></th>
		        <td><p><label>
					<input type="radio" name="html_import[import_title]"
						value="tag" <?php checked( $options['import_title'], 'tag' ); ?> class="showrow" title="title" />
					<?php _e( 'HTML tag', 'import-html-pages' ); ?></label> 
					&nbsp;&nbsp;
					<label>
					<input type="radio" name="html_import[import_title]"
						value="region" <?php checked( $options['import_title'], 'region' ); ?> class="showrow" title="title" />
  					<?php _e( 'Dreamweaver template region', 'import-html-pages' ); ?></label>
					&nbsp;&nbsp;
					<label>
					<input type="radio" name="html_import[import_title]"
						value="filename" <?php checked( $options['import_title'], 'filename' ); ?> class="showrow" title="title" />
  					<?php _e( 'File name', 'import-html-pages' ); ?></label>
				</p>
				
				
				<table>
					<tr id="title-tag" <?php if ( $options['import_title'] !== 'tag' ) echo 'style="display:none;"'; ?> >
					     	<td class="taginput">
					            <label><?php _e( "Tag", 'import-html-pages' ); ?><br />
					            <input type="text" name="html_import[title_tag]" id="title_tag" value="<?php echo esc_attr( $options['title_tag'] ); ?>" />
					            </label>
					            <br />
					            <span class="description"><?php _e( "The HTML tag, without brackets", 'import-html-pages' ); ?></span>
							</td>
							<td class="taginput">
					            <label><?php _e( "Attribute", 'import-html-pages' ); ?><br />
					            <input type="text" name="html_import[title_tagatt]" id="title_tagatt" value="<?php echo esc_attr( $options['title_tagatt'] ); ?>" />
					            </label>
					            <br />
					            <span class="description"><?php _e( "Leave blank to use a tag without an attribute, or when the attributes don't matter, such as &lt;title&gt;", 'import-html-pages' ); ?></span>
							</td>
							<td class="taginput">
					            <label><?php _e( "= Value", 'import-html-pages' ); ?><br />
					            <input type="text" name="html_import[title_attval]" id="title_attval" value="<?php echo esc_attr( $options['title_attval'] ); ?>" />
					            </label>
					            <br />
					            <span class="description"><?php _e( "Enter the attribute's value ( such as width, ID, or class name ) without quotes", 'import-html-pages' ); ?></span>
					        </td>
						</tr>


						<tr id="title-region" <?php if ( $options['import_title'] !== 'region' ) echo 'style="display:none;"'; ?> >
						     	<td class="taginput">
							<td colspan="3">
								<label><?php _e( "Dreamweaver template region", 'import-html-pages' ); ?><br />
						        <input type="text" name="html_import[title_region]" id="title_region" value="<?php echo esc_attr( $options['title_region'] ); ?>" />  
						        </label><br />
						        <span class="description"><?php _e( "The name of the editable region ( e.g. 'Page Title' )", 'import-html-pages' ); ?></span>
							</td>
						</tr>
				</table>
					

				</td>
	        </tr>
	
			<tr valign="top">
				<th><?php _e( "Phrase to remove from page title: ", 'import-html-pages' ); ?></th>
				<td>
					<label><input type="text" name="html_import[remove_from_title]" id="remove_from_title" value="<?php echo esc_attr( $options['remove_from_title'] ); ?>" class="widefloat" />  </label><br />
					<span class="description"><?php _e( "Any common title phrase ( such as the site name, which most themes will print automatically )", 'import-html-pages' ); ?></span>
				</td>
			</tr>
			
			<tr>
			<th><?php _e( "Title position", 'import-html-pages' ); ?></th>
			<td>
				<label><input name="html_import[title_inside]" id="title_inside"  type="checkbox" value="1" 
					<?php checked( $options['title_inside'], '1' ); ?> /> <?php _e( "The title is inside the content area and should be removed from the post body", 'import-html-pages' ); ?></label>
			</td>
			</tr>
			<tr>
			
				<tr valign="top">
			        <th scope="row"><?php _e( "Import files as", 'import-html-pages' ); ?></th>
			        <td>
						<?php
						// support all public post types
						$typeselect = '';
						$post_types = get_post_types( array( 'public' => true ), 'objects' );
						foreach ( $post_types as $post_type ) {
							if ( $post_type->name != 'attachment' ) {
								$typeselect .= '<label><input name="html_import[type]" type="radio" value="' . esc_attr( $post_type->name ) . '" '.checked( $options['type'], $post_type->name, false );
								if ( is_post_type_hierarchical( $post_type->name ) )
									$typeselect .= "onclick=\"javascript: jQuery( '#hierarchy' ).show( 'fast' );jQuery( '#page-template' ).show( 'fast' );\"";
								else
									$typeselect .= "onclick=\"javascript: jQuery( '#hierarchy' ).hide( 'fast' );jQuery( '#page-template' ).hide( 'fast' );\"";
								$typeselect .= '> '.esc_html( $post_type->labels->name ).'</label> &nbsp;&nbsp;';
							}
						}
						echo $typeselect; 
						?>
					</td>
		        </tr>
				<tr>
				<th><?php _e( "Set status to", 'import-html-pages' ); ?></th>
				<td>
					<select name="html_import[status]" id="status">
				    	<option value="publish" <?php selected( 'publish', $options['status'] ); ?>><?php _e( "publish", 'import-html-pages' ); ?></option>
				        <option value="draft" <?php selected( 'draft', $options['status'] ); ?>><?php _e( "draft", 'import-html-pages' ); ?></option>
				        <option value="private" <?php selected( 'private', $options['status'] ); ?>><?php _e( "private", 'import-html-pages' ); ?></option>
				        <option value="pending" <?php selected( 'pending', $options['status'] ); ?>><?php _e( "pending", 'import-html-pages' ); ?></option>
				    </select>
				</td>
				</tr>
				<tr>
				<th><?php _e( "Set timestamps to", 'import-html-pages' ); ?></th>
				<td>
					<select name="html_import[timestamp]" id="timestamp">
				    	<option value="now" <?php if ( $options['timestamp'] == 'now' ) echo 'selected="selected"'; ?>><?php _e( "now", 'import-html-pages' ); ?></option>
				        <option value="filemtime" <?php if ( $options['timestamp'] == 'filemtime' ) echo 'selected="selected"'; ?>>
							<?php _e( "last time the file was modified", 'import-html-pages' ); ?></option>
						<option value="customfield" <?php if ( $options['timestamp'] == 'customfield' ) echo 'selected="selected"'; ?>>
							<?php _e( "custom field", 'import-html-pages' ); ?></option>
				    </select>
				</td>
				</tr>
				<tr>
				<th><?php _e( "Set author to", 'import-html-pages' ); ?></th>
				<td>
					<?php wp_dropdown_users( array( 'selected' => $options['user'], 'name' => 'html_import[user]', 'who' => 'authors' ) ); ?>
				</td>
				</tr>

				<tr id="hierarchy" <?php if ( !is_post_type_hierarchical( $options['type'] ) ) echo "style=display:none;"; ?>>
				<th><?php _e( "Import pages as children of: ", 'import-html-pages' ); ?></th>
				<td>
			        <?php 
			            $pages = wp_dropdown_pages( array( 'echo' => 0, 'selected' => $options['root_parent'], 'name' => 'html_import[root_parent]', 'show_option_none' => __( 'None ( top level )', 'import-html-pages' ), 'sort_column'=> 'menu_order, post_title' ) );
			            if ( empty( $pages ) ) $pages = "<select name=\"root_parent\"><option value=\"0\">".__( 'None ( top level )', 'import-html-pages' )."</option></select>";
			            echo $pages;
			        ?>
				</td>
				</tr>

				<tr id="page-template" <?php if ( !is_post_type_hierarchical( $options['type'] ) ) echo "style=display:none;"; ?>>
				<th><?php _e( "Template for imported pages: ", 'import-html-pages' ); ?></th>
				<td>
			        <select name="html_import[page_template]" id="page_template">
					<option value='0'><?php _e( 'Default Template' ); ?></option>
					<?php page_template_dropdown( $options['page_template'] ); ?>
					</select>
				</td>
				</tr>
		</table>
		</div>
		

		<!-- CUSTOM FIELDS -->
		<div id="tabs-4">
		<h3><?php _e( "Custom Fields", 'import-html-pages' ); ?></h3>				
		<table class="form-table ui-tabs-panel striped" id="customfields">
			<tbody>
			<tr valign="top" id="customdatefield">
		        <th scope="row"><?php _e( "Select date by", 'import-html-pages' ); ?></th>
		        <td colspan="2"><p><label>
					<input type="radio" name="html_import[import_date]"
						value="tag" <?php checked( $options['import_date'], 'tag' ); ?> class="showrow" title="date" />
					<?php _e( 'HTML tag', 'import-html-pages' ); ?></label> 
					&nbsp;&nbsp;
					<label>
					<input type="radio" name="html_import[import_date]"
						value="region" <?php checked( $options['import_date'], 'region' ); ?> class="showrow" title="date" />
  					<?php _e( 'Dreamweaver template region', 'import-html-pages' ); ?></label>
				</p>
				
				
				<table>
					<tr id="date-tag" <?php if ( $options['import_date'] !== 'tag' ) echo 'style="display: none;"'; ?>>
			     	<td class="taginput">
			            <label><?php _e( "Tag", 'import-html-pages' ); ?><br />
			            <input type="text" name="html_import[date_tag]" id="date_tag" value="<?php echo esc_attr( $options['date_tag'] ); ?>" />
			            </label>
					</td>
					<td class="taginput">
			            <label><?php _e( "Attribute", 'import-html-pages' ); ?><br />
			            <input type="text" name="html_import[date_tagatt]" id="date_tagatt" value="<?php echo esc_attr( $options['date_tagatt'] ); ?>" />
			            </label>
					</td>
					<td class="taginput">
			            <label><?php _e( "= Value", 'import-html-pages' ); ?><br />
			            <input type="text" name="html_import[date_attval]" id="date_attval" value="<?php echo esc_attr( $options['date_attval'] ); ?>" />
			            </label>
			        </td>
			</tr>
			<tr id="date-region" <?php if ( $options['import_date'] !== 'region' ) echo 'style="display: none;"'; ?>>
				<td colspan="3">
					<label><?php _e( "Dreamweaver template region", 'import-html-pages' ); ?><br />
			        <input type="text" name="html_import[date_region]" value="<?php echo esc_attr( $options['date_region'] ); ?>" />  
			        </label>
				</td>
			</tr>
			</table>
			
				</td>
	        </tr>
	
	<tr valign="top">
		<th colspan="3">
			<h4><?php _e( "Custom fields", 'import-html-pages' ); ?></h4>
		</th>
	</tr>

	<?php if ( !empty( $options['customfield_name'] ) && is_array( $options['customfield_name'] ) ) {
		foreach ( $options['customfield_name'] as $index => $fieldname ) : ?>
		<tr valign="top" class="clone" id="customfield<?php echo $index; ?>">
			<th><a class="button-secondary delRow" title="Remove field">&times;</a></th>
			<td>
				<label><?php _e( 'Custom field name', 'import-html-pages' ); ?><br />
					<input type="text" name="html_import[customfield_name][<?php echo $index; ?>]" 
						value="<?php echo esc_attr( $options['customfield_name'][$index] ); ?>" />
					</label><br />
					<label>
						<input type="checkbox" name="html_import[customfield_html][<?php echo $index; ?>]" value="1" <?php checked( '1', $options['customfield_html'][$index] ) ?>>
						<?php _e( 'Allow HTML', 'import-html-pages' ); ?>
					</label>
			</td>
	        <td>

				Select field by:<br />
				<label>
				<input type="radio" name="html_import[import_field][<?php echo $index; ?>]"
					value="tag" class="showrow" title="customfield" <?php checked( $options['import_field'][$index], 'tag' ); ?> />
				<?php _e( 'HTML tag', 'import-html-pages' ); ?></label> 
				&nbsp;&nbsp;
				<label>
				<input type="radio" name="html_import[import_field][<?php echo $index; ?>]"
					value="region" class="showrow" title="customfield" <?php checked( $options['import_field'][$index], 'region' ); ?> />
				<?php _e( 'Dreamweaver template region', 'import-html-pages' ); ?></label>
			</p>


			<table>
				<tr id="customfield-tag" <?php if ( $options['import_field'][$index] == 'region' ) echo 'style="display: none;"'; ?> >
		     	<td class="taginput">
		            <label><?php _e( "Tag", 'import-html-pages' ); ?><br />
		            <input type="text" name="html_import[customfield_tag][<?php echo $index; ?>]" 
						value="<?php  echo esc_attr( $options['customfield_tag'][$index] ); ?>" />
		            </label>

				</td>
				<td class="taginput">
		            <label><?php _e( "Attribute", 'import-html-pages' ); ?><br />
		            <input type="text" name="html_import[customfield_tagatt][<?php echo $index; ?>]" 
						value="<?php echo esc_attr( $options['customfield_tagatt'][$index] ); ?>" />
		            </label>

				</td>
				<td class="taginput">
		            <label><?php _e( "= Value", 'import-html-pages' ); ?><br />
		            <input type="text" name="html_import[customfield_attval][<?php echo $index; ?>]" 
						value="<?php echo esc_attr( $options['customfield_attval'][$index] ); ?>" />
		            </label>

		        </td>
				</tr>
				<tr id="customfield-region" <?php if ( $options['import_field'][$index] == 'tag' ) echo 'style="display: none;"'; ?> >
					<td colspan="3">
						<label><?php _e( "Dreamweaver template region", 'import-html-pages' ); ?><br />
				        <input type="text" name="html_import[customfield_region][<?php echo $index; ?>]" 
							value="<?php echo esc_attr( $options['customfield_region'][$index] ); ?>" />  
				        </label>
					</td>
				</tr>
			</table>

			</td>
	    </tr>
	<?php endforeach;
	} else { ?>
		<tr valign="top" class="clone" id="customfield0">
			<th>
				<a class="button-secondary delRow" title="Remove field">&times;</a></th>
			<td>
				<label><?php _e( 'Custom field name', 'import-html-pages' ); ?><br />
					<input type="text" name="html_import[customfield_name][]" value="" />
					</label><br />
					<label>
						<input type="checkbox" name="html_import[customfield_html][<?php echo $index; ?>]" value="1" <?php checked( '1', $options['customfield_html'][$index] ) ?>>
						<?php _e( 'Allow HTML', 'import-html-pages' ); ?>
					</label>
			</td>
	        <td>

				Select field by:<br />
				<label>
				<input type="radio" name="html_import[import_field][]"
					value="tag" class="showrow" title="customfield" />
				<?php _e( 'HTML tag', 'import-html-pages' ); ?></label> 
				&nbsp;&nbsp;
				<label>
				<input type="radio" name="html_import[import_field][]"
					value="region" class="showrow" title="customfield" />
				<?php _e( 'Dreamweaver template region', 'import-html-pages' ); ?></label>
			</p>


			<table>
				<tr id="customfield-tag">
		     	<td class="taginput">
		            <label><?php _e( "Tag", 'import-html-pages' ); ?><br />
		            <input type="text" name="html_import[customfield_tag][]" value="" />
		            </label>

				</td>
				<td class="taginput">
		            <label><?php _e( "Attribute", 'import-html-pages' ); ?><br />
		            <input type="text" name="html_import[customfield_tagatt][]" value="" />
		            </label>

				</td>
				<td class="taginput">
		            <label><?php _e( "= Value", 'import-html-pages' ); ?><br />
		            <input type="text" name="html_import[customfield_attval][]" value="" />
		            </label>

		        </td>
				</tr>
				<tr id="customfield-region" style="display: none;">
					<td colspan="3">
						<label><?php _e( "Dreamweaver template region", 'import-html-pages' ); ?><br />
				        <input type="text" name="html_import[customfield_region][]" value="" />  
				        </label>
					</td>
				</tr>
			</table>

			</td>
	    </tr>
	<?php } // else no custom fields ?>

</tbody>
<tfoot>
<tr><td colspan="2"><a class="button-secondary cloneTableRows" href="#">Add a custom field</a></td>
	</tr>
	</tfoot>
		</table>
		</div>
		
		<!-- TAXONOMIES -->
		<div id="tabs-5">
		<h3><?php _e( "Taxonomies", 'import-html-pages' ); ?></h3>				
		<div class="ui-tabs-panel" id="taxonomies">
			<?php
			// support all public taxonomies
			$nonhierarchical = '';
			$taxonomies = get_taxonomies( array( 'public' => true ), 'objects', 'and' );
			?>
			<?php if ( is_array( $taxonomies ) ) : ?>
			<p><?php _e( 'Here, you may assign categories, tags, and custom taxonomy terms to all your imported posts.', 'import-html-pages' ); ?></p>
			<p><?php _e( 'To import tags from a region in each file, use a custom field with the name <kbd>post_tag</kbd>.', 'import-html-pages' ); ?></p>
					<?php foreach ( $taxonomies as $tax ) :
						if ( isset( $options[$tax->name] ) )
							$value = esc_attr( $options[$tax->name] );
						else
							$value = '';
						if ( !is_taxonomy_hierarchical( $tax->name ) ) :
						// non-hierarchical
							$nonhierarchical .= '<p class="taxoinput"><label>'.esc_html( $tax->label ).'<br />';
							$nonhierarchical .= '<input type="text" name="html_import['.esc_attr( $tax->name ).']" 
							 	value="'.$value.'" /></label></p>';
						else:
						// hierarchical 
						?>
						 	<div class="taxochecklistbox">
								<?php echo esc_html( $tax->label ); ?><br />
					        <ul class="taxochecklist">
					     	<?php
							if ( !isset( $options[$tax->name] ) ) $selected = '';
							else $selected = $options[$tax->name];
							wp_terms_checklist( 0, array( 
								           'descendants_and_self' => 0,
								           'selected_cats' => $selected,
								           'popular_cats' => false,
								           'walker' => new HTML_Import_Walker_Category_Checklist,
								           'taxonomy' => $tax->name,
								           'checked_ontop' => false,
								       )
								 ); 
						?>
						</ul>  </div>
					<?php
					endif;
					endforeach; 
					echo '<br class="clear" />'.$nonhierarchical;
					?>
			</div>
			<?php endif; ?>
		</div>	
					
		
		<!-- TOOLS -->
		<div id="tabs-6">
		<h3><?php _e( "Tools", 'import-html-pages' ); ?></h3>				
			<table class="form-table ui-tabs-panel" id="tools">
		        <tr valign="top">
			        <th scope="row"><?php _e( "Regenerate <kbd>.htaccess</kbd> redirects", 'import-html-pages' ); ?></th>
			        <td><p><?php printf( __( 'If you <a href="%s">changed your permalink structure</a> after you imported files, you can <a href="%s">regenerate the redirects</a>.', 'import-html-pages' ), 'wp-admin/options-permalink.php', wp_nonce_url( 'admin.php?import=html&step=2', 'html_import_regenerate' ) ) ?></p></td>
		        </tr>
				<tr valign="top">
			        <th scope="row"><?php _e( "Other helpful plugins", 'import-html-pages' ); ?></th>
					<td>
						<p><?php printf( __( '<a href="%s">Broken Link Checker</a> finds broken links and references to missing media files. Since the importer does not handle links or media files other than images, you should run this to see what else needs to be copied or updated from your old site.', 'import-html-pages' ), 'http://wordpress.org/extend/plugins/broken-link-checker/' ); ?></p>
						<p><?php printf( __( '<a href="%s">Search and Replace</a> helps you fix many broken links at once, if you have many links to the same files or if there is a pattern ( like <kbd>&lt;a href="../../files"&gt;</kbd> ) to your broken links.', 'import-html-pages' ), 'http://wordpress.org/extend/plugins/search-and-replace/' ); ?></p>
						<p><?php printf( __( '<a href="%s">Redirection</a> provides a nice admin interface for managing redirects. If you would rather not edit your <kbd>.htaccess</kbd> file, or if you just want to redirect one or two of your old pages, you can ignore the redirects generated by the importer. Instead, copy the post\'s old URL from the custom fields and paste it into Redirection\'s options.', 'import-html-pages' ), 'http://wordpress.org/extend/plugins/redirection/' ); ?></p>
						<p><?php printf( __( '<a href="%s">Add from Server</a> lets you import media files that are on your server but not part of the WordPress media library.', 'import-html-pages' ), 'http://wordpress.org/extend/plugins/add-from-server/' ); ?></p>
						<p><?php printf( __( '<a href="%s">Add Linked Images to Gallery</a> is helpful if you have imported data using other plugins and you would like to import linked images. However, it handles only images that are referenced with complete URLs; relative paths will not work.', 'import-html-pages' ), 'http://wordpress.org/extend/plugins/add-linked-images-to-gallery-v01/' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Donate', 'import-html-pages' ) ?></th>
					<td>
						<p><?php printf( __( 'If this importer has saved you hours of copying and pasting, a <a href="%s">donation toward future development</a> would be much appreciated!', 'import-html-pages' ), 'http://stephanieleary.com/code/wordpress/html-import/' ); ?></p>
					</td>
			</table>
	</div>		
	
	</div>	<!-- UI tabs wrapper -->	
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save settings', 'import-html-pages' ) ?>" />
				<?php if ( !$options['firstrun'] ) { ?>
				<a href="admin.php?import=html" class="button-secondary">Import files</a>
				<?php } ?>
			</p>
		</form>
	
	</div> <!-- .wrap -->
	<?php 
}

function html_import_validate_options( $input ) {
	// Validation/sanitization. Add errors to $msg[].
	$msg = array();
	$linkmsg = '';
	$msgtype = 'error';
	
	// sanitize path for Win32
	$input['root_directory'] = str_replace( '\\' ,'/', $input['root_directory'] ); 
	$input['root_directory'] = preg_replace( '|/+|', '/', $input['root_directory'] );
	
	if ( validate_import_file( $input['root_directory'] ) > 0 ) {
		$msg[] = __( "The beginning directory you entered is not an absolute path. Relative paths are not allowed here.", 'import-html-pages' );
		$input['root_directory'] = ABSPATH.__( 'html-files-to-import', 'import-html-pages' );
	}
		
	$input['root_directory'] = rtrim( $input['root_directory'], '/' );
	$input['old_url'] = esc_url( rtrim( $input['old_url'], '/' ) );
	
	// trim the extensions, skipped dirs, allowed attributes. Invalid ones will not cause problems.
	$input['file_extensions']  = str_replace( '.', '', $input['file_extensions'] );
	$input['file_extensions']  = str_replace( ' ', '', $input['file_extensions'] );
	$input['file_extensions']  = strtolower( $input['file_extensions'] );
	$input['document_mimes']   = str_replace( '.', '', $input['document_mimes'] );
	$input['document_mimes']   = str_replace( ' ', '', $input['document_mimes'] );
	$input['document_mimes']   = strtolower( $input['document_mimes'] );
	$input['skipdirs'] 		   = str_replace( ' ', '', $input['skipdirs'] );
	$input['allow_tags'] 	   = str_replace( '/', '', $input['allow_tags'] );
	$input['allow_tags'] 	   = str_replace( ' ', '', $input['allow_tags'] );
	$input['allow_attributes'] = str_replace( ' ', '', $input['allow_attributes'] );
	$input['index_files'] 	   = str_replace( ' ', '', $input['index_files'] );
	
	if ( !in_array( $input['status'], get_post_stati() ) ) 
		$input['status'] = 'publish';
	
	$post_types = get_post_types( array( 'public' => true ),'names' );
	if ( !in_array( $input['type'], $post_types ) )
		$input['type'] = 'page';
		
	if ( !in_array( $input['timestamp'], array( 'now', 'filemtime', 'customfield' ) ) )
		$input['timestamp'] = 'filemtime';
		
	if ( !in_array( $input['import_content'], array( 'tag', 'region', 'file' ) ) )
		$input['import_content'] = 'tag';
	if ( !in_array( $input['import_title'], array( 'tag', 'region', 'filename' ) ) )
		$input['import_title'] = 'tag';
	
	// trim region/tag/attr/value
	if ( !empty( $input['content_region'] ) )	$input['content_region'] = 	sanitize_text_field( $input['content_region'] );
	if ( !empty( $input['content_tag'] ) )		$input['content_tag'] 	 =	sanitize_text_field( $input['content_tag'] );
	if ( !empty( $input['content_tagatt'] ) )	$input['content_tagatt'] = 	sanitize_text_field( $input['content_tagatt'] );
	if ( !empty( $input['content_attval'] ) )	$input['content_attval'] = 	sanitize_text_field( $input['content_attval'] );
	if ( !empty( $input['title_region'] ) )		$input['title_region'] 	 = 	sanitize_text_field( $input['title_region'] );
	if ( !empty( $input['title_tag'] ) )		$input['title_tag'] 	 = 	sanitize_text_field( $input['title_tag'] );
	if ( !empty( $input['title_tagatt'] ) )		$input['title_tagatt'] 	 = 	sanitize_text_field( $input['title_tagatt'] );
	if ( !empty( $input['title_attval'] ) )		$input['title_attval'] 	 = 	sanitize_text_field( $input['title_attval'] );
	if ( !empty( $input['date_region'] ) )		$input['date_region'] 	 = 	sanitize_text_field( $input['date_region'] );
	if ( !empty( $input['date_tag'] ) )			$input['date_tag'] 		 = 	sanitize_text_field( $input['date_tag'] );
	if ( !empty( $input['date_tagatt'] ) )		$input['date_tagatt'] 	 = 	sanitize_text_field( $input['date_tagatt'] );
	if ( !empty( $input['date_attval'] ) )		$input['date_attval'] 	 = 	sanitize_text_field( $input['date_attval'] );
	
	// We could have many custom fields. For now, let's just make it an array. Deal with it in the importer.
	if ( !is_array( $input['customfield_name'] ) )
		$input['customfield_name'] = array( $input['customfield_name'] );
	if ( !is_array( $input['import_field'] ) )
		$input['import_field'] = array( $input['import_field'] );
	if ( !is_array( $input['customfield_region'] ) )
		$input['customfield_region'] = array( $input['customfield_region'] );
	if ( !is_array( $input['customfield_tag'] ) )
		$input['customfield_tag'] = array( $input['customfield_tag'] );
	if ( !is_array( $input['customfield_tagatt'] ) )
		$input['customfield_tagatt'] = array( $input['customfield_tagatt'] );
	if ( !is_array( $input['customfield_attval'] ) )
		$input['customfield_attval'] = array( $input['customfield_attval'] );
		if ( !is_array( $input['customfield_html'] ) )
			$input['customfield_html'] = array( $input['customfield_html'] );
	
	// must have something to look for in the HTML
	if ( $input['import_content'] == 'tag' && empty( $input['content_tag'] ) )
		$msg[] = __( "You did not enter an HTML content tag to import.", 'import-html-pages' );
	if ( $input['import_content'] == 'region' && empty( $input['content_region'] ) )
		$msg[] = __( "You did not enter a Dreamweaver content template region to import.", 'import-html-pages' );
	if ( $input['import_title'] == 'tag' && empty( $input['title_tag'] ) )
		$msg[] = __( "You did not enter an HTML title tag to import.", 'import-html-pages' );
	if ( $input['import_title'] == 'region' && empty( $input['title_region'] ) )
		$msg[] = __( "You did not enter a Dreamweaver title template region to import.", 'import-html-pages' );
		
	if ( !isset( $input['root_parent'] ) )
		$input['root_parent'] = 0;
	
	// $input['remove_from_title'] could be anything, including unencoded characters or HTML tags
	// it's a search pattern; leave it alone
	
	// these should all be zero or one
	$input['clean_html'] = absint( $input['clean_html'] );
	if ( $input['clean_html'] > 1 ) $input['clean_html'] = 0;
	$input['encode'] = absint( $input['encode'] );
	if ( $input['encode'] > 1 ) $input['encode'] = 0;
	$input['meta_desc'] = absint( $input['meta_desc'] );
	if ( $input['meta_desc'] > 1 ) $input['meta_desc'] = 1;
	$input['title_inside'] = absint( $input['title_inside'] );
	if ( $input['title_inside'] > 1 ) $input['title_inside'] = 0;

	
	// see if this is a real user
	$input['user'] = absint( $input['user'] );
	$user_info = get_userdata( $input['user'] );
	if ( $user_info === false ) {
		$msg[] = "The author you specified does not exist.";
		$currentuser = wp_get_current_user();
		$input['user'] = $currentuser->ID;
	}
		
	// If settings have been saved at least once, we can turn this off.
	$input['firstrun'] = false;
	
	
	// Send custom updated message
	$msg = implode( '<br />', $msg );
	
	if ( empty( $msg ) ) {
		
		$linkstructure = get_option( 'permalink_structure' );
		if ( empty( $linkstructure ) )
			$linkmsg = sprintf( __( 'If you intend to <a href="%s">set a permalink structure</a>, you should do it 
				before importing so the <kbd>.htaccess</kbd> redirects will be accurate.', 'import-html-pages' ), 'options-permalink.php' );
		
		$msg = sprintf( __( 'Settings saved. %s <a href="%s">Ready to import files?</a>', 'import-html-pages' ), 
				$linkmsg, 'admin.php?import=html' );
		// $msg .= '<pre>'. print_r( $input, false ) .'</pre>';
		$msgtype = 'updated';
	}
	
	add_settings_error( 'html_import', 'html_import', $msg, $msgtype );
	return $input;
}

// custom file validator to accommodate Win32 paths starting with drive letter
// based on WP's validate_file()
function validate_import_file( $file, $allowed_files = '' ) {
   if ( false !== strpos( $file, '..' ) )
       return 1;

    if ( false !== strpos( $file, './' ) )
       return 1;

   if ( !empty ( $allowed_files ) && ( !in_array( $file, $allowed_files ) ) )
       return 3;
/*
    if ( ':' == substr( $file, 1, 1 ) )
        return 2;
*/
   return 0;
}

// custom walker so we can change the name attribute of the category checkboxes ( until #16437 is fixed )
// mostly a duplicate of Walker_Category_Checklist
class HTML_Import_Walker_Category_Checklist extends Walker {
     var $tree_type = 'category';
     var $db_fields = array ( 'parent' => 'parent', 'id' => 'term_id' ); 

 	function start_lvl( &$output, $depth = 0, $args = array() ) {
         $indent = str_repeat( "\t", $depth );
         $output .= "$indent<ul class='children'>\n";
     }
 
 	function end_lvl( &$output, $depth = 0, $args = array() ) {
         $indent = str_repeat( "\t", $depth );
         $output .= "$indent</ul>\n";
     }
 
 	function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
         extract( $args );
         if ( empty( $taxonomy ) )
             $taxonomy = 'category';
 
		// This is the part we changed
         $name = 'html_import['.$taxonomy.']';
 
         $class = in_array( $object->term_id, $popular_cats ) ? ' class="popular-category"' : '';
         $output .= "\n<li id='{$taxonomy}-{$object->term_id}'$class>" . '<label class="selectit"><input value="' . $object->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $object->term_id . '"' . checked( in_array( $object->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $object->name ) ) . '</label>';
     }
 
 	function end_el( &$output, $category, $depth = 0, $args = array() ) {
         $output .= "</li>\n";
     }
}