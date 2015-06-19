<?php
// Load configuration
@require_once(dirname(__FILE__) . '/config.php');

// Overwrite options for security
ini_set('display_errors', '0');

// Blog meta data
if(!isset($PHOTOBLOG_SITE_NAME)) $PHOTOBLOG_SITE_NAME = 'New PhotoBlog \o/';
if(!isset($PHOTOBLOG_DOMAIN)) $PHOTOBLOG_DOMAIN = 'photoblog.local';
if(!isset($PHOTOBLOG_AUTHOR)) $PHOTOBLOG_AUTHOR = 'Your Name';
if(!isset($PHOTOBLOG_EMAIL)) $PHOTOBLOG_EMAIL = 'mail@photoblog.local';
if(!isset($PHOTOBLOG_EXIF)) $PHOTOBLOG_EXIF = false;
if(!isset($PHOTOBLOG_EXIF_TAGS) || count($PHOTOBLOG_EXIF_TAGS)<1) {
	$PHOTOBLOG_EXIF_TAGS = array(
	'DateTimeOriginal'	=> 'Datum',
	'FocalLength'		=> 'Brennweite',
	'Flash'			=> 'Blitz',
	'DigitalZoomRatio'	=> 'Digitalzoom',
	'WhiteBalance'		=> 'Weißabgleich',
	'Software'		=> 'Bearbeitungssoftware',
	);
}

// Data options
if(!isset($PHOTOBLOG_DATA_DIR)) $PHOTOBLOG_DATA_DIR = '/dev/null';
if(!isset($PHOTOBLOG_IMAGE_SUFFIX) || count($PHOTOBLOG_IMAGE_SUFFIX)<1) $PHOTOBLOG_IMAGE_SUFFIX = array('gif', 'jpg', 'png');

// Styling
if(!isset($PHOTOBLOG_DIVIDER_TEXT)) $PHOTOBLOG_DIVIDER_TEXT = '&#9809;';
if(!isset($PHOTOBLOG_STYLE)) $PHOTOBLOG_STYLE = 'starter';

?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php print($PHOTOBLOG_SITE_NAME); ?></title>
</head>
<body>
<style>

* { margin:0; padding:0; }
body { padding:7vh; }

.content { max-width:100%; table-layout: fixed; word-wrap:break-all; }

.entry-title-image { float:left; }


/* http://www.cssplay.co.uk/menu/cssplay-toggle-zoom.html */
.image-container input { display:none; position:absolute; left:-99px; }
.image-container label { display:block; cursor:pointer; }
.entry-title-image { min-width:40vw; }
.entry-title-image .image-container input:checked + label { min-height:90vh; min-width:1px; }
.entry-image-box .image-container input:checked + label { min-height:90vh; min-width:1px; }

.image-container .image-box img { max-height:86vh; max-width:92vw; }
.image-container input:checked + label .image-box { position:fixed; top:0vh; left:0vh; right:0vh; bottom:0vh; }
.image-container input:checked + label .image-box img { max-height:90vh; max-width:50vw; padding:5vh 5vh 100vh 5vh; margin:0; }


.image-box { display:inline-block; }

.image-info,
.image-info *,
.image-action { display:none; }

.image-container input:checked + label .image-info,
.image-container input:checked + label .image-info *,
.image-container input:checked + label .image-action { display:block; }

.image-container input:checked + label .image-info,
.image-container input:checked + label .image-action { position:fixed; }


/* https://friendlybit.com/css/emulating-tables-automatic-width/ */
.entry-content { min-width:50vw; overflow:auto; }


.entry-title-text { float:left; }
.entry-title-date { float:right; }
.entry-title-clearer { clear:both; height:0; }

.entry-image-box { clear:both; }
.entry-image-box div { float:right; }
.entry-image-box .entry-image img { max-height:20vh; width:auto; }

.entry-vspacer { display:block; padding:0.7em 0 1.2em 0; text-align:center; clear:both; }
.entry-vspacer span.space-left { float:left; }
.entry-vspacer span.space-right { float:right; }
.entry-vspacer span.space-text { margin:0 auto 0 auto; }

.footer { margin:0 -7vh -7vh -7vh; }

<?php
// Load extra css styles

$add_css_file = $PHOTOBLOG_DATA_DIR . '/' . $PHOTOBLOG_STYLE . '.css';
if(is_file($add_css_file)) {
	$additional_style = @file_get_contents($add_css_file);
	if($additional_style) {
		print($additional_style);
	}
}

?>

</style>

<div class="content">

<?php

