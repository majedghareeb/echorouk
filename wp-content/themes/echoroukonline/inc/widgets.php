<?php
/**
 * Sidebars and block-compatible news widgets.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_register_sidebars() {
	$sidebars = array(
		'sidebar-main'    => esc_html__( 'Main Sidebar', 'echoroukonline' ),
		'sidebar-article' => esc_html__( 'Article Sidebar', 'echoroukonline' ),
		'footer-1'        => esc_html__( 'Footer Column 1', 'echoroukonline' ),
		'footer-2'        => esc_html__( 'Footer Column 2', 'echoroukonline' ),
		'footer-3'        => esc_html__( 'Footer Column 3', 'echoroukonline' ),
	);

	foreach ( $sidebars as $id => $name ) {
		register_sidebar(
			array(
				'name'          => $name,
				'id'            => $id,
				'description'   => esc_html__( 'Add news widgets or block content here.', 'echoroukonline' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'echorouk_register_sidebars' );

class Echorouk_News_List_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'echorouk_news_list',
			esc_html__( 'Echourouk News List', 'echoroukonline' ),
			array( 'description' => esc_html__( 'Reusable latest, most-read, editorial, category, video, PDF, or newsletter block.', 'echoroukonline' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$mode  = ! empty( $instance['mode'] ) ? sanitize_key( $instance['mode'] ) : 'latest';
		$count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;

		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		if ( 'newsletter' === $mode ) {
			get_template_part( 'template-parts/components/newsletter' );
		} else {
			$this->render_posts( $mode, $count, isset( $instance['category'] ) ? absint( $instance['category'] ) : 0 );
		}

		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title    = isset( $instance['title'] ) ? $instance['title'] : '';
		$mode     = isset( $instance['mode'] ) ? $instance['mode'] : 'latest';
		$count    = isset( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		$category = isset( $instance['category'] ) ? absint( $instance['category'] ) : 0;
		$modes    = echorouk_widget_modes();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'echoroukonline' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'mode' ) ); ?>"><?php esc_html_e( 'Type', 'echoroukonline' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'mode' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'mode' ) ); ?>">
				<?php foreach ( $modes as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $mode, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e( 'Category ID', 'echoroukonline' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>" type="number" value="<?php echo esc_attr( $category ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Count', 'echoroukonline' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="12" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$modes                 = echorouk_widget_modes();
		$instance             = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$mode                 = isset( $new_instance['mode'] ) ? sanitize_key( $new_instance['mode'] ) : 'latest';
		$instance['mode']     = array_key_exists( $mode, $modes ) ? $mode : 'latest';
		$instance['category'] = isset( $new_instance['category'] ) ? absint( $new_instance['category'] ) : 0;
		$count                = isset( $new_instance['count'] ) ? absint( $new_instance['count'] ) : 5;
		$instance['count']    = min( 12, max( 1, $count ) );

		return $instance;
	}

	private function render_posts( $mode, $count, $category ) {
		$query_args = array(
			'post_type'      => echorouk_news_post_types(),
			'posts_per_page' => $count,
		);

		if ( 'most_read' === $mode ) {
			$query_args['meta_key'] = 'echorouk_view_count';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'DESC';
		} elseif ( 'editorial' === $mode ) {
			$query_args['meta_key']   = 'editorial_pick';
			$query_args['meta_value'] = 1;
			$query_args['orderby']    = 'date';
		} elseif ( 'video' === $mode ) {
			$query_args['post_type'] = 'video';
		} elseif ( 'document' === $mode ) {
			$query_args['post_type'] = 'document';
		}

		if ( $category ) {
			$query_args['cat'] = $category;
		}

		$posts = echorouk_get_cached_posts( 'widget_' . $mode . '_' . $category . '_' . $count, $query_args, 300 );
		echo '<div class="widget-news-list">';
		foreach ( $posts as $post ) {
			echorouk_news_card( $post->ID, 'compact' );
		}
		echo '</div>';
	}
}

function echorouk_widget_modes() {
	return array(
		'latest'     => esc_html__( 'Latest news', 'echoroukonline' ),
		'most_read'  => esc_html__( 'Most read', 'echoroukonline' ),
		'editorial'  => esc_html__( 'Editorial recommendations', 'echoroukonline' ),
		'category'   => esc_html__( 'Category news', 'echoroukonline' ),
		'video'      => esc_html__( 'Video list', 'echoroukonline' ),
		'document'   => esc_html__( 'PDF/document list', 'echoroukonline' ),
		'newsletter' => esc_html__( 'Newsletter subscription', 'echoroukonline' ),
	);
}

function echorouk_register_widgets() {
	register_widget( 'Echorouk_News_List_Widget' );
}
add_action( 'widgets_init', 'echorouk_register_widgets' );
