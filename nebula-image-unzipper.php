<?php
/**
* Plugin Name: Nebula Image Unzipper
* Description: This plugin unzips image zip files, and uploads the files into the media manager
* Author: Katrine-Marie Burmeister
* Version: 1.0.0
* Author URI: https://fjordstudio.dk
* Text Domain: nebula-unzipper
* License:     GNU General Public License v3.0
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

if(!defined('ABSPATH')){
	exit('Go away!');
}

add_action( 'admin_menu', 'nebula_start_image_unzip' );

function nebula_start_image_unzip(){
	add_menu_page('Upload Image Zip', 'Upload Image Zip','manage_options','nebula_upload_image_zips','nebula_upload_image_zips','dashicons-media-archive',15 );
}

function nebula_allowed_file_types($filetype){
	$allowed_file_types = array('image/png','image/jpeg','image/jpg','image/gif');
	if(in_array($filetype,$allowed_file_types))
	{
		return true;
	} else {
		return false;
	}
}

function nebula_upload_image_zips(){

}
