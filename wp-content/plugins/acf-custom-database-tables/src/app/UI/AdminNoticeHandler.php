<?php

namespace ACFCustomDatabaseTables\UI;

/**
 * Class AdminNoticeHandler
 * @package ACFCustomDatabaseTables\UI
 */
class AdminNoticeHandler {

	/**
	 * @var array
	 */
	protected $error_messages = [];

	/**
	 * @var array
	 */
	protected $success_messages = [];

	/**
	 * @var array
	 */
	protected $warning_messages = [];

	/**
	 * @var array
	 */
	protected $info_messages = [];

	/**
	 * @var bool
	 */
	protected $has_initialised = false;

	/**
	 * The hook that this object renders output on
	 *
	 * @var string
	 */
	protected $hook = 'admin_notices';

	/**
	 *
	 */
	public function init() {
		if ( ! $this->has_initialised ) {
			$this->has_initialised = true;
			add_action( $this->hook, [ $this, 'render' ] );
		}
	}

	/**
	 * Override the target hook for rendering notices
	 *
	 * @param $hook
	 */
	public function set_target_hook( $hook ) {
		$this->hook = $hook;
	}

	/**
	 * @param $message
	 */
	public function add_error( $message ) {
		$this->error_messages[] = $message;
	}

	/**
	 * @param $message
	 */
	public function add_success( $message ) {
		$this->success_messages[] = $message;
	}

	/**
	 * @param $message
	 */
	public function add_warning( $message ) {
		$this->warning_messages[] = $message;
	}

	/**
	 * @param $message
	 */
	public function add_info( $message ) {
		$this->info_messages[] = $message;
	}

	/**
	 * Hooked render method
	 */
	public function render() {
		$this->render_type( 'error_messages', 'notice-error' );
		$this->render_type( 'success_messages', 'notice-success' );
		$this->render_type( 'warning_messages', 'notice-warning' );
		$this->render_type( 'info_messages', 'notice-info' );
	}

	/**
	 * The actual renderer. This consolidates multiple messages of a type into one notification so we don't bombard the
	 * UI with too many at once.
	 *
	 * @param $type
	 * @param string $class
	 */
	protected function render_type( $type, $class = '' ) {
		if ( $this->{$type} ) {
			?>
			<div class="notice <?php echo $class ?>">
				<?php if ( count( $this->{$type} ) > 1 ): ?>
					<ul>
						<?php foreach ( $this->{$type} as $message ): ?>
							<li><?php echo $message ?></li>
						<?php endforeach; ?>
					</ul>
				<?php elseif ( $this->contains_p_tags( $this->{$type}[0] ) ): ?>
					<?php echo $this->{$type}[0] ?>
				<?php else: ?>
					<p><?php echo $this->{$type}[0] ?></p>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Checks to see if the message already contains <p> tags
	 *
	 * @param $message
	 *
	 * @return false|int
	 */
	private function contains_p_tags( $message ) {
		return preg_match( '/<\s*p[^>]*>/', $message );
	}

}