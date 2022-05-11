<?php

/**
 * @file
 * Provide views data for tests.
 */

/**
 * Implements hook_views_data().
 */
function tmg_utility_views_data(): array {
  $data = [];

  $data['views_remote_data_tmw_api_partner']['table']['group'] = 'views_remote_data_tmw_api_partner';
  $data['views_remote_data_tmw_api_partner']['table']['base'] = [
    'title' => 'views_remote_data_tmw_api_partner',
    'query_id' => 'views_remote_data_query',
  ];
  return $data;
}
