<?php
/*
Plugin Name: Showcase Portfolio
Plugin URI: http://www.ajbiscaro.com
Description: A plugin for showcasing portfolio.
Version: 1.0
Author: Ardel John Biscaro
Author URI: http://www.ajbiscaro.com
License: GPLv2
*/

/** Back-end **/

//Create Showcase Portfolio Custom Post Type
add_action( 'init', 'create_showcase_portfolio' );
function create_showcase_portfolio() {
	$labels = array(
		'name' => 'Showcase Portfolio',
		'singular_name' => 'Showcase Portfolio',
		'add_new' => 'Add New Item',
		'add_new_item' => 'Add New Portfolio Item',
		'edit' => 'Edit',
		'edit_item' => 'Edit Portfolio Item',
		'new_item' => 'New Portfolio Item',
		'view' => 'View',
		'view_item' => 'View Portfolio Item',
		'search_items' => 'Search Portfolio',
		'not_found' => 'No Portfolio Item found',
		'not_found_in_trash' => 'No Portfolio Item found in Trash',
		'parent' => 'Parent Portfolio'		
	);
	
	$args = array(
		'labels' => $labels,
		'public' => true,
		'menu_position' => 25,
		'supports' => array(
			'title',
			'thumbnail',
			'editor',
		),
		'taxonomies' => array( 'shc-portfolio-category' ),
		'menu_icon' => 'dashicons-format-gallery',
		'has_archive' => true
	);
	
    register_post_type( 'shc-portfolio', $args );
	
	//Showcase Portfolio Categories - Taxonomies
	$labels = array(
		'name' =>'Category',
		'singular_name' =>'Category',
		'search_items' => 'Search Categories',
		'all_items' => 'All Categories',
		'parent_item' => 'Parent Category',
		'parent_item_colon' => 'Parent Category:',
		'edit_item' => 'Edit Category', 
		'update_item' => 'Update Category',
		'add_new_item' => 'Add New Category',
		'new_item_name' => 'New Category Name',
		'menu_name' => 'Categories',
	); 
	
	$args = array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'shc-portfolio-category' )	
	);
	
	register_taxonomy( 'shc-portfolio-category', array( 'shc-portfolio' ), $args );
}

//Create Filter by Category
add_action( 'restrict_manage_posts', 'filter_portfolio_category' );
function filter_portfolio_category() {
    $screen = get_current_screen();
    global $wp_query;
    if ( $screen->post_type == 'shc-portfolio' ) {
        wp_dropdown_categories( array(
            'show_option_all' => 'Show All Categories',
			'show_option_none' => 'No Categories',
            'taxonomy' => 'shc-portfolio-category',
            'name' => 'shc-portfolio-category',
            'orderby' => 'name',
            'selected' => ( isset( $wp_query->query['shc-portfolio-category'] ) ? $wp_query->query['shc-portfolio-category'] : '' ),
            'hierarchical' => false,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => true,
        ) );
    }
}

//Perform filtering by category selected
add_filter( 'parse_query','perform_filtering_category' );
function perform_filtering_category( $query ) {
    $qv = &$query->query_vars;
    if ( ( $qv['shc-portfolio-category'] ) && is_numeric( $qv['shc-portfolio-category'] ) ) {
        $term = get_term_by( 'id', $qv['shc-portfolio-category'], 'shc-portfolio-category' );
        $qv['shc-portfolio-category'] = $term->slug;
    }
}

//Add Showcase Portfolio Metaboxes
add_action( 'admin_init', 'create_portfolio_meta_box' );
function create_portfolio_meta_box() {
    add_meta_box( 'shc_portfolio_meta_box',
        'Showcase Portfolio Details',
        'display_portfolio_meta_box',
        'shc-portfolio', 
		'normal', 
		'high'
    );
}


/**
 * Enqueue the date picker
 */
add_action( 'admin_enqueue_scripts', 'portfolio_date_picker_scripts' );
function portfolio_date_picker_scripts() {
	// Enqueue Datepicker
	global $post_type;
	if( 'shc-portfolio' != $post_type ) { return; }
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	
	//load custom script
	wp_enqueue_script('shw_prtf_js', plugins_url('/js/shw_prtf.js', __FILE__),null,null,true );
}


