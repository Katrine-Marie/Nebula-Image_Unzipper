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

namespace nebula\unzipper;

if(!defined('ABSPATH')){
	exit('Go away!');
}

add_action( 'admin_menu', __NAMESPACE__.'\nebula_start_image_unzip' );

function nebula_start_image_unzip(){
	add_menu_page('Upload Image Zip', 'Upload Image Zip','manage_options','nebula_upload_image_zips',__NAMESPACE__.'\nebula_upload_image_zips','dashicons-media-archive',15 );
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
	echo '<div class="wrap">';
	echo '<h1>Upload An Image Zip File</h1>';

	if(isset($_FILES['fileToUpload'])) {

		// Get the current uploads directory including the year and month.
		$dir = "../wp-content/uploads" . \wp_upload_dir()['subdir'];

		// Use regular PHP to upload the zip file to the uploads directory (using the $dir variable from above).
		$target_file 	= $dir . '/' . basename($_FILES["fileToUpload"]["name"]);
		move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
		$file_name 		= basename( $_FILES["fileToUpload"]["name"]);

		// Create (instantiate) a new zip utility object.
		$zip = new \ZipArchive;

		// Attempt to open the zip file.
		$res = $zip->open($target_file);

		// Process the zip file if it can be opened.
		if ($res === TRUE) {
		  $zip->extractTo($dir);

			echo "<h3 style='color:#090;'>The zip file $file_name was successfully unzipped to  " . \wp_upload_dir()['url'] . ".</h3>";

			// Display a message with the number of media files in the zip file.
			echo "There are ".$zip->numFiles." files in this zip file. <br/>";

			// Loop through each media file to process it for the media library.
			for($i=0; $i < $zip->numFiles; $i++) {

				// Get the URL of the media file.
				$media_file_name = \wp_upload_dir()['url'] . '/' . $zip->getNameIndex($i);

				// Get the file type of the media file.
				$filetype 	= \wp_check_filetype( basename( $media_file_name ), null );
				$allowed 	= nebula_allowed_file_types($filetype['type']);

				if($allowed) {
					// Display a link for the user to view the file on upload...
					echo '<a href="'.$media_file_name.'" target="_blank">' . $media_file_name . '</a> Type: '.$filetype['type'].'<br/>';

					// Prepare the attachment information array that will be used by the media library.
					$attachment = array(
						'guid'           => $media_file_name,
						'post_mime_type' => $filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', $zip->getNameIndex($i) ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);

					// Insert the attachment.
					$attach_id = \wp_insert_attachment( $attachment, $dir . '/' . $zip->getNameIndex($i) );

					// Generate the metadata for the attachment.
					$attach_data = \wp_generate_attachment_metadata( $attach_id, $dir . '/' . $zip->getNameIndex($i ));
					\wp_update_attachment_metadata( $attach_id, $attach_data );
				} else {
					echo $zip->getNameIndex($i) . ' could not be uploaded. Its file type of ' . $filetype['type'] . ' is not allowed.<br/>' ;
				}
			}

			echo "</ul>";

		} else {
			echo "<h3 style='color:#F00;'>Error! The file was not successfully unzipped.</h3>";
		}

		$zip->close();

	}

	echo ('
		<form style="margin-top:20px;" method="post" action="/wp-admin/admin.php?page=nebula_upload_image_zips"
		enctype="multipart/form-data" class="server-form">
			<label for="fileToUpload">Select ZIP File: <input type="file" name="fileToUpload" id="fileToUpload"></label>
			<div style="margin-top:10px;" class="submit-button-section">
				<input type="submit" value="Upload ZIP File" name="submit">
			</div>
		</form>
	');

	echo '</div>';
}
