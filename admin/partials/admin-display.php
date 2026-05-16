<?php
/**
 * Admin settings page template.
 *
 * @package HolyRosary
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap holy-rosary-admin">
	<h1>
		<span class="dashicons dashicons-heart" style="color:#E24B4A;font-size:28px;margin-right:8px;vertical-align:middle;"></span>
		<?php esc_html_e( 'Holy Rosary', 'holy-rosary' ); ?>
		<span class="holy-rosary-version">v<?php echo esc_html( HOLY_ROSARY_VERSION ); ?></span>
	</h1>

	<?php settings_errors( 'holy_rosary_settings' ); ?>

	<div class="holy-rosary-admin-grid">
		<!-- Settings form -->
		<div class="holy-rosary-admin-main">
			<form method="post" action="options.php">
				<?php
				settings_fields( 'holy_rosary_settings_group' );
				do_settings_sections( 'holy-rosary' );
				submit_button( esc_html__( 'Save Settings', 'holy-rosary' ) );
				?>
			</form>
		</div>

		<!-- Sidebar: shortcode usage -->
		<div class="holy-rosary-admin-sidebar">
			<div class="holy-rosary-admin-box">
				<h3><?php esc_html_e( 'How to Use', 'holy-rosary' ); ?></h3>
				<p><?php esc_html_e( 'Add the Rosary to any page or post using the shortcode:', 'holy-rosary' ); ?></p>
				<code>[holy_rosary]</code>

				<h4><?php esc_html_e( 'Shortcode Options', 'holy-rosary' ); ?></h4>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Attribute', 'holy-rosary' ); ?></th>
							<th><?php esc_html_e( 'Values', 'holy-rosary' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>mystery</code></td>
							<td><code>auto</code>, <code>joyful</code>, <code>luminous</code>, <code>sorrowful</code>, <code>glorious</code></td>
						</tr>
						<tr>
							<td><code>show_wall</code></td>
							<td><code>true</code>, <code>false</code></td>
						</tr>
						<tr>
							<td><code>show_stats</code></td>
							<td><code>true</code>, <code>false</code></td>
						</tr>
					</tbody>
				</table>

				<p><strong><?php esc_html_e( 'Example:', 'holy-rosary' ); ?></strong></p>
				<code>[holy_rosary mystery="joyful" show_wall="false"]</code>
			</div>

			<div class="holy-rosary-admin-box">
				<h3><?php esc_html_e( 'Gutenberg Block', 'holy-rosary' ); ?></h3>
				<p><?php esc_html_e( 'Search for "Holy Rosary" in the Gutenberg block inserter to add the prayer guide to any page.', 'holy-rosary' ); ?></p>
			</div>
		</div>
	</div>
</div>