//Display Showcase Portfolio Metaboxes
function display_portfolio_meta_box( $shc_portfolio ) {
    $portfolio_link = esc_html( get_post_meta( $shc_portfolio->ID, 'portfolio_link', true ) );
	$client_name = esc_html( get_post_meta( $shc_portfolio->ID, 'client_name', true ) );
	$my_role = esc_html( get_post_meta( $shc_portfolio->ID, 'my_role', true ) );	
	$date_accomplished = esc_html( get_post_meta( $shc_portfolio->ID, 'date_accomplished', true ) );
	$set_order = esc_html( get_post_meta( $shc_portfolio->ID, 'set_order', true ) );
    ?>
	<div class="inside">
		<div class="wrap">
			<p>
				<label for="portfolio_link">Website Link:</label> <br/>
				<input type="text" name="portfolio_link" value="<?php echo $portfolio_link; ?>" style="width:100%" />
			</p>
			
			<p>
				<label for="client_name">Client's Name:</label> <br/>
				<input type="text" name="client_name" value="<?php echo $client_name; ?>" style="width:80%" />
			</p>
			
			<p>
				<label for="my_role">My Role:</label> <br/>
				<input type="text" name="my_role" value="<?php echo $my_role; ?>" style="width:100%" />
			</p>
			
			<p>
				<label for="date_accomplished">Date Accomplished:</label> <br/>
				<input type="text" name="date_accomplished" id="date_accomplished" value="<?php echo $date_accomplished; ?>" style="width:15%" />
				<span class="description">format: mm/dd/yyyy</span>
			</p>
			
			<p>
				<label for="set_order">Set Order:</label> <br/>
				<input type="text" name="set_order" value="<?php echo $set_order; ?>" class="small-text" />
			</p>
			
		</div>
	</div>

<?php
}

//Save Showcase Portfolio Metaboxes Values
add_action( 'save_post', 'save_portfolio_metabox_values', 10, 2 );
function save_portfolio_metabox_values( $shc_portfolio_id, $shc_portfolio ) {
    // Check post type for showcase portfolio
    if ( $shc_portfolio->post_type == 'shc-portfolio' ) {
        // Store data in post meta table if present in post data
        if ( isset( $_POST['portfolio_link'] ) && $_POST['portfolio_link'] != '' ) {
            update_post_meta( $shc_portfolio_id, 'portfolio_link', $_POST['portfolio_link'] );
        }
		if ( isset( $_POST['client_name'] ) && $_POST['client_name'] != '' ) {
            update_post_meta( $shc_portfolio_id, 'client_name', $_POST['client_name'] );
        }
		if ( isset( $_POST['my_role'] ) && $_POST['my_role'] != '' ) {
            update_post_meta( $shc_portfolio_id, 'my_role', $_POST['my_role'] );
        }
		if ( isset( $_POST['date_accomplished'] ) && $_POST['date_accomplished'] != '' ) {
            update_post_meta( $shc_portfolio_id, 'date_accomplished', $_POST['date_accomplished'] );
        }
		if ( isset( $_POST['set_order'] ) && $_POST['set_order'] != '' ) {
            update_post_meta( $shc_portfolio_id, 'set_order', $_POST['set_order'] );
        }
    }
}

//Menu Icon Gallery
function add_menu_icons_styles(){
?> 
	<style>
		#adminmenu .menu-icon-gallery div.wp-menu-image:before {
			content: "\f161";
		}
	</style>

<?php
} 
add_action( 'admin_head', 'add_menu_icons_styles' );


//Setting Page
add_action('admin_menu' , 'portfolio_settings');  
function portfolio_settings() {
    add_submenu_page('edit.php?post_type=shc-portfolio', 'Portfolio Settings', 'Settings', 'edit_posts', basename(__FILE__), 'portfolio_settings_display');
	add_action('admin_init', 'portfolio_settings_store');
}

function portfolio_settings_store() {
    register_setting('portfolio_settings', 'portfolio_container_width');
    register_setting('portfolio_settings', 'portfolio_post_per_page_list');
	register_setting('portfolio_settings', 'portfolio_post_per_page_content');
	register_setting('portfolio_settings', 'portfolio_thumb_size_w');
    register_setting('portfolio_settings', 'portfolio_thumb_size_h');
    register_setting('portfolio_settings', 'portfolio_img_size_w');
    register_setting('portfolio_settings', 'portfolio_img_size_h');
	register_setting('portfolio_settings', 'portfolio_order');
}

