<?php

return array (
  'name' => 'Instamojo',
  'slug' => 'instamojo',
  'image' => 'modules/instamojo/images/logo.png',
  'title' => 'Instamojo',
  'processing_fee' => '1.5',
  'subscription' => 0,
  'configs' => 
  array (
    'instamojo_client_id' => '',
    'instamojo_client_secret' => '',
    'instamojo_salt_key' => 'e69dec98ca6941d988b18f3cd8872f3d',
    'instamojo_sandbox_mode' => '1',
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
    'instamojo_sandbox_mode' => 
    array (
      'type' => 'select',
      'label' => 'Select Mode',
      'options' => 
      array (
        1 => 'Sandbox',
        0 => 'Live',
      ),
    ),
    'instamojo_client_id' => 
    array (
      'type' => 'password',
      'label' => 'Client ID',
    ),
    'instamojo_client_secret' => 
    array (
      'type' => 'password',
      'label' => 'Client Secret',
    ),
    'instamojo_salt_key' => 
    array (
      'type' => 'password',
      'label' => 'Salt Key',
    ),
    'subscription' => 
    array (
      'type' => 'checkbox',
      'label' => 'Subscription',
    ),
  ),
);
