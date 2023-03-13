namespace Drupal\smithsonian_open_access;
<?php
class SmithsonianOpenAccess {

public static function search($search) {
$config = \Drupal::config('smithsonian_open_access.settings');
$base_url = $config->get('base_url');
$api_key = $config->get('api_key');
$url = $base_url . '/search?q=' . urlencode($search) . '&api_key=' . $api_key;
$response = file_get_contents($url);
$data = json_decode($response);
return $data;
}

}

function hook_smithsonian_open_access_search($search) {
return SmithsonianOpenAccess::search($search);
}
