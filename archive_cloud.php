<?php
/*
Plugin Name: Wordpress Archive Cloud
Plugin URI: http://thebrent.net/projects/wordpress-archive-cloud/
Description: Archive widget that uses tag-cloud functionality to display archive links with relative size, like the tag cloud.
Version: 0.1
Author: Brent Maxwell
Author URI: http://thebrent.net/
License: GPL2

*/

class WP_Widget_ArchiveCloud extends WP_Widget {
	
	public function __construct() {
		$widget_ops = array('classname' => 'widget_archive_cloud', 'description' => __( 'A monthly archive of your site&#8217;s Posts as a tag cloud.') );
		parent::__construct('archives_cloud', __('Archive Cloud'), $widget_ops);
	}
	public function widget( $args, $instance ) {
		add_filter('tag_cloud_sort',array($this,'sort_terms'));
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Archives' ) : $instance['title'], $instance, $this->id_base );
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$archives = wp_get_archives(array(
			'type'            => 'monthly',
			'format'          => 'custom',
			'echo'			  => 0,
			'show_post_count' => 1,
		));
		$archives = str_replace("\n","",$archives);
		$archives = str_replace("\t","",$archives);
		preg_match_all("/[^<]*(<a href=[']([^']+)[']>([^<]+)<\/a>&nbsp;\(([^\)]+)\))/s",$archives,$matches,PREG_SET_ORDER);
		$archives = array();
		foreach($matches as $key=>$match){
			$obj = array(
				'link' => $match[2],
				'name' => $match[3],
				'count' => $match[4],
				'id'=> $key
			);
			if($c){$obj['name'] .= ' ('.$obj['count'].')';}
			$archives[] = (object)$obj;
		}
		echo wp_generate_tag_cloud($archives,array('orderby'=>'id'));
		echo $args['after_widget'];
		remove_filter('tag_cloud_sort',array($this,'sort_terms'));
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		return $instance;
	}
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$title = strip_tags($instance['title']);
		$count = $instance['count'] ? 'checked="checked"' : '';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
		</p>
<?php
	}
	
	function sort_terms($terms){
		$terms[] = '' ;
		return $terms;
	}
}
add_action( 'widgets_init', function(){ register_widget( 'WP_Widget_ArchiveCloud' );} );