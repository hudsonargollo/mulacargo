<?php

return array (
  'name' => 'Midtrans',
  'slug' => 'midtrans',
  'image' => 'modules/midtrans/images/logo.png',
  'title' => 'Midtrans',
  'processing_fee' => '1',
  'subscription' => 0,
  'configs' => 
  array (
    'merchant_id' => 'G334748764',
    'client_key' => '',
    'server_key' => '',
    'midtrans_sandbox_mode' => '1',
  ),
  'fields' => 
  array (
    'title' => 
    array (
      'type' => 'text',
      'label' => 'Label',
    ),
    'processing_fee' => 
    array (
      'type' => 'number',
      'label' => 'Processing Fee',
    ),
    'midtrans_sandbox_mode' => 
    array (
      'type' => 'select',
      'label' => 'Select Mode',
      'options' => 
      array (
        1 => 'Sandbox',
        0 => 'Live',
      ),
    ),
    'merchant_id' => 
    array (
      'type' => 'password',
      'label' => 'Merchant ID',
    ),
    'client_key' => 
    array (
      'type' => 'password',
      'label' => 'Client Key',
    ),
    'server_key' => 
    array (
      'type' => 'password',
      'label' => 'Server Key',
    ),
    'subscription' => 
    array (
      'type' => 'checkbox',
      'label' => 'Subscription',
    ),
  ),
);