function portfolio_settings_display() { ?>
    <div class="wrap">
        <h2>Portfolio Settings</h2>
        <form method="post" action="options.php">
			<fieldset>
				<?php settings_fields('portfolio_settings'); ?>
				
				<p>
					<label for="portfolio_container_width">Container Width:</label> <br/>
					<input type="text" name="portfolio_container_width" class="small-text" value="<?php echo get_option('portfolio_container_width', '604'); ?>" /> 
					<span class="description">px (default = 604)</span>		
				</p>
				<p>
					<label for="portfolio_post_per_page_list">Post per page for Single Page List:</label> <br/>
					<input type="text" name="portfolio_post_per_page_list" class="small-text" value="<?php echo get_option('portfolio_post_per_page_list', '5'); ?>" />
					<span class="description">(default = 5)</span>
				</p>
				<p>
					<label for="portfolio_post_per_page_content">Post per page for Content:</label> <br/>
					<input type="text" name="portfolio_post_per_page_content" class="small-text" value="<?php echo get_option('portfolio_post_per_page_content', '3'); ?>" />
					<span class="description">(default = 3)</span>
				</p>
				<p>
					<label for="portfolio_thumb_size">Portfolio Thumbnail Size:</label> <br/>
					<input type="text" name="portfolio_thumb_size_w" class="small-text" value="<?php echo get_option('portfolio_thumb_size_w', '300'); ?>" />
					<span class="description">px Width(default = 300)</span>
					
					<input type="text" name="portfolio_thumb_size_h" class="small-text" value="<?php echo get_option('portfolio_thumb_size_h', '225'); ?>" />
					<span class="description">px Height(default = 225)</span>	
				</p>
				<p>
					<label for="portfolio_img_size">Portfolio Image Size:</label> <br/>
					<input type="text" name="portfolio_img_size_w" class="small-text" value="<?php echo get_option('portfolio_img_size_w', '600'); ?>" />
					<span class="description">px Width(default = 600)</span>
					
					<input type="text" name="portfolio_img_size_h" class="small-text" value="<?php echo get_option('portfolio_img_size_h', '400'); ?>" />
					<span class="description">px Height(default = 400)</span>	
				</p>
				<p>
					<label for="portfolio_order">Order by:</label> <br/>
					<input name="portfolio_order" type="radio" value="0" <?php checked( '0', get_option( 'portfolio_order' ) ); ?> />
					<span class="description">Date Accomplished</span><br/>
					<input name="portfolio_order" type="radio" value="1" <?php checked( '1', get_option( 'portfolio_order' ) ); ?> />
					<span class="description">Set Order</span>
				</p>
				
				<p class="submit"><input type="submit" class="button-primary" value="Save Changes" /></p>
			</fieldset>
	   </form>
    </div>
<?php }

add_image_size('portfolio-thumb', get_option('portfolio_thumb_size_w', '300'), get_option('portfolio_thumb_size_h', '225'), true);
add_image_size('portfolio-img', get_option('portfolio_img_size_w', '600'), get_option('portfolio_img_size_h', '400'), true);

/** Front-end **/

