<?php
/**
 * @package WPSEO\Admin\Notifiers
 */

/**
 * Class Yoast_Plugin_Conflict_Notifier
 */
class Yoast_Plugin_Conflict_Notifier implements Yoast_Notifier_Interface {

	/** @var Yoast_Plugin_Conflict Plugin Conflict object that spawned this notification */
	private $yoast_plugin_conflict;

	/** @var string Plugin section thats being checked */
	private $plugin_section;

	/** @var string Readable version of the plugin section */
	private $readable_plugin_section;

	/**
	 * Yoast_Plugin_Conflict_Notifier constructor.
	 *
	 * @param Yoast_Plugin_Conflict $yoast_plugin_conflict   Plugin Conflict object.
	 * @param string                $plugin_section          Plugin section to display notification on.
	 * @param string                $readable_plugin_section Textual representation.
	 */
	public function __construct(
		Yoast_Plugin_Conflict $yoast_plugin_conflict,
		$plugin_section,
		$readable_plugin_section
	) {
		$this->yoast_plugin_conflict   = $yoast_plugin_conflict;
		$this->plugin_section          = $plugin_section;
		$this->readable_plugin_section = $readable_plugin_section;
	}

	/**
	 * Check if the cause for the notification is present
	 *
	 * @return bool True if notification is no longer relevant, False if it is still active.
	 */
	public function notify() {
		return $this->yoast_plugin_conflict->check_for_conflicts( $this->plugin_section );
	}

	/**
	 * Create the notification
	 *
	 * @return Yoast_Notification
	 */
	public function get_notification() {
		$plugins_as_string = $this->yoast_plugin_conflict->get_conflicting_plugins_as_string( $this->plugin_section );
		$active_plugins    = $this->yoast_plugin_conflict->get_active_plugins();

		$error_message = '<h3>' . __( 'Warning!', 'wordpress-seo' ) . '</h3>';

		$error_message .= '<p>';
		$error_message .= sprintf(
		/* translators: %1$s: 'Facebook & Open Graph' plugin name(s) of possibly conflicting plugin(s), %2$s to Yoast SEO */
			__( 'The %1$s plugin(s) might cause issues when used in conjunction with %2$s.', 'wordpress-seo' ),
			$plugins_as_string,
			'Yoast SEO'
		);
		$error_message .= '</p>';

		$error_message .= sprintf( $this->readable_plugin_section, 'Yoast SEO', $plugins_as_string ) . '<br/><br/>';

		$error_message .= '<p><strong>' . __( 'Recommended solution', 'wordpress-seo' ) . '</strong><br/>';
		$error_message .= sprintf(
		/* Translators: %1$s: 'Facebook & Open Graph' plugin name(s) of possibly conflicting plugin(s). %2$s to Yoast SEO */
			__( 'We recommend you deactivate %1$s and have another look at your %2$s configuration using the button above.',
				'wordpress-seo' ),
			$plugins_as_string,
			'Yoast SEO'
		);
		$error_message .= '</p>';

		foreach ( $active_plugins[ $this->plugin_section ] as $plugin_file ) {

			$href = wp_nonce_url(
				'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all',
				'deactivate-plugin_' . $plugin_file
			);

			$error_message .= '<a target="_blank" class="button-primary" href="' . $href . '">';
			/* translators: %s: 'Facebook' plugin name of possibly conflicting plugin */
			$error_message .= sprintf( __( 'Deactivate %s', 'wordpress-seo' ),
				WPSEO_Utils::get_plugin_name( $plugin_file ) );
			$error_message .= '</a> ';
		}

		$error_message .= '<p class="alignright"><small>';
		/* Translators: %1$s expands to Yoast SEO. */
		$error_message .= sprintf( __( 'This warning is generated by %1$s.', 'wordpress-seo' ), 'Yoast SEO' );
		$error_message .= '</small></p><div class="clear"></div>';

		$options = array(
			'type'                  => 'error yoast-dismissible',
			'id'                    => 'wpseo-plugin-conflict',
			'capabilities_required' => array( 'update_plugins' ),
			'data_json'             => array(
				'section' => $this->plugin_section,
				'plugins' => $active_plugins[ $this->plugin_section ],
			),
		);

		// Create the notification.
		return new Yoast_Notification(
			$error_message,
			$options
		);
	}
}
