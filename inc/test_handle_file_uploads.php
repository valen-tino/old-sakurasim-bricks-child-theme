<?php

// Handle file uploads and save URLs in order meta
add_action( 'woocommerce_checkout_update_order_meta', 'save_custom_image_fields_in_order_meta' );
function save_custom_image_fields_in_order_meta( $order_id ) {
    // Check if the file fields are set
    if ( ! empty( $_FILES['friend_qr_code']['name'] ) || ! empty( $_FILES['front_residence_card']['name'] ) || ! empty( $_FILES['back_residence_card']['name'] ) || ! empty( $_FILES['address_image']['name'] ) ) {
        $upload_dir = wp_upload_dir();
        $file_types = array( 'image/jpeg', 'image/png', 'image/jpg' );
        $max_file_size = 4 * 1024 * 1024; // 4 MB in bytes

        // Process Friend's QR CODE
        if ( ! empty( $_FILES['friend_qr_code']['name'] ) ) {
            $file = $_FILES['friend_qr_code'];
            $field_name = 'friend_qr_code_field';
            $file_url = handle_uploaded_file( $file, $field_name, $upload_dir, $file_types, $max_file_size );
            if ( $file_url ) {
                update_post_meta( $order_id, $field_name, $file_url );
            }
        }

        // Process Front Residence Card
        if ( ! empty( $_FILES['front_residence_card']['name'] ) ) {
            $file = $_FILES['front_residence_card'];
            $field_name = 'front_residence_card_field';
            $file_url = handle_uploaded_file( $file, $field_name, $upload_dir, $file_types, $max_file_size );
            if ( $file_url ) {
                update_post_meta( $order_id, $field_name, $file_url );
            }
        }

        // Process Back Residence Card
        if ( ! empty( $_FILES['back_residence_card']['name'] ) ) {
            $file = $_FILES['back_residence_card'];
            $field_name = 'back_residence_card_field';
            $file_url = handle_uploaded_file( $file, $field_name, $upload_dir, $file_types, $max_file_size );
            if ( $file_url ) {
                update_post_meta( $order_id, $field_name, $file_url );
            }
        }

        // Process Address Image
        if ( ! empty( $_FILES['address_image']['name'] ) ) {
            $file = $_FILES['address_image'];
            $field_name = 'address_image_field';
            $file_url = handle_uploaded_file( $file, $field_name, $upload_dir, $file_types, $max_file_size );
            if ( $file_url ) {
                update_post_meta( $order_id, $field_name, $file_url );
            }
        }
    }
}

// Validate file types and sizes
function handle_uploaded_file( $file, $field_name, $upload_dir, $file_types, $max_file_size ) {
    $file_name = $file['name'];
    $file_type = $file['type'];
    $file_tmp_name = $file['tmp_name'];
    $file_size = $file['size'];

    // Check file type and size
    if ( ! in_array( $file_type, $file_types ) || $file_size > $max_file_size ) {
        wc_add_notice( 'Invalid file type or size for ' . $field_name . '.', 'error' );
        return false;
    }

    // Upload file
    $upload_overrides = array( 'test_form' => false );
    $file_url = '';

    $movefile = wp_handle_upload( $file, $upload_overrides );

    if ( $movefile && ! isset( $movefile['error'] ) ) {
        $file_url = $movefile['url'];
    } else {
        wc_add_notice( 'Error uploading file for ' . $field_name . '.', 'error' );
    }

    return $file_url;
}

// Add validation for the file upload fields in checkout
add_action( 'woocommerce_checkout_process', 'validate_custom_image_fields' );
function validate_custom_image_fields() {

    $file_types = array( 'image/jpeg', 'image/png', 'image/jpg' );
    $max_file_size = 4 * 1024 * 1024; // 4 MB in bytes

    if ( ! empty( $_FILES['friend_qr_code']['name'] ) ) {
        $file = $_FILES['friend_qr_code'];
        $field_name = 'friend_qr_code_field';
        handle_uploaded_file( $file, $field_name, wp_upload_dir(), $file_types, $max_file_size );
    }

    if ( ! empty( $_FILES['front_residence_card']['name'] ) ) {
        $file = $_FILES['front_residence_card'];
        $field_name = 'front_residence_card_field';
        handle_uploaded_file( $file, $field_name, wp_upload_dir(), $file_types, $max_file_size );
    }

    if ( ! empty( $_FILES['back_residence_card']['name'] ) ) {
        $file = $_FILES['back_residence_card'];
        $field_name = 'back_residence_card_field';
        handle_uploaded_file( $file, $field_name, wp_upload_dir(), $file_types, $max_file_size );
    }

    if ( ! empty( $_FILES['address_image']['name'] ) ) {
        $file = $_FILES['address_image'];
        $field_name = 'address_image_field';
        handle_uploaded_file( $file, $field_name, wp_upload_dir(), $file_types, $max_file_size );
    }
}
