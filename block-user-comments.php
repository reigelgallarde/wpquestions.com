<?php

function block_user_in_comments_form($return){
    if ( current_user_can('moderate_comments') ) { // you might need changing this line
		global $comment, $post;
		
		if ($post->post_author === $comment->user_id) {return;} // don't do for post author
		$block_users = get_post_meta($post->ID, 'block-user-comments', true );
		$checked = ' ';
		
		if (is_array($block_users) && !empty($block_users) && in_array($comment->user_id, $block_users)) {
			$checked = ' checked="checked" ';
		}
		
		$form = '<form method="POST" action="'.get_the_permalink($post->ID).'">';
		$form .= 'Block user? <input type="checkbox" name="block-user-comments" '.$checked.' value="yes" />';
		$form .= '<input type="hidden" name="user_id" value="'.$comment->user_id.'" />';
		$form .= '<input type="hidden" name="the_post" value="'.$post->ID.'" />';
		$form .= wp_nonce_field( 'block-user-comments', '_wpnonce', true, false );
		$form .= ' <input type="submit" value="submit" name="submit" /></form>';
		$return .= $form;
	}
    return $return;
}
add_filter('get_comment_author_link', 'block_user_in_comments_form');

function block_user_in_comments(){
	if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'block-user-comments' ) ) {
		if (isset($_POST['user_id']) && isset($_POST['the_post'])) {
			$user_id = sanitize_text_field($_POST['user_id']);
			$post_id = sanitize_text_field($_POST['the_post']);
			$block_users = get_post_meta($post_id, 'block-user-comments', true );
			if ($_POST['block-user-comments'] === 'yes') {
				if (is_array($block_users) && !empty($block_users)) {
					$block_users[] = $user_id;
				} else {
					$block_users = array($user_id);
				}
			} elseif (is_array($block_users) && !empty($block_users)) {
				$key = array_search($user_id, $block_users);
				if (isset($key))
				unset($block_users[$key]);
			}
			update_post_meta($post_id, 'block-user-comments', $block_users );
		}
	} 
}
add_filter('template_redirect', 'block_user_in_comments');

function my_comments_open( $open, $post_id ) {
	global $current_user;
	$block_users = get_post_meta($post_id, 'block-user-comments', true );
	if (is_array($block_users) && !empty($block_users)) {
		$key = array_search($current_user, $block_users);
		if (isset($key)) {
			$open = false;
		}
	}

	return $open;
}
add_filter( 'comments_open', 'my_comments_open', 10, 2 );

?>