// Create Display Shortcode
add_shortcode( 'showcase_portfolio', 'showcase_portfolio_display' );
function showcase_portfolio_display($attr) {
	ob_start();
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

	//Post per Page
	$posts_per_page='';
	if ( $attr['template']=='content' ) {
		if( get_option( 'portfolio_post_per_page_content' ) ) {
			$posts_per_page = get_option( 'portfolio_post_per_page_content' );
		}
	}else if ( $attr['template']=='list' ){
		if( get_option( 'portfolio_post_per_page_list' ) ) {
			$posts_per_page = get_option( 'portfolio_post_per_page_list' );
		}
	}
	
	//Order Set
	if( get_option( 'portfolio_order' ) ) {
		if( get_option( 'portfolio_order' ) == 0 ) {
			$meta_key = 'date_accomplished';
			$order = 'DESC';
		}else if( get_option( 'portfolio_order' ) == 1 ) {
			$meta_key = 'set_order';
			$order = 'ASC';
		}
	}
	
	$terms = get_terms("shc-portfolio-category");
    $count = count($terms);
    echo '<ul id="portfolio-filter">';
    echo '<li><a href="#all" title="">All</a></li>';
        if ( $count > 0 )
        {   
            foreach ( $terms as $term ) {
                $termname = strtolower($term->name);
                $termname = str_replace(' ', '-', $termname);
                echo '<li><a href="#'.$termname.'" title="" rel="'.$termname.'">'.$term->name.'</a></li>';
            }
        }
    echo "</ul>";
	
	//WP_Query data
    $query = new WP_Query( array(
        'post_type' => 'shc-portfolio',
        'posts_per_page' => $posts_per_page,
		'paged' => $paged, 
		'meta_key'=>$meta_key,
		'orderby'=>'meta_value',
		'order' => $order
    ) );

	//Container width
	$style_width = '';
	if( get_option( 'portfolio_container_width' ) ) {
		$style_width = 'style="width:'.get_option('portfolio_container_width').'px;"';
	}
	
	//Display HTML
	if ( $query->have_posts() ) { ?> 
		<div class="portfolio-container" <?php echo $style_width; ?>>
			<ul id="portfolio-list" class="portfolio-post-list">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php
                        $terms = get_the_terms( $post->ID, 'shc-portfolio-category' );
                                 
                        if ( $terms && ! is_wp_error( $terms ) ) : 
                            $links = array();
 
                            foreach ( $terms as $term ) 
                            {
                                $links[] = $term->name;
                            }
                            $links = str_replace(' ', '-', $links); 
                            $tax = join( " ", $links );     
                        else :  
                            $tax = '';  
                        endif;
                ?>
                         
                <?php $infos = get_post_custom_values('_url'); ?>
				<li id="post-<?php the_ID(); ?>" class="portfolio-item <?php echo strtolower($tax); ?> all portfolio-post-item">
					<div class="portfolio-item-thumb">
						<?php if ( has_post_thumbnail()) { ?>
							<?php the_post_thumbnail('portfolio-thumb'); ?>
						<?php } else { ?>
							<div style="background:url(<?php echo plugins_url( '/showcase-portfolio/images/no-image.jpg' ) ?>);width:<?php echo get_option('portfolio_thumb_size_w', '300'); ?>px;height:<?php echo get_option('portfolio_thumb_size_h', '225'); ?>px" title="No Image"></div>
						<?php } ?>
					</div>
					<div class="portfolio-item-detail">
						<h3><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h3>
						<div class="portfolio-item-content">
							<?php the_excerpt(); ?>
						</div>
						
						<!-- Showcase Portfolio Meta -->
						<?php if(get_post_meta( get_the_ID(), 'client_name', true) ) { ?>
							<p class="portfolio-text"><strong>Client's Name:</strong><span class="portfolio-text-desc"><?php echo esc_html( get_post_meta( get_the_ID(), 'client_name', true ) ); ?></span></p>
						<?php } ?>
						
						<?php if(get_post_meta( get_the_ID(), 'my_role', true) ) { ?>
							<p class="portfolio-text"><strong>My Role:</strong><span class="portfolio-text-desc"><?php echo esc_html( get_post_meta( get_the_ID(), 'my_role', true ) ); ?><span></p>
						<?php } ?>
						
						<?php if(get_post_meta( get_the_ID(), 'portfolio_link', true) ) { ?>
							<p class="portfolio-text"><a href="<?php echo esc_html( get_post_meta( get_the_ID(), 'portfolio_link', true ) ); ?>" class="button">Link to Website</a></p>
						<?php } ?>
					
					</div>
				</li>
				<?php endwhile; ?>
				<?php
					if($attr['template']=='list') { 
						portfolio_pagination($query->max_num_pages,$paged);
					}
				?> 
				<?php wp_reset_postdata(); ?>
				<script>
					jQuery(document).ready(function() {	
						jQuery("#portfolio-list").filterable();
					});
				</script>
			</ul>
		</div>
    <?php $portfolio_output = ob_get_clean();
    return $portfolio_output;
    }
}

//Pagination
function portfolio_pagination($numpages = '', $paged='') {
	
$big = 12345678;
$page_format = paginate_links( array(
    'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
    'format' => '?paged=%#%',
    'current' => $paged,
    'total' => $numpages,
    'type'  => 'array'
) );
	

	 if( is_array($page_format) ) {


           echo '<div class="pagination"><ul>';
            echo '<li><span>'. $paged . ' of ' . $numpages .'</span></li>';
            foreach ( $page_format as $page ) {
                    echo "<li>$page</li>";
            }
           echo '</ul></div>';
	  }
}

//Single page template
add_filter( 'single_template', 'get_portfolio_template' );
function get_portfolio_template($single_template) {
	global $post;
    if ($post->post_type == 'shc-portfolio') {
          $single_template = dirname( __FILE__ ) . '/templates/single-showcase-portfolio.php';
    }
    return $single_template;
}

//Excerpt Length
function custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

/***
* Enqueue styles
***/
add_action( 'wp_enqueue_scripts', 'showcase_portfolio_styles' );

/**
 * Enqueue plugin style-file
 */
function showcase_portfolio_styles() {
    //Style.css is relative to the current file
    wp_register_style( 'portfolio-style', plugins_url('/css/style.css', __FILE__) );
    wp_enqueue_style( 'portfolio-style' );
}

add_action( 'wp_enqueue_scripts', 'showcase_portfolio_scripts' );
/**
 * Enqueue plugin script
 */
function showcase_portfolio_scripts() {
	wp_register_script( 'portfolio-script', plugins_url('/js/filterable.js' , __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'portfolio-script' );
}

?>