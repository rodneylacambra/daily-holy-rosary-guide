<?php
/**
 * Template for the Holy Rosary frontend display.
 *
 * Variables available:
 * @var array $atts Shortcode / block attributes.
 *
 * @package HolyRosary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Unique instance ID (supports multiple shortcodes on one page).
static $instance = 0;
$instance++;
$element_id = 'holy-rosary-app-' . $instance;
?>
<div id="<?php echo esc_attr( $element_id ); ?>"
	class="holy-rosary-app"
	data-mystery="<?php echo esc_attr( $atts['mystery'] ?? 'auto' ); ?>"
	data-show-wall="<?php echo esc_attr( $atts['show_wall'] ? 'true' : 'false' ); ?>"
	data-show-stats="<?php echo esc_attr( $atts['show_stats'] ? 'true' : 'false' ); ?>"
	role="main"
	aria-label="<?php esc_attr_e( 'Holy Rosary Prayer Guide', 'holy-rosary' ); ?>">

	<!-- Date banner -->
	<div class="hr-date-banner" id="<?php echo esc_attr( $element_id ); ?>-date-banner">
		<span class="hr-date-left" id="<?php echo esc_attr( $element_id ); ?>-date"></span>
		<span class="hr-date-right" id="<?php echo esc_attr( $element_id ); ?>-mystery-today"></span>
	</div>

	<!-- Channel bar -->
	<div class="hr-channel-bar">
		<div class="hr-avatar" aria-hidden="true">🙏</div>
		<div class="hr-channel-info">
			<h2><?php esc_html_e( 'Pray the Rosary with Me', 'holy-rosary' ); ?></h2>
			<p><?php esc_html_e( 'Step-by-step guide • All 5 mysteries', 'holy-rosary' ); ?></p>
		</div>
		<span class="hr-live-badge" aria-label="<?php esc_attr_e( 'Live', 'holy-rosary' ); ?>">● LIVE</span>
	</div>

	<!-- Progress label -->
	<p class="hr-progress-label" id="<?php echo esc_attr( $element_id ); ?>-progress" aria-live="polite">
		<?php esc_html_e( 'Your progress — tap Next to begin', 'holy-rosary' ); ?>
	</p>

	<!-- Rosary canvas -->
	<canvas id="<?php echo esc_attr( $element_id ); ?>-canvas"
		class="hr-canvas"
		aria-hidden="true"></canvas>

	<!-- Mystery tabs -->
	<div class="hr-mystery-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Mystery Sets', 'holy-rosary' ); ?>">
		<button class="hr-tab" role="tab" data-mystery="0" id="<?php echo esc_attr( $element_id ); ?>-tab-0">
			<?php esc_html_e( 'Joyful', 'holy-rosary' ); ?>
		</button>
		<button class="hr-tab" role="tab" data-mystery="1" id="<?php echo esc_attr( $element_id ); ?>-tab-1">
			<?php esc_html_e( 'Luminous', 'holy-rosary' ); ?>
		</button>
		<button class="hr-tab" role="tab" data-mystery="2" id="<?php echo esc_attr( $element_id ); ?>-tab-2">
			<?php esc_html_e( 'Sorrowful', 'holy-rosary' ); ?>
		</button>
		<button class="hr-tab" role="tab" data-mystery="3" id="<?php echo esc_attr( $element_id ); ?>-tab-3">
			<?php esc_html_e( 'Glorious', 'holy-rosary' ); ?>
		</button>
	</div>

	<!-- Step card -->
	<div class="hr-step-card" id="<?php echo esc_attr( $element_id ); ?>-card" role="region" aria-live="polite">
		<div class="hr-step-number" id="<?php echo esc_attr( $element_id ); ?>-step-num"></div>
		<h3 class="hr-step-title"  id="<?php echo esc_attr( $element_id ); ?>-step-title"></h3>
		<p  class="hr-step-desc"   id="<?php echo esc_attr( $element_id ); ?>-step-desc"></p>
		<div id="<?php echo esc_attr( $element_id ); ?>-tag-box"></div>
		<div id="<?php echo esc_attr( $element_id ); ?>-prayer-box"></div>
		<div id="<?php echo esc_attr( $element_id ); ?>-mystery-box"></div>
		<div id="<?php echo esc_attr( $element_id ); ?>-decade-dots"></div>
	</div>

	<!-- Navigation -->
	<div class="hr-nav-row">
		<button class="hr-btn hr-btn-back"
			id="<?php echo esc_attr( $element_id ); ?>-btn-back"
			aria-label="<?php esc_attr_e( 'Previous step', 'holy-rosary' ); ?>">
			← <?php esc_html_e( 'Back', 'holy-rosary' ); ?>
		</button>
		<button class="hr-btn hr-btn-next hr-btn-primary"
			id="<?php echo esc_attr( $element_id ); ?>-btn-next"
			aria-label="<?php esc_attr_e( 'Next step', 'holy-rosary' ); ?>">
			<?php esc_html_e( 'Next', 'holy-rosary' ); ?> →
		</button>
		<span class="hr-step-counter" id="<?php echo esc_attr( $element_id ); ?>-counter"
			aria-live="polite"></span>
	</div>

</div><!-- /.holy-rosary-app -->

<script>
// Boot this specific instance once the global app is ready.
document.addEventListener( 'DOMContentLoaded', function () {
	if ( typeof HolyRosary !== 'undefined' ) {
		HolyRosary.init( '<?php echo esc_js( $element_id ); ?>' );
	}
} );
</script>
