<?php
/* 
This file is intended to populate GEO Zones (for Shipping) from categories when you have a hierarchy of countries as child categories and continents as main categories.

Output and logic:
###########
Array
(
    [name] => Central African Republic
    [category_id] => 61
    [parent_id] => 59
    [geo] => 4
    [cid] => 41
    [original] => Central African Republic
)
Array
(
    [name] => Egypt
    [category_id] => 62
    [parent_id] => 59
    [geo] => 4
    [cid] => 63
    [original] => Egypt
)
Array
(
    [name] => Ethiopia
    [category_id] => 64
    [parent_id] => 59
    [geo] => 4
    [cid] => 68
    [original] => Ethiopia
)
Array
(
    [name] => Morocco
    [category_id] => 66
    [parent_id] => 59
    [geo] => 4
    [cid] => 144
    [original] => Morocco
)
###########

You should edit it as you need.

For me 5 is the geozone is for 35 euro shipping geo zone, and so on.
*/


set_time_limit(0);
require_once('config.php');
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);



// Database 
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

function get_country($name){
	global $db;
	$cco = $db->query("SELECT country_id as cid, name as original FROM `oc_country` where UPPER(name) like '".strtoupper($name)."' or UPPER(name) like '%".strtoupper(str_replace(' ','%',$name))."%' ");
	return $cco->row;
	

}

$ccountries = $db->query("SELECT b.name, a.category_id, a.parent_id FROM `" . DB_PREFIX . "category` a left join `oc_category_description` b  on a.category_id = b.category_id where parent_id > 0 Order By `category_id` ASC");
$costarray = array(
/* 35 euro shipping (geozone id is 5 for me), mapping the child categories of this ids (continents in my case) to this geozone */
'145' => '5',
'90' => '5',
/* 25 euro shipping geozone */
'136' => '4',
'59' => '4',
'76' => '4',
/* 20 euro shipping geozone*/
'95' => '6'
);

foreach ($ccountries->rows as $cc) {
	if(isset($costarray[$cc['parent_id']])){
	$cc['geo'] = $costarray[$cc['parent_id']];
	$it = get_country($cc['name']);
	$cco = array_merge($cc, $it);

		if(isset($cco['cid'])) {
				echo '<pre>';
				print_R($cco);
				echo '</pre>';
				
			 $query = $db->query("INSERT INTO `" . DB_PREFIX . "zone_to_geo_zone` 
			 (`country_id`, `zone_id`, `geo_zone_id`, `date_added`, `date_modified`) 
			 VALUES 
			 ('" . $cco['cid'] . "', '0', '" . $cc['geo'] . "', now(), now())");

		}
	}

}	