// Generate image container
function image_container_template($type, $data_dir, $dir, $image_file, $print_exif) {
	print('<div class="' . $type . '">');
	print('<div class="image-container">');
	print('<input type="checkbox" id="' . $dir . '-' . $image_file . '"><label for="' . $dir . '-' . $image_file . '">');
	print('<div class="image-box">');

	print('<div class="image-info"><span class="image-info-text">');

	if($print_exif) {
		$exif_data = @exif_read_data($data_dir . '/' . $dir . '/' . $image_file, 'ANY_TAG');
		print('<b>' . $exif_data['FileName'] . '</b><br />');
		if($exif_data['ImageDescription'] != '') print('<i>' . $exif_data['ImageDescription'] . '</i>');

		if($exif_data['COMPUTED']['Height'] != '' && $exif_data['COMPUTED']['Width'] != '') print('Format: ' . $exif_data['COMPUTED']['Height'] . ' x ' . $exif_data['COMPUTED']['Width'] . ' px<br />');
		if($exif_data['Make'] != '') print('Ger&auml;t: ' . $exif_data['Make'] . ' ' . $exif_data['Model'] . '<br />');

		foreach($PHOTOBLOG_EXIF_TAGS as $key => $value) {
			if(isset($exif_data[$key]) && $exif_data[$key] != '') print($value . ': ' . $exif_data[$key] . '<br />');
		}
	}

	print('</span></div>'); // image-info-text, image-info
	print('<img src="/' . $dir . '/' . $image_file . '" />');
	print('</div>'); // image-box
	print('<div class="image-action">Click anywhere to close.</div>');
	print('</label>');
	print('</div>'); // image-container
	print('</div>'); // $type
	return true;
}


// Read and print entries
$data_dir = $PHOTOBLOG_DATA_DIR;
$dir_list = @scandir($data_dir, SCANDIR_SORT_DESCENDING);

$image_list_preg = '/\.(' . implode('|', $PHOTOBLOG_IMAGE_SUFFIX) . ')$/';

foreach($dir_list as $dir) {
	if($dir[0] == '.') continue;

	$curr_dir_path = $data_dir . '/' . $dir;

	if(!is_dir($curr_dir_path)) continue;

	$entry_list = scandir($curr_dir_path);

	// get images from posts
	$images = preg_grep($image_list_preg, $entry_list);

	$title_image = array_pop($images);
	$title_image_path = $data_dir . '/' . $dir . '/' . $title_image;

	if(!is_file($title_image_path)) {
		foreach($PHOTOBLOG_IMAGE_SUFFIX as $suffix) {
			$alt_title_image_path = $data_dir . '/' . $dir . '/' . 'title.' . $suffix;
			if(is_file($alt_title_image_path)) {
				$title_image_path = $alt_title_image_path;
				break;
			}
		}
	}

	print('<div class="entry">');

	// print first image
	if(is_file($title_image_path)) {
		image_container_template('entry-title-image', $data_dir, $dir, $title_image, $PHOTOBLOG_EXIF);
	}
	
		// get data
		$title_file = $data_dir . '/' . $dir . '/title';
		$date_file = $data_dir . '/' . $dir . '/date';

		print('<div class="entry-content">');

		print('<div class="entry-title">');

		if(is_file($title_file) || is_file($date_file)) {

			if(is_file($title_file)) {
				print('<div class="entry-title-text">' . str_replace("\n", '', file_get_contents($title_file)) . '</div>');
			}

			if(is_file($date_file)) {
				print('<div class="entry-title-date">' . str_replace("\n", '', file_get_contents($date_file)) . '</div>');
			}

			print('<div class="entry-title-clearer"></div>');
		}

		print('</div>'); // entry-title


		$text_file = $data_dir . '/' . $dir . '/text';
		if(is_file($text_file)) {
			print('<div class="entry-text">');
			print('<div class="entry-text-container">');
			print(str_replace("\n", '<br />', file_get_contents($text_file)));
			print('</div>'); // entry-text-container
			print('</div>'); // entry-text
		}



		// print thumbnails of further images
		if(count($images)>0) {
			print('<div class="entry-image-box">');

			foreach($images as $curr_image) {
				image_container_template('entry-image', $data_dir, $dir, $curr_image, $PHOTOBLOG_EXIF);
			}

			print('</div>'); // entry-image-box
		}

		print('</div>'); // entry-content

		print('<div class="entry-vspacer"><span class="space-left">&nbsp;</span><span class="space-text">' . $PHOTOBLOG_DIVIDER_TEXT . '</span><span class="space-right">&nbsp;</span></div>');

		print('</div>'); // entry
}
?>

</div>
<div class="footer">
<a href="http://<?php print($PHOTOBLOG_DOMAIN) ?>/"><?php print($PHOTOBLOG_DOMAIN) ?></a> // &copy; <?php print($PHOTOBLOG_AUTHOR) ?> &lt;<a href="mailto:<?php print($PHOTOBLOG_EMAIL) ?>"><?php print($PHOTOBLOG_EMAIL) ?></a>&gt;
</div>
</body>
</html>
