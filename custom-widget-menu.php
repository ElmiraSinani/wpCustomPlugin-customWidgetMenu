<?php
/**
 * Plugin Name: Custom widget menu
 * Description: The (Custom widget menu) will work on any custom made template...
 * Version: 0.1
 */
//Mete Boxes
/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function page_menu_add_meta_box() {
    add_meta_box(
		'page_menu_sectionid', //id
		__('Custom menu', 'cmp'), //title
		'page_menu_meta_box_callback', //collback
		'page', // screen
		'side'	);
}
add_action('add_meta_boxes', 'page_menu_add_meta_box');
/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function page_menu_meta_box_callback($post) {  
	$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
    // Add an nonce field so we can check for it later.
    wp_nonce_field('cmp_meta_box', 'cmp_meta_box_nonce');
    $pageCustomMenu = get_post_meta($post->ID, '_page_custom_menu_val', true); 
    //$menus = get_registered_nav_menus();
    echo "<select id='menu_field' name='menu_field'>";
    echo "<option value=''>Choose Menu For This Page</option>";
    foreach ($menus as $key => $menu) {
        echo "<option ". checkIsSelected( $pageCustomMenu, $menu->slug ) ." value='" . $menu->slug . "'>" . $menu->name . "</option>";
    }
    echo "</select>";
}
//Checking Selectbox option selected
function checkIsSelected($field, $val){
    if( $field == $val ){
        $sel = 'selected="selected"';
    }else{
         $sel = "";
    }    return $sel;  
}
/**
 * When the post is saved, saves our custom data.
 * * @param int $post_id The ID of the post being saved.
 */
function cmp_save_meta_box_data($post_id) {
    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.     */
    // Check if our nonce is set.
    if (!isset($_POST['cmp_meta_box_nonce'])) {
        return;
    }
    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['cmp_meta_box_nonce'], 'cmp_meta_box')) {
        return;
    }
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }	}
    /* OK, it's safe for us to save the data now. */
    // Make sure that it is set.
    if (!isset($_POST['menu_field'])) {
        return;
    }    // Sanitize user input.
    $selectMenu_data = isset($_POST['menu_field']) ? sanitize_text_field($_POST['menu_field']) : "";
    // Add Or Update the meta field in the database.
    if ( !update_post_meta($post_id, '_page_custom_menu_val', $selectMenu_data) ){
        add_post_meta($post_id, '_page_custom_menu_val', $selectMenu_data);
    }
}
add_action('save_post', 'cmp_save_meta_box_data');
function cmp_sidebar_init() {
    register_sidebar(
         array(
            'name' => 'Custom Menu Sidebar',
            'id' => 'cmp-page-sidebar',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h4>',
            'after_title' => '</h4>',
        )
    );
}
add_action( 'init', 'cmp_sidebar_init' );

function pageIdBySlug($page_slug) {
	$page = get_page_by_path($page_slug);
	if ($page) {
		return $page->ID;
	} else {
		return null;
	}}
class customMenuPage extends WP_Widget {
	// constructor
	function customMenuPage() {
		parent::WP_Widget(false, $name = __('Custom Menu Widget', 'wp_widget_plugin') );
	}
	// widget form creation
	function form($instance) {
		/* ... */
	}
	// widget update
	function update($new_instance, $old_instance) {
		/* ... */
	}
	// widget display
	function widget($args, $instance) {
		$postType = get_post_type( get_the_ID());
		if ( $postType == 'product' ){
			$id = pageIdBySlug('shop');
		}
		elseif( $postType == 'post' ){
			$id = get_option( 'page_for_posts' );
		}
		else{
			$id = get_the_ID();
		}
		// $menu = get_post_meta( get_the_ID(), '_page_custom_menu_val', true ); 
	   $menu = get_post_meta( $id, '_page_custom_menu_val', true ); 
	   ?>
		<div id="navbar-bottom">
            <ul id="nav-bottom">
				<?php 					if ( $menu == '' ) {
						wp_nav_menu( array('theme_location' => 'primary'));
					} else {
						wp_nav_menu( array('menu' => $menu )); 
					}
				?>				
			 </ul>
		</div>
	  <?php 
	}}
// register widget
add_action('widgets_init', create_function('', 'return register_widget("customMenuPage");'));