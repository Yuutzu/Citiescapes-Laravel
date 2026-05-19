<div class="theme-offer">
   <?php
        // Check if the demo import has been completed
        $apartment_rental_services_demo_import_completed = get_option('apartment_rental_services_demo_import_completed', false);

        // If the demo import is completed, display the "View Site" button
        if ($apartment_rental_services_demo_import_completed) {
            echo '<br>';
            echo '<div class="success">Demo Import Successful</div>';
            echo '<br>';
            echo '<hr>';
            echo '<br>';
            echo '<span>' . esc_html__( 'You can now visit your site or customize it further.', 'apartment-rental-services' ) . '</span>';
            echo '<br>';
            echo '<br>';
            echo '<br>';
            echo '<div class="view-site-btn">';
            echo '<a href="' . esc_url(home_url()) . '" class="button button-primary button-large" style="margin-top: 10px;" target="_blank">View Site</a>';
            echo '<a href="' . esc_url( admin_url('customize.php') ) . '" class="button button-primary button-large" style="margin-top: 10px;" target="_blank">Customize Demo Content</a>';
            echo '</div>';
        }
    if ( isset( $_POST['submit'] ) ) {
        echo '<div class="plugin-notice">';
            // Check if Classic Blog Grid plugin is installed
            if (!is_plugin_active('classic-blog-grid/classic-blog-grid.php')) {
                // Plugin slug and file path for Classic Blog Grid
                $apartment_rental_services_plugin_slug = 'classic-blog-grid';
                $apartment_rental_services_plugin_file = 'classic-blog-grid/classic-blog-grid.php';
            
                // Check if Classic Blog Grid is installed and activated
                if ( ! is_plugin_active( $apartment_rental_services_plugin_file ) ) {
            
                    // Check if Classic Blog Grid is installed
                    $apartment_rental_services_installed_plugins = get_plugins();
                    if ( ! isset( $apartment_rental_services_installed_plugins[ $apartment_rental_services_plugin_file ] ) ) {
            
                        // Include necessary files to install plugins
                        include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
                        include_once( ABSPATH . 'wp-admin/includes/file.php' );
                        include_once( ABSPATH . 'wp-admin/includes/misc.php' );
                        include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
            
                        // Download and install Classic Blog Grid
                        $apartment_rental_services_upgrader = new Plugin_Upgrader();
                        $apartment_rental_services_upgrader->install( 'https://downloads.wordpress.org/plugin/classic-blog-grid.latest-stable.zip' );
                    }
            
                    // Activate the Classic Blog Grid plugin after installation (if needed)
                    activate_plugin( $apartment_rental_services_plugin_file );
                }
            }
        echo '</div>';
        // ------- Create Main Menu --------
        $apartment_rental_services_menuname = 'Primary Menu';
        $apartment_rental_services_bpmenulocation = 'primary';
        $apartment_rental_services_menu_exists = wp_get_nav_menu_object( $apartment_rental_services_menuname );
    
        if ( !$apartment_rental_services_menu_exists ) {
            $apartment_rental_services_menu_id = wp_create_nav_menu( $apartment_rental_services_menuname );

            // Create Home Page
            $apartment_rental_services_home_title = 'Home';
            $apartment_rental_services_home = array(
                'post_type'    => 'page',
                'post_title'   => $apartment_rental_services_home_title,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_slug'    => 'home'
            );
            $apartment_rental_services_home_id = wp_insert_post($apartment_rental_services_home);
            // Assign Home Page Template
            add_post_meta($apartment_rental_services_home_id, '_wp_page_template', 'templates/template-home-page.php');
            // Update options to set Home Page as the front page
            update_option('page_on_front', $apartment_rental_services_home_id);
            update_option('show_on_front', 'page');
            // Add Home Page to Menu
            wp_update_nav_menu_item($apartment_rental_services_menu_id, 0, array(
                'menu-item-title' => __('Home', 'apartment-rental-services'),
                'menu-item-classes' => 'home',
                'menu-item-url' => home_url('/'),
                'menu-item-status' => 'publish',
                'menu-item-object-id' => $apartment_rental_services_home_id,
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type'
            ));

            // Create a new Page 
            $apartment_rental_services_pages_title = 'Project';
            $apartment_rental_services_pages_content = '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960 with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>';
            $apartment_rental_services_pages = array(
                'post_type'    => 'page',
                'post_title'   => $apartment_rental_services_pages_title,
                'post_content' => $apartment_rental_services_pages_content,
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_slug'    => 'project'
            );
            $apartment_rental_services_pages_id = wp_insert_post($apartment_rental_services_pages);
            // Add Pages Page to Menu
            wp_update_nav_menu_item($apartment_rental_services_menu_id, 0, array(
                'menu-item-title' => __('Project', 'apartment-rental-services'),
                'menu-item-classes' => 'project',
                'menu-item-url' => home_url('/project/'),
                'menu-item-status' => 'publish',
                'menu-item-object-id' => $apartment_rental_services_pages_id,
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type'
            ));

            // Create a new Page 
            $apartment_rental_services_pages_title = 'Pages';
            $apartment_rental_services_pages_content = '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960 with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>';
            $apartment_rental_services_pages = array(
                'post_type'    => 'page',
                'post_title'   => $apartment_rental_services_pages_title,
                'post_content' => $apartment_rental_services_pages_content,
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_slug'    => 'pages'
            );
            $apartment_rental_services_pages_id = wp_insert_post($apartment_rental_services_pages);
            // Add Pages Page to Menu
            wp_update_nav_menu_item($apartment_rental_services_menu_id, 0, array(
                'menu-item-title' => __('Pages', 'apartment-rental-services'),
                'menu-item-classes' => 'pages',
                'menu-item-url' => home_url('/pages/'),
                'menu-item-status' => 'publish',
                'menu-item-object-id' => $apartment_rental_services_pages_id,
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type'
            ));

            // Create Blogs Page with Dummy Content
            $apartment_rental_services_about_title = 'Blog';
            $apartment_rental_services_about_content = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500...';

            $apartment_rental_services_about = array(
                'post_type'    => 'page',
                'post_title'   => $apartment_rental_services_about_title,
                'post_content' => $apartment_rental_services_about_content,
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_name'    => 'blog' 
            );

            $apartment_rental_services_about_id = wp_insert_post($apartment_rental_services_about);

            // Add Blogs Page to Menu
            wp_update_nav_menu_item( $apartment_rental_services_menu_id, 0, array(
                'menu-item-title'     => __( 'Blog', 'apartment-rental-services' ),
                'menu-item-classes'  => 'blog',
                'menu-item-object-id'=> $apartment_rental_services_about_id,
                'menu-item-object'   => 'page',
                'menu-item-type'     => 'post_type',
                'menu-item-status'   => 'publish'
            ));

            update_option( 'page_for_posts', $apartment_rental_services_about_id );

            // Create About Us Page with Dummy Content
            $apartment_rental_services_about_title = 'Contact Us';
            $apartment_rental_services_about_content = '<P>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960 with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</P>';
            $apartment_rental_services_about = array(
                'post_type'    => 'page',
                'post_title'   => $apartment_rental_services_about_title,
                'post_content' => $apartment_rental_services_about_content,
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_slug'    => 'contact-us'
            );
            $apartment_rental_services_about_id = wp_insert_post($apartment_rental_services_about);
            // Add About Us Page to Menu
            wp_update_nav_menu_item($apartment_rental_services_menu_id, 0, array(
                'menu-item-title' => __('Contact Us', 'apartment-rental-services'),
                'menu-item-classes' => 'contact-us',
                'menu-item-url' => home_url('/contact-us/'),
                'menu-item-status' => 'publish',
                'menu-item-object-id' => $apartment_rental_services_about_id,
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type'
            ));

            // Assign the menu to the primary location if not already set
            if ( ! has_nav_menu( $apartment_rental_services_bpmenulocation ) ) {
                $apartment_rental_services_locations = get_theme_mod( 'nav_menu_locations' );
                if ( empty( $apartment_rental_services_locations ) ) {
                    $apartment_rental_services_locations = array();
                }
                $apartment_rental_services_locations[ $apartment_rental_services_bpmenulocation ] = $apartment_rental_services_menu_id;
                set_theme_mod( 'nav_menu_locations', $apartment_rental_services_locations );
            }
        }

        //Header Section
        set_theme_mod( 'apartment_rental_services_the_custom_logo', esc_url( get_template_directory_uri().'/images/Logo.png'));
        set_theme_mod( 'apartment_rental_services_header_btn_text', 'Ask A Question' );
        set_theme_mod( 'apartment_rental_services_header_btn_url', '#' );
        
        // Slider settings
        set_theme_mod('apartment_rental_services_button_text', 'EXPLORE APARTMENT');
        set_theme_mod('apartment_rental_services_button_link_slider', '#');
        set_theme_mod('apartment_rental_services_button_text2', 'TAKE A LOOK AT VR');
        set_theme_mod('apartment_rental_services_button_link_slider2', '#');

        // Helper function to import an image from a URL
        function apartment_rental_services_import_image($apartment_rental_services_image_url, $apartment_rental_services_post_id) {
            $apartment_rental_services_image_name = basename($apartment_rental_services_image_url);
            $apartment_rental_services_upload_dir = wp_upload_dir();

            // Download image data using file_get_contents
            $apartment_rental_services_image_data = @file_get_contents($apartment_rental_services_image_url);
            if ($apartment_rental_services_image_data === false) {
                return new WP_Error('image_download_error', 'Failed to download image: ' . $apartment_rental_services_image_url);
            }

            // Use wp_upload_bits to handle the file saving
            $apartment_rental_services_result = wp_upload_bits($apartment_rental_services_image_name, null, $apartment_rental_services_image_data);
            if ($apartment_rental_services_result['error']) {
                return new WP_Error('image_write_error', 'Failed to write image to uploads: ' . $apartment_rental_services_result['error']);
            }

            // Insert the image into the media library
            $apartment_rental_services_attachment = array(
                'post_mime_type' => wp_check_filetype($apartment_rental_services_image_name, null)['type'],
                'post_title'     => sanitize_file_name($apartment_rental_services_image_name),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $apartment_rental_services_attach_id = wp_insert_attachment($apartment_rental_services_attachment, $apartment_rental_services_result['file'], $apartment_rental_services_post_id);
            if (is_wp_error($apartment_rental_services_attach_id)) {
                return new WP_Error('attachment_error', 'Failed to create attachment: ' . $apartment_rental_services_attach_id->get_error_message());
            }

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $apartment_rental_services_attach_data = wp_generate_attachment_metadata($apartment_rental_services_attach_id, $apartment_rental_services_result['file']);
            wp_update_attachment_metadata($apartment_rental_services_attach_id, $apartment_rental_services_attach_data);

            return $apartment_rental_services_attach_id;
        }

        // Create a category for the slider
        $apartment_rental_services_category_id = wp_create_category('Apartment');
        if (is_wp_error($apartment_rental_services_category_id)) {
            error_log('Error creating category: ' . $apartment_rental_services_category_id->get_error_message());
            return;
        }

        // Fetch and set the category image
        $category_image_url = get_template_directory_uri() . '/images/slider.png'; 
        $category_image_id = apartment_rental_services_import_image($category_image_url, 0);
        if (is_wp_error($category_image_id)) {
            error_log('Error importing category image: ' . $category_image_id->get_error_message());
        } else {
            set_theme_mod('apartment_rental_services_category_image', wp_get_attachment_url($category_image_id));
        }

        $apartment_rental_services_post_titles = array(
            'Where Convenience Meets Opulence',
            'Perfect Blend of Comfort and Elegance',
            'Unique Fusion of Convenience and Luxury'
        );
        $apartment_rental_services_images = [
            get_template_directory_uri() . '/images/slider-img/slider1.png',
            get_template_directory_uri() . '/images/slider-img/slider2.png',
            get_template_directory_uri() . '/images/slider-img/slider3.png',
        ];
        
        for ($apartment_rental_services_i = 0; $apartment_rental_services_i < 3; $apartment_rental_services_i++) {
            $apartment_rental_services_post_title = $apartment_rental_services_post_titles[$apartment_rental_services_i]; // Correct index
            $apartment_rental_services_post_id = wp_insert_post(array(
                'post_title'   => $apartment_rental_services_post_title,
                'post_content' => 'This is the demo content for the slider.',
                'post_status'  => 'publish',
                'post_category' => array($apartment_rental_services_category_id),
            ));
        
            if (is_wp_error($apartment_rental_services_post_id)) {
                error_log('Error creating post: ' . $apartment_rental_services_post_id->get_error_message());
                continue;
            }
        
            // Attach images to the post
            foreach ($apartment_rental_services_images as $apartment_rental_services_j => $apartment_rental_services_image_url) {
                $apartment_rental_services_image_id = apartment_rental_services_import_image($apartment_rental_services_image_url, $apartment_rental_services_post_id);
                if (is_wp_error($apartment_rental_services_image_id)) {
                    error_log('Error importing image: ' . $apartment_rental_services_image_id->get_error_message());
                    continue;
                }
                // Set each image in customizer settings
                set_theme_mod('apartment_rental_services_slider_img' . $apartment_rental_services_post_id . '_' . ($apartment_rental_services_j + 1), wp_get_attachment_url($apartment_rental_services_image_id));
            }
        }        

        set_theme_mod('apartment_rental_services_slider_cat', get_category($apartment_rental_services_category_id)->slug);  

        //Service Section
        set_theme_mod( 'apartment_rental_services_service_title', 'Apartment Services' );
        set_theme_mod( 'apartment_rental_services_service_text', 'simply dummy text of the printing and typesetting industry' );

        $apartment_rental_services_featured_category_id = wp_create_category('Services');
        set_theme_mod('apartment_rental_services_posts', 'Services');

        $apartment_rental_services_titles = array('Innovative Spaces', 'Crafted Rooms', '24/7 Reception');
        $apartment_rental_services_content = 'Veniam sed quis nostrud ulamc';

        for ($apartment_rental_services_i = 0; $apartment_rental_services_i < 3; $apartment_rental_services_i++) {
            set_theme_mod('apartment_rental_services_title' . ($apartment_rental_services_i + 1), $apartment_rental_services_titles[$apartment_rental_services_i]);

            $apartment_rental_services_my_post = array(
                'post_title'    => wp_strip_all_tags($apartment_rental_services_titles[$apartment_rental_services_i]),
                'post_content'  => $apartment_rental_services_content,
                'post_status'   => 'publish',
                'post_type'     => 'post',
                'post_category' => array($apartment_rental_services_featured_category_id),
            );

            $apartment_rental_services_post_id = wp_insert_post($apartment_rental_services_my_post);

            if (!is_wp_error($apartment_rental_services_post_id)) {
                $apartment_rental_services_image_url = get_template_directory_uri() . '/images/services/service' . ($apartment_rental_services_i + 1) . '.png';
                $apartment_rental_services_image_id = media_sideload_image($apartment_rental_services_image_url, $apartment_rental_services_post_id, null, 'id');
                if (!is_wp_error($apartment_rental_services_image_id)) {
                    set_post_thumbnail($apartment_rental_services_post_id, $apartment_rental_services_image_id);
                } else {
                    error_log('Failed to set post thumbnail for post ID: ' . $apartment_rental_services_post_id);
                }
            } else {
                error_log('Failed to create post: ' . print_r($apartment_rental_services_post_id, true));
            }
        }

        // Show success message and the "View Site" button
        update_option('apartment_rental_services_demo_import_completed', true);
        echo '<br>';
        echo '<div class="success">Demo Import Successful</div>';
        echo '<br>';
        echo '<hr>';
        echo '<br>';
        echo '<span>' . esc_html__( 'You can now visit your site or customize it further.', 'apartment-rental-services' ) . '</span>';
        echo '<br>';
    }
     ?>
    <ul>
        <li>
        <?php 
        // Check if the form is submitted
        if ( !isset( $_POST['submit'] ) ) : ?>
            <!-- Show demo importer form only if it's not submitted -->
            <?php if (!get_option('apartment_rental_services_demo_import_completed')) : ?>
                <span><?php echo esc_html( 'Click on the below content to get demo content installed.', 'apartment-rental-services' ); ?></span>
                <br><br>
                <hr><br>
                <b class="note"><?php echo esc_html('Note :', 'apartment-rental-services' ); ?></b><br><br>
                <small><b><?php echo esc_html('Please take a backup if your website is already live with data. This importer will overwrite existing data.', 'apartment-rental-services' ); ?></b></small><br><br>
                <form id="demo-importer-form" action="" method="POST" onsubmit="return runDemoImport();">
                    <input type="submit" name="submit" value="<?php echo esc_attr('Run Importer','apartment-rental-services'); ?>" class="button button-primary button-large">
                </form>
                <script type="text/javascript">
                    function runDemoImport() {
                        if (confirm('Do you really want to do this?')) {
                            document.getElementById('demo-import-loader').style.display = 'block';
                            return true;
                        }
                        return false;
                    }
                </script>
             <?php endif; ?>
         <?php 
        endif; 

        // Show "View Site" button after form submission
        if ( isset( $_POST['submit'] ) ) {
        echo '<div class="view-site-btn">';
        echo '<a href="' . esc_url(home_url()) . '" class="button button-primary button-large" style="margin-top: 10px;" target="_blank">View Site</a>';
        echo '<a href="' . esc_url( admin_url('customize.php') ) . '" class="button button-primary button-large" style="margin-top: 10px;" target="_blank">Customize Demo Content</a>';
        echo '</div>';
        }
        ?>
        </li>
    </ul>
 </div>