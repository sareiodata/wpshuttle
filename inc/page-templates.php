<?php

/*
 * Returns an array with the information for each template
 *
 */
function wpshuttle_get_page_templates() {

    $page_templates = array(
        array(
            'name'       => 'Full Width',
            'admin_icon' => 'icon-template-full-width.png',
            'body_class' => 'full-width'
        ),
        array(
            'name'       => 'Boxed',
            'admin_icon' => 'icon-template-boxed.png',
            'body_class' => 'boxed'
        ),
        array(
            'name'       => 'Boxed with Left Sidebar',
            'admin_icon' => 'icon-template-boxed-left-sidebar.png',
            'body_class' => 'boxed-sidebar-left'
        ),
        array(
            'name'       => 'Boxed with Right Sidebar',
            'admin_icon' => 'icon-template-boxed-right-sidebar.png',
            'body_class' => 'boxed-sidebar-right'
        )
    );

    return $page_templates;

}


/*
 * Add templates classes to the body
 *
 */
function wpshuttle_page_template_body_class( $classes ) {

    $post_page_template  = wpshuttle_get_post_page_template();
    $page_templates      = wpshuttle_get_page_templates();

    if( isset($page_templates[$post_page_template]) )
        array_push( $classes, $page_templates[$post_page_template]['body_class'] );

    return $classes;
}
add_filter( 'body_class', 'wpshuttle_page_template_body_class' );


/*
 * Return the saved page template for the current post
 *
 */
function wpshuttle_get_post_page_template() {

    global $post;

    if( !$post )
        return -1;

    // Get page template
    $page_template = get_post_meta( $post->ID, 'wpshuttle_page_template', true );

    return ( isset( $page_template ) ? $page_template : 3 );

}


/*
 * Output for the template meta box in the admin area
 *
 */
function wpshuttle_page_templates_meta_box_output( $post ) {

    // Get page templates
    $page_templates = wpshuttle_get_page_templates();

    // Get saved page template
    $saved_page_template = get_post_meta( $post->ID, 'wpshuttle_page_template', true );

    // Display available templates
    if( !empty( $page_templates ) ) {
        foreach( $page_templates as $key => $page_template ) {
            echo '<div class="wpshuttle-page-template">';
                echo '<input id="wpshuttle-page-template-' . $key . '" type="radio" name="wpshuttle_page_template" value="' . $key . '" ' . checked( $saved_page_template, $key, false ) . '/>';
                echo '<label for="wpshuttle-page-template-' . $key . '">';

                    if( isset( $page_template['admin_icon'] ) )
                        echo '<img src="' . get_template_directory_uri() . '/img/' . $page_template['admin_icon'] . '" />';

                echo '</label>';

                echo '<span>' . $page_template['name'] . '</span>';
            echo '</div>';
        }
    }

}


/*
 * Save value for the page template meta box
 *
 */
function wpshuttle_page_templates_meta_box_save( $post_id ) {

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
    if ( ! isset( $_POST['wpshuttle_page_template'] ) )
        return;

    // Sanitize user input.
    $page_template = sanitize_text_field( $_POST['wpshuttle_page_template'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'wpshuttle_page_template', $page_template );

}
add_action( 'save_post', 'wpshuttle_page_templates_meta_box_save' );


/*
 * Initialises the templates meta box
 *
 */
function wpshuttle_page_templates_meta_box_init() {

    add_meta_box( 'wpshuttle_page_templates', 'Page Layout', 'wpshuttle_page_templates_meta_box_output', array_merge( array( 'post', 'page' ), get_post_types( array( '_builtin' => false ) ) ) );

}
add_action( 'add_meta_boxes', 'wpshuttle_page_templates_meta_box_init' );