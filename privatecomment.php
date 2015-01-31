/**
* Make comments able to choose private or public...
* as seen in http://wpquestions.com/question/showLoggedIn/id/10468
*/

function restrict_comments( $comments , $post_id ){ 

	global $post;

	$user = wp_get_current_user();

	if($post->post_author == $user->ID){

			return $comments;

	}

	foreach($comments as $comment){
		$is_private = is_comment_private($comment);
		if (!$is_private && !($comment->comment_parent > 0)) {$new_comments_array[] = $comment;continue;}
		
		if( $comment->user_id == $user->ID || $post->post_author == $comment->user_id  ){
			if($post->post_author == $comment->user_id){
				if($comment->comment_parent > 0){
					$parent_comm = get_comment( $comment->comment_parent );
					if( ( $parent_comm->user_id == $user->ID ) ){
						$new_comments_array[] = $comment;		

					}
				}elseif (!$is_private){
						$new_comments_array[] = $comment;	
				}
			}elseif (!$is_private){
				$new_comments_array[] = $comment;
			}
		}

	}
	return $new_comments_array;
}

add_filter( 'comments_array' , 'restrict_comments' , 10, 2 );

function is_comment_private($comment) {
	$is_private = (get_comment_meta( $comment->comment_ID, 'private', 'no' )=='yes')?true:false;
	if ($is_private) {
		return true;
	} elseif ($comment->comment_parent > 0) {
		return is_comment_private(get_comment( $comment->comment_parent ));
	}
	return false;
}

add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {
  if ( !is_user_logged_in() ) {return;}
  if ( ( isset( $_POST['private'] ) ) && ( $_POST['private'] != '') )
  $private = wp_filter_nohtml_kses($_POST['private']);
  add_comment_meta( $comment_id, 'private', $private );

}
add_action( 'comment_form_logged_in_after', 'additional_fields' );
add_action( 'comment_form_after_fields', 'additional_fields' );

function additional_fields () {
	if ( !is_user_logged_in() ) {return;}
  echo '<p class="comment-form-phone">'.
      '<label for="private"><input id="private" name="private" type="checkbox" value="yes"  />' . __( 'Make this comment private' ) . '</label>'.
      '</p>';

}
