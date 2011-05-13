<?php
/**
 * Displays a row in the manage backups table
 *
 * @param string $file
 */
function hmbkp_get_backup_row( $file ) {

	$encode = base64_encode( $file['file'] ); ?>

	<tr class="hmbkp_manage_backups_row<?php if ( get_option( 'hmbkp_complete' ) ) : ?> completed<?php delete_option( 'hmbkp_complete' ); endif; ?>">

		<th scope="row">
			<?php echo date( get_option('date_format'), filemtime( $file['file'] ) ) . ' ' . date( 'H:i', filemtime($file['file'] ) ); ?>
		</th>

		<td>
			<?php echo hmbkp_size_readable( filesize( $file['file'] ) ); ?>
		</td>

		<td>

			<a href="tools.php?page=<?php echo HMBKP_PLUGIN_SLUG; ?>&amp;hmbkp_download=<?php echo $encode; ?>"><?php _e( 'Download', 'hmbkp' ); ?></a> |
			<a href="tools.php?page=<?php echo HMBKP_PLUGIN_SLUG; ?>&amp;hmbkp_delete=<?php echo $encode ?>" class="delete"><?php _e( 'Delete', 'hmbkp' ); ?></a>

		</td>

	</tr>

<?php }

function hmbkp_admin_notices() {
	
	// Make sure backups dir exists and is writable
	if ( !is_dir( hmbkp_path() ) ) :
	
	    function hmbkp_path_exists_warning() {
		    $php_user = exec( 'whoami' );
			$php_group = reset( explode( ' ', exec( 'groups' ) ) );
	    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress is almost ready.', 'hmbkp' ) . '</strong> ' . sprintf( __( 'The backups directory can\'t be created because your %s directory isn\'t writable, run %s or %s or create the folder yourself.', 'hmbkp' ), '<code>wp-content</code>', '<code>chown ' . $php_user . ':' . $php_group . ' ' . WP_CONTENT_DIR . '</code>', '<code>chmod 777 ' . WP_CONTENT_DIR . '</code>' ) . '</p></div>';
	    }
	    add_action( 'admin_notices', 'hmbkp_path_exists_warning' );
	
	endif;
	
	if ( is_dir( hmbkp_path() ) && !is_writable( hmbkp_path() ) ) :
	
	    function hmbkp_writable_path_warning() {
			$php_user = exec( 'whoami' );
			$php_group = reset( explode( ' ', exec( 'groups' ) ) );
	    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress is almost ready.', 'hmbkp' ) . '</strong> ' . sprintf( __( 'Your backups directory isn\'t writable. run %s or %s or set the permissions yourself.', 'hmbkp' ), '<code>chown -R ' . $php_user . ':' . $php_group . ' ' . hmbkp_path() . '</code>', '<code>chmod -R 777 ' . hmbkp_path() . '</code>' ) . '</p></div>';
	    }
	    add_action( 'admin_notices', 'hmbkp_writable_path_warning' );
	
	endif;
	
	if ( ini_get( 'safe_mode' ) ) :
	
	    function hmbkp_safe_mode_warning() {
	    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.', 'hmbkp' ) . '</strong> ' . sprintf( __( ' %s is running in %s. Please contact your host and ask them to disable %s.', 'hmbkp' ), '<code>PHP</code>', '<a href="http://php.net/manual/en/features.safe-mode.php"><code>Safe Mode</code></a>', '<code>Safe Mode</code>' ) . '</p></div>';
	    }
	    add_action( 'admin_notices', 'hmbkp_safe_mode_warning' );
	
	endif;
	
	if ( defined( 'HMBKP_FILES_ONLY' ) && HMBKP_FILES_ONLY && defined( 'HMBKP_DATABASE_ONLY' ) && HMBKP_DATABASE_ONLY ) :
	
	    function hmbkp_nothing_to_backup_warning() {
	    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.', 'hmbkp' ) . '</strong> ' . sprintf( __( 'You have both %s and %s defined so there isn\'t anything to back up.', 'hmbkp' ), '<code>HMBKP_DATABASE_ONLY</code>', '<code>HMBKP_FILES_ONLY</code>' ) . '</p></div>';
	    }
	    add_action( 'admin_notices', 'hmbkp_nothing_to_backup_warning' );
	
	endif;
	
	if ( get_transient( 'hmbkp_estimated_filesize' ) && disk_free_space( ABSPATH ) <= ( 2 * get_transient( 'hmbkp_estimated_filesize' ) ) ) :
	
	    function hmbkp_low_space_warning() {
	    	echo '<div id="hmbkp-warning" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.', 'hmbkp' ) . '</strong> ' . sprintf( __( 'You only have %s of free space left on your server.', 'hmbkp' ), '<code>' . hmbkp_size_readable( disk_free_space( ABSPATH ), null, '%01u %s' ) . '</code>' ) . '</p></div>';
	    }
	    add_action( 'admin_notices', 'hmbkp_low_space_warning' );
	
	endif;
	
	if( defined( 'HMBKP_EMAIL' ) && !is_email( HMBKP_EMAIL ) ) :
		function hmbkp_email_invalid(){
			echo '<div id="hmbkp-email_invalid" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.', 'hmbkp' ) . '</strong> ' . sprintf( __( '%s is not a valid email address.', 'hmbkp' ), '<code>' . HMBKP_EMAIL . '</code>' ) . '</p></div>';
		}
		add_action( 'admin_notices', 'hmbkp_email_invalid' );
	endif; 
	
	
	if ( defined( 'HMBKP_EMAIL' ) && get_option( 'hmbkp_email_error' ) ) :
		function hmbkp_email_error(){
			echo '<div id="hmbkp-email_invalid" class="updated fade"><p><strong>' . __( 'BackUpWordPress has detected a problem.', 'hmbkp' ) . '</strong> ' . __( 'The last backup email failed to send.', 'hmbkp' ) . '</p></div>';
		}
		add_action( 'admin_notices', 'hmbkp_email_error' );
	endif; 

}
add_action( 'admin_head', 'hmbkp_admin_notices' );