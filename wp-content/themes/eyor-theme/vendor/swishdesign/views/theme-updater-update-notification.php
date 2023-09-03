<style>
	.update-nag { display: none; }
	#instructions {max-width: 750px;}
	.theme-screen-picture {float: right; margin: 0 0 20px 20px; border: 1px solid #ddd; width: 300px; height:auto;}
	#changeLogList {list-style-type: disc;list-style-position: inside;}
</style>

<?php
$screenshot_src = get_template_directory_uri(). '/screenshot.png';
?>

<div class="wrap">
	<div id="icon-tools" class="icon32"></div>
	<h2><?php echo esc_html( $theme_name ); ?> <?php esc_html_e( 'Theme Updates','qed' ); ?></h2>
	<div id="message" class="updated below-h2">
		<p><strong><?php printf( esc_html__( 'There is a new version of the %s Theme available.','qed' ), $theme_name ); ?></strong> <?php printf( esc_html__( 'You currently have version %s installed. Please update to version %s.','qed' ), $current_version, $new_version ); ?></p>
	</div>
	<?php if ( ! empty( $updates_flat_log ) ) {?>
		<div>
			<h3><?php esc_html_e( 'Changelog','qed' ); ?></h3>
			<ul id="changeLogList">
				<?php foreach ( $updates_flat_log as $message ) { ?>
					<li><?php echo esc_html( $message ); ?></li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
</div>