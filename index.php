<?php

@require_once(dirname(__FILE__) . '/config.php');

if(!isset($PHOTOBLOG_SITE_NAME)) $PHOTOBLOG_SITE_NAME = 'New PhotoBlog \o/';
if(!isset($PHOTOBLOG_DATA_DIR)) $PHOTOBLOG_DATA_DIR = '/dev/null';
if(!isset($PHOTOBLOG_IMAGE_SUFFIX) || count($PHOTOBLOG_IMAGE_SUFFIX)<1) $PHOTOBLOG_IMAGE_SUFFIX = array('gif', 'jpg', 'png');

?>

<!doctype html>
<html>
<head>
<title><?php print($PHOTOBLOG_SITE_NAME); ?></title>
</head>
<body>

<style>
* { margin:0; padding:0; }
body { padding:7vh; color:#080808; background:#fafafa; font-size:15pt; line-height:1.3em; font-family: Baskerville, "Baskerville Old Face", "Hoefler Text", Garamond, "Times New Roman", serif; }

.entry-title-image { float:left; }
.entry-title-image img { max-height:86vh; width:auto; margin:0 2em 1em 0; }
/*.entry-title-image img:hover { max-height:105vh; position:absolute; top:0; left:auto; right:auto; }*/

.entry-content { float:right; min-width:50vw; padding-top:0.4em; width:auto; }

.entry-title { padding-bottom:0.6em; /*margin-bottom:1em;*/ border-bottom:solid 1px #080808; }

.entry-title-text { float:left; font-size:180%; font-weight:bold; font-family: Rockwell, "Courier Bold", Courier, Georgia, Times, "Times New Roman", serif; padding-left:0.2em; }
.entry-title-date { float:right; vertical-align:bottom; padding:0 0.5em 0 1.3em; margin-top:0.35em; margin-bottom:-3em; }
.entry-title-clearer { clear:both; height:0; }

.entry-text { padding:0.9em 0.6em 0.3em 0.6em; /*background:#eaeaea;*/ background:rgba(234, 234, 234, 0.7); }

.entry-image-box { clear:both; }
.entry-image-box div { float:right; }
.entry-image img { max-height:20vh; width:auto; padding:4vh 4vh 0 1vh; }

.entry-vspacer { min-height:5em; clear:both; }

</style>

<div class="content">

<?php

$data_dir = $PHOTOBLOG_DATA_DIR;
$dir_list = scandir($data_dir, SCANDIR_SORT_DESCENDING);

$image_list_preg = '/\.(' . implode('|', $PHOTOBLOG_IMAGE_SUFFIX) . ')$/';

foreach($dir_list as $dir) {
	if($dir[0] == '.') continue;

	$curr_dir_path = $data_dir . '/' . $dir;
	$entry_list = scandir($curr_dir_path);

	// get images from posts
	$images = preg_grep($image_list_preg, $entry_list);

	$title_image = array_pop($images);
	$title_image_path = $data_dir . '/' . $dir . '/' . $title_image;

	// print first image
	if(is_file($title_image_path)) {
		print('<div class="entry">');
		print('<div class="entry-title-image"><img src="/' . $dir . '/' . $title_image . '" height="" width="" /></div>');
	
		// get data
		$title_file = $data_dir . '/' . $dir . '/title';
		$date_file = $data_dir . '/' . $dir . '/date';

		print('<div class="entry-content">');

		if(is_file($title_file) || is_file($date_file)) {
			print('<div class="entry-title">');

			if(is_file($title_file)) {
				print('<div class="entry-title-text">' . str_replace("\n", '', file_get_contents($title_file)) . '</div>');
			}

			if(is_file($date_file)) {
				print('<div class="entry-title-date">' . str_replace("\n", '', file_get_contents($date_file)) . '</div>');
			}

			print('<div class="entry-title-clearer"></div>');
			print('</div>'); // entry-title
		}

		$text_file = $data_dir . '/' . $dir . '/text';
		if(is_file($text_file)) {
			print('<div class="entry-text">');
			print(str_replace("\n", '<br />', file_get_contents($text_file)));
			print('</div>'); // entry-text
		}



		// print thumbnails of further images
		if(count($images)>0) {
			print('<div class="entry-image-box">');

			foreach($images as $curr_image) {
				print('<div class="entry-image"><img src="/' . $dir . '/' . $curr_image . '" /></div>');
			}

			print('</div>'); // entry-image-box
		}

		print('</div>'); // entry-content

		print('<div class="entry-vspacer">');

		print('</div>'); // entry
	}
}
?>

</div>
</body>
</html>
