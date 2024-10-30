<?php
/*
Plugin Name: Heello Feed
Plugin URI: http://heelo.com
Description: Heello Feed Widget Plugin
Author: Nicholas Krut
Version: 0.1
Author URI: http://nikru.com/
*/

function heello_widget($args) {
	global $pagenow;
	
	if ($_GET['action'] != 'register'){ // we don't want to display this form in the heello Page
	//mydebug($args);
	extract($args);
	$options = get_option('widget_heello');
	$heellouser = empty($options['heellouser']) ? __('heello') : apply_filters('widget_heellouser', $options['heellouser']);
?>
		<?php echo $before_widget; ?>
			<?php echo $before_heellouser . $heellouser . $after_heellouser; ?>'s <a href="http://heello.com/" target="_blank">heello</a> feed

		<?php the_heello_form($heellouser);	?>
		<?php echo $after_widget; ?>
<?php
}
}
function the_heello_form($args=null) {
	$register_button=__('Register');
	if(isset($args['register_button'])) $register_button = $args['register_button'];
	
	$heellopage = file_get_contents("http://heello.com/" . $args);
	preg_match_all('/<div class="ping" data-id="(\d+)" data-username="(.+?)" style="(.+?)">(.+?)<div style="clear:both;"><\/div>\W+<\/div>/si', $heellopage, $m);
	foreach($m[1] as $index => $username)
	{
		$d = array();
		preg_match('/<small>\W+<a href="(.+?)">(.+?)<\/a>\W+<a href="(.+?)">(.+?)<\/a>\W+<\/small>|<small>\W+<a href="(.+?)">(.+?)<\/a>\W+<\/small>/si', $m[4][$index], $d['timeAndType']);
		preg_match('/<div class="ping-text">(.+?)<\/div>/si',                                           $m[4][$index], $d['messageOfUser']);
		if ($d['timeAndType'][1] == '')
			$ping = array(
				"when"  => $d['timeAndType'][6],
				"link"  => $d['timeAndType'][5],
				"text"  => trim(strip_tags($d['messageOfUser'][1]))
			);
		else
			$ping = array(
				"when"  => $d['timeAndType'][2],
				"link"  => $d['timeAndType'][1],
				"extra" => $d['timeAndType'][4],
				"elink" => $d['timeAndType'][3],
				"text"  => trim(strip_tags($d['messageOfUser'][1]))
			);
		$pings[] = $ping;
	}
	
	foreach($pings as $index => $ping) {
		if ($index <= 3) {
?>
	<ul class="heello-feed">
		<li>
			<span class="ping-text">"<?=$ping['text'];?>"</span>
			<br />
			<small>
			<? if ($ping['extra']) { ?>
				<a class="ping-extra-link" href="http://heello.com<?=$ping['elink'];?>" target="_blank"><?=$ping['extra'];?></a>&nbsp;
			<? } ?>
			<i><a class="ping-main-link" href="http://heello.com<?=$ping['link'];?>" target="_blank"><?=$ping['when'];?></a></i>
			</small>
		</li>
	</ul>
<?
		}
	}
}
/**
 * Display and process heello widget options form.
 *
 */
function heello_widget_control() {
	$options = $newoptions = get_option('widget_heello');
	if ( isset($_POST["heello-submit"]) ) {
		$newoptions['heellouser'] = strip_tags(stripslashes($_POST["heello-heellouser"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_heello', $options);
	}
	$heellouser = attribute_escape($options['heellouser']);
?>
			<p><label for="heello-heellouser"><?php _e('Heello Username:'); ?> <input class="widefat" id="heello-heellouser" name="heello-heellouser" type="text" value="<?php echo $heellouser; ?>" /></label></p>
			<input type="hidden" id="heello-submit" name="heello-submit" value="1" />
<?php
}

function heello_widget_init(){
	register_sidebar_widget("heello feed", "heello_widget");
	register_widget_control("heello feed","heello_widget_control");
}

add_action("plugins_loaded", "heello_widget_init");