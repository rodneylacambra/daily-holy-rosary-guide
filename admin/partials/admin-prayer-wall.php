<?php
/**
 * Admin prayer wall moderation template.
 *
 * @package HolyRosary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db          = new Holy_Rosary_Database();
$pending     = $db->get_pending_intentions();

// Handle approve/reject actions.
if ( isset( $_POST['holy_rosary_moderate'] ) ) {
	check_admin_referer( 'holy_rosary_moderate_intention' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'holy-rosary' ) );
	}

	$intention_id = absint( $_POST['intention_id'] ?? 0 );
	$action       = sanitize_text_field( $_POST['intention_action'] ?? '' );

	if ( $intention_id && in_array( $action, array( 'approved', 'rejected' ), true ) ) {
		$db->moderate_intention( $intention_id, $action );
		$pending = $db->get_pending_intentions(); // Refresh.

		$msg = 'approved' === $action
			? esc_html__( 'Intention approved.', 'holy-rosary' )
			: esc_html__( 'Intention rejected.', 'holy-rosary' );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
	}
}
?>
<div class="wrap holy-rosary-admin">
	<h1>
		<span class="dashicons dashicons-heart" style="color:#E24B4A;font-size:28px;margin-right:8px;vertical-align:middle;"></span>
		<?php esc_html_e( 'Prayer Wall — Moderation', 'holy-rosary' ); ?>
	</h1>

	<?php if ( empty( $pending ) ) : ?>
		<p><?php esc_html_e( 'No pending intentions. All caught up! 🙏', 'holy-rosary' ); ?></p>
	<?php else : ?>
		<p>
			<?php
			printf(
				/* translators: %d: number of pending intentions */
				esc_html( _n( '%d intention awaiting approval.', '%d intentions awaiting approval.', count( $pending ), 'holy-rosary' ) ),
				count( $pending )
			);
			?>
		</p>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Intention', 'holy-rosary' ); ?></th>
					<th><?php esc_html_e( 'Submitted By', 'holy-rosary' ); ?></th>
					<th><?php esc_html_e( 'Date', 'holy-rosary' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'holy-rosary' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $pending as $item ) : ?>
					<tr>
						<td><?php echo esc_html( $item->intention ); ?></td>
						<td>
							<?php echo $item->is_anonymous
								? esc_html__( 'Anonymous', 'holy-rosary' )
								: esc_html( $item->display_name ); ?>
						</td>
						<td><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $item->created_at ) ) ); ?></td>
						<td>
							<form method="post" style="display:inline;">
								<?php wp_nonce_field( 'holy_rosary_moderate_intention' ); ?>
								<input type="hidden" name="intention_id"     value="<?php echo absint( $item->id ); ?>">
								<input type="hidden" name="intention_action" value="approved">
								<button type="submit" name="holy_rosary_moderate" class="button button-primary button-small">
									<?php esc_html_e( 'Approve', 'holy-rosary' ); ?>
								</button>
							</form>
							<form method="post" style="display:inline;margin-left:4px;">
								<?php wp_nonce_field( 'holy_rosary_moderate_intention' ); ?>
								<input type="hidden" name="intention_id"     value="<?php echo absint( $item->id ); ?>">
								<input type="hidden" name="intention_action" value="rejected">
								<button type="submit" name="holy_rosary_moderate" class="button button-small" style="color:#dc3232;">
									<?php esc_html_e( 'Reject', 'holy-rosary' ); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
