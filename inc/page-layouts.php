<?php

/*
 * Returns an array with the information for each layout
 *
 */
function wpshuttle_get_page_layouts() {

    $page_layouts = array(
        array(
            'name'       => 'Full Width',
            'admin_icon' => 'icon-layout-full-width.png',
            'body_class' => 'full-width'
        ),
        array(
            'name'       => 'Boxed',
            'admin_icon' => 'icon-layout-boxed.png',
            'body_class' => 'boxed'
        ),
        array(
            'name'       => 'Boxed with Left Sidebar',
            'admin_icon' => 'icon-layout-boxed-left-sidebar.png',
            'body_class' => 'boxed-sidebar-left'
        ),
        array(
            'name'       => 'Boxed with Right Sidebar',
            'admin_icon' => 'icon-layout-boxed-right-sidebar.png',
            'body_class' => 'boxed-sidebar-right'
        )
    );

    return $page_layouts;

}


/**
 * Add layouts classes to the body
 *
 */
function wpshuttle_page_layout_body_class( $classes ) {

    $post_page_layout  = wpshuttle_get_post_page_layout();
    $page_layouts      = wpshuttle_get_page_layouts();

    if( isset($page_layouts[$post_page_layout]) )
        array_push( $classes, $page_layouts[$post_page_layout]['body_class'] );

    return $classes;
}
add_filter( 'body_class', 'wpshuttle_page_layout_body_class' );


/**
 * Return the saved page layout for the current post
 *
 */
function wpshuttle_get_post_page_layout() {

    global $post;

    if( !$post )
        return -1;

    // Get page layout
    $page_layout = get_post_meta( $post->ID, 'wpshuttle_page_layout', true );

    return ( isset( $page_layout ) && $page_layout != '' ? $page_layout : 2 );

}


/*
 * Output for the layout meta box in the admin area
 *
 */
function wpshuttle_page_layouts_meta_box_output( $post ) {

    // Get page layouts
    $page_layouts = wpshuttle_get_page_layouts();

    // Get saved page layout
    $saved_page_layout = get_post_meta( $post->ID, 'wpshuttle_page_layout', true );

    // Display available layouts
    if( !empty( $page_layouts ) ) {
        foreach( $page_layouts as $key => $page_layout ) {
            echo '<div class="wpshuttle-page-layout">';
                echo '<input id="wpshuttle-page-layout-' . $key . '" type="radio" name="wpshuttle_page_layout" value="' . $key . '" ' . checked( $saved_page_layout, $key, false ) . '/>';
                echo '<label for="wpshuttle-page-layout-' . $key . '">';

                    if( isset( $page_layout['admin_icon'] ) )
                        echo '<img src="' . get_template_directory_uri() . '/img/' . $page_layout['admin_icon'] . '" />';

                echo '</label>';

                echo '<span>' . $page_layout['name'] . '</span>';
            echo '</div>';
        }
    }

}


/*
 * Save value for the page layout meta box
 *
 */
function wpshuttle_page_layouts_meta_box_save( $post_id ) {

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Make sure that it is set.
    if ( ! isset( $_POST['wpshuttle_page_layout'] ) )
        return;

    // Sanitize user input.
    $page_layout = sanitize_text_field( $_POST['wpshuttle_page_layout'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'wpshuttle_page_layout', $page_layout );

}
add_action( 'save_post', 'wpshuttle_page_layouts_meta_box_save' );


/*
 * Initialises the layouts meta box
 *
 */
function wpshuttle_page_layouts_meta_box_init() {

    add_meta_box( 'wpshuttle_page_layouts', 'Page Layout', 'wpshuttle_page_layouts_meta_box_output', array_merge( array( 'post', 'page' ), get_post_types( array( '_builtin' => false ) ) ), 'side' );

}
add_action( 'add_meta_boxes', 'wpshuttle_page_layouts_meta_box_init' );