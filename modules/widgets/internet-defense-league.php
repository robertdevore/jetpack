<?php /*

**************************************************************************

Plugin Name:  Internet Defense League
Description:  Displays your support for the Internet Defense League.
Author:       Automattic Inc.
Author URI:   https://automattic.com/

**************************************************************************/

class Jetpack_Internet_Defense_League_Widget extends WP_Widget {

	public $defaults = array();

	public $variant;
	public $variants = array();

	public $campaign;
	public $campaigns  = array();
	public $no_current = true;

	public $badge;
	public $badges = array();

	function __construct() {
		parent::__construct(
			'internet_defense_league_widget',
			/** This filter is documented in modules/widgets/facebook-likebox.php */
			apply_filters( 'jetpack_widget_name_widget', esc_html__( 'Internet Defense League', 'jetpack' ) ),
			array(
				'description' => __( 'Show your support for the Internet Defense League.', 'internetdefenseleague' ),
			)
		);

		// When enabling campaigns other than 'none' or empty, change $no_current to false above.
		$this->campaigns = array(
			''       => __( 'All current and future campaigns', 'internetdefenseleague' ),
			// 'nsa' => __( 'NSA Protest on July 4th, 2013', 'internetdefenseleague' ),
			'none'   => __( 'None, just display the badge please', 'internetdefenseleague' ),
		);

		$this->variants = array(
			'banner' => __( 'Banner at the top of my site', 'internetdefenseleague' ),
			'modal'  => __( 'Modal (Overlay Box)', 'internetdefenseleague' ),
		);

		$this->badges = array(
			'shield_badge'   => __( 'Shield Badge', 'internetdefenseleague' ),
			'super_badge'    => __( 'Super Badge', 'internetdefenseleague' ),
			'side_bar_badge' => __( 'Red Cat Badge', 'internetdefenseleague' ),
			'none'           => __( 'Don\'t display a badge (just the campaign)', 'internetdefenseleague' ),
		);

		$this->defaults = array(
			'campaign' => key( $this->campaigns ),
			'variant'  => key( $this->variants ),
			'badge'    => key( $this->badges ),
		);
	}

	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		if ( 'none' != $instance['badge'] ) {
			if ( ! isset( $this->badges[ $instance['badge'] ] ) ) {
				$instance['badge'] = $this->defaults['badge'];
			}
			echo $args['before_widget'];
			echo '<p><a href="https://internetdefenseleague.org/"><img src="' . esc_url( 'https://internetdefenseleague.org/images/badges/final/' . $instance['badge'] . '.png' ) . '" alt="Member of The Internet Defense League" style="max-width: 100%; height: auto;" /></a></p>';
			echo $args['after_widget'];
			do_action( 'jetpack_stats_extra', 'widget_view', 'internet_defense_league' );
		}

		if ( 'none' != $instance['campaign'] ) {
			$this->campaign = $instance['campaign'];
			$this->variant  = $instance['variant'];
			add_action( 'wp_footer', array( $this, 'footer_script' ) );
			do_action( 'jetpack_stats_extra', 'widget_view', 'internet_defense_league' );
		}
	}

	public function footer_script() {
		if ( ! isset( $this->campaigns[ $this->campaign ] ) )
			$this->campaign = $this->defaults['campaign'];

		if ( ! isset( $this->variants[ $this->variant ] ) )
			$this->variant = $this->defaults['variant'];
		?>
		<script type="text/javascript">
			window._idl = {};
			_idl.campaign = "<?php echo esc_js( $this->campaign ); ?>";
			_idl.variant = "<?php echo esc_js( $this->variant ); ?>";
			(function() {
				var idl = document.createElement('script');
				idl.type = 'text/javascript';
				idl.async = true;
				idl.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'members.internetdefenseleague.org/include/?url=' + (_idl.url || '') + '&campaign=' + (_idl.campaign || '') + '&variant=' + (_idl.variant || 'banner');
				document.getElementsByTagName('body')[0].appendChild(idl);
			})();
		</script>
		<?php
	}

	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		// Hide first two form fields if no current campaigns.
		if ( false === $this->no_current ) {
			echo '<p><label>';
			echo __( 'Which Internet Defense League campaign do you want to participate in?', 'internetdefenseleague' ) . '<br />';
			$this->select( 'campaign', $this->campaigns, $instance['campaign'] );
			echo '</label></p>';

			echo '<p><label>';
			echo __( 'How do you want to promote the campaign?', 'internetdefenseleague' ) . '<br />';
			$this->select( 'variant', $this->variants, $instance['variant'] );
			echo '</label></p>';
		}

		echo '<p><label>';
		echo __( 'Which badge would you like to display?', 'internetdefenseleague' ) . '<br />';
		$this->select( 'badge', $this->badges, $instance['badge'] );
		echo '</label></p>';

		/* translators: %s is a name of an internet campaign called the "Internet Defense League" */
		echo '<p>' . sprintf( _x( 'Learn more about the %s', 'the Internet Defense League', 'internetdefenseleague' ), '<a href="https://www.internetdefenseleague.org/">Internet Defense League</a>' ) . '</p>';
	}

	public function select( $field_name, $options, $default = null ) {
		echo '<select class="widefat" name="' . $this->get_field_name( $field_name ) . '">';
		foreach ( $options as $option_slug => $option_name ) {
			echo '<option value="' . esc_attr( $option_slug ) . '"' . selected( $option_slug, $default, false ) . '>' . esc_html( $option_name ) . '</option>';
		}
		echo '</select>';
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['campaign'] = ( isset( $new_instance['campaign'] ) && isset( $this->campaigns[ $new_instance['campaign'] ] ) ) ? $new_instance['campaign'] : $this->defaults['campaign'];
		$instance['variant']  = ( isset( $new_instance['variant'] )  && isset( $this->variants[  $new_instance['variant']  ] ) ) ? $new_instance['variant']  : $this->defaults['variant'];
		$instance['badge']    = ( isset( $new_instance['badge'] )    && isset( $this->badges[    $new_instance['badge'] ] ) )    ? $new_instance['badge']    : $this->defaults['badge'];

		return $instance;
	}
}

function jetpack_internet_defense_league_init() {
	if ( Jetpack::is_active() ) {
		register_widget( 'Jetpack_Internet_Defense_League_Widget' );
	}
}

add_action( 'widgets_init', 'jetpack_internet_defense_league_init' );
