<?php
add_filter('woocommerce_billing_fields', 'customize_billing_fields', 100);
add_filter('woocommerce_shipping_fields', 'customize_shipping_fields', 100);

function customize_billing_fields($fields) {
    $remove_fields = array('billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state');

    foreach ($remove_fields as $field) {
        $fields[$field]['required'] = false; // Make them unrequired
        unset($fields[$field]); // Remove the field
        
    }

    return $fields;
}

function customize_shipping_fields($fields) {
    $remove_fields = array('shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state');

    foreach ($remove_fields as $field) {
        $fields[$field]['required'] = false; // Make them unrequired
        unset($fields[$field]); // Remove the field
        
    }

    return $fields;
}