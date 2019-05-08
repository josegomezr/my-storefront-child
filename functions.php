<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
 
}

add_filter('woocommerce_default_address_fields', 'my_address_fields');

function my_address_fields($fields) {
  return $fields;
}

add_filter('woocommerce_billing_fields', 'my_billing');

$DEFAULT_FIELDS_WOOCOMMERCE = array(
    // 'first_name' => __('', 'woocommerce'),
    // 'last_name'  => __('', 'woocommerce'),
    // 'company'    => __('', 'woocommerce'),
    'country'       => __('Country', 'woocommerce'),
    'address_1'     => __( 'Street address', 'woocommerce' ),
    'address_2'     => __( 'Address line 2', 'woocommerce' ),
    'city'          => __( 'Town / City', 'woocommerce' ),
    'state'         => __( 'State / County', 'woocommerce' ),
    'postcode'      => __( 'Postcode / ZIP', 'woocommerce' ),
  );


function my_billing($fields) {
  global $DEFAULT_FIELDS_WOOCOMMERCE;
  foreach ( $DEFAULT_FIELDS_WOOCOMMERCE as $field => $label ) {
    if ( 'hidden' === get_option( 'woocommerce_checkout_'. $field .'_field', 'required' ) ) {  
      unset($fields['billing_'.$field]);
    }
  }
  return $fields;
}

function cwoo_sanitize_checkout_field_display( $value ) {
  $options = array( 'hidden', 'optional', 'required' );
  return in_array( $value, $options, true ) ? $value : '';
}


add_action( 'customize_register', 'my_customize_register' );
function my_customize_register($wp_customize) {
  global $DEFAULT_FIELDS_WOOCOMMERCE;
  foreach ( $DEFAULT_FIELDS_WOOCOMMERCE as $field => $label ) {
    $wp_customize->add_setting(
      'woocommerce_checkout_' . $field . '_field',
      array(
        'default'           => 'phone' === $field ? 'required' : 'optional',
        'type'              => 'option',
        'capability'        => 'manage_woocommerce',
        'sanitize_callback' => 'cwoo_sanitize_checkout_field_display',
      )
    );
    $wp_customize->add_control(
      'woocommerce_checkout_' . $field . '_field',
      array(
        /* Translators: %s field name. */
        'priority' => 1,
        'label'    => sprintf( __( '%s field', 'woocommerce' ), $label ),
        'section'  => 'woocommerce_checkout',
        'settings' => 'woocommerce_checkout_' . $field . '_field',
        'type'     => 'select',
        'choices'  => array(
          'hidden'   => __( 'Hidden', 'woocommerce' ),
          'optional' => __( 'Optional', 'woocommerce' ),
          'required' => __( 'Required', 'woocommerce' ),
        ),
      )
    );
  }
}


// remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

// add_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
add_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 10 );