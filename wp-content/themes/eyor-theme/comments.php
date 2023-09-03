<?php
/**
 * Comments generation template part.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @version   1.0.0
 */

if ( ! comments_open() && get_comments_number() < 1 ) {
	return;
}

if ( ! function_exists( 'qed_comment_form_renderer' ) ) {
	/**
	 * Move textarea to bottom.
	 *
	 * @return mixed $fields
	 */
	function qed_comment_field_to_bottom( $fields ) {
		$comment_field = $fields['comment'];
		unset( $fields['comment'] );
		$fields['comment'] = $comment_field;

		return $fields;
	}

	add_filter( 'comment_form_fields', 'qed_comment_field_to_bottom' );
}

if ( ! function_exists( 'qed_comment_form_renderer' ) ) {
	/**
	 * Comment form renderer function.
	 *
	 * @return void
	 */
	function qed_comment_form_renderer() {
		$commenter = wp_get_current_commenter();
		$fields = array(
				'author' => '<div class="row"><div class="col-md-12">' .
				            '<input id="author" name="author" type="text" placeholder="Name" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" />' .
				            '</div>',
				'email' => '<div class="col-md-12">' .
				           '<input id="email" name="email" type="text" placeholder="Email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" />' .
				           '</div>',
				'url' => '<div class="col-md-12">' .
				         '<input id="url" name="url" type="text" placeholder="Website" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />' .
				         '</div></div>',
		);
		$args = array(
				'fields' => apply_filters( 'comment_form_default_fields', $fields ),
				'comment_notes_before' => '',
				'comment_notes_after' => '',
				'comment_field' => '<div class="row"><div class="col-md-12"><textarea id="comment" name="comment">Your Message</textarea></div></div>',
				'label_submit' => '',
				'cancel_reply_link' => '<i class="fa fa-times"></i>',
				'title_reply'          => __( 'Leave a Comment' ),
				'title_reply_to'       => __( 'Leave a Reply to %s' ),
				'title_reply_before'   => '<div class="title title--center margin-bottom"><h3 id="reply-title" class="title__primary">',
				'title_reply_after'    => '</h3></div>',
		);
		ob_start();
		comment_form( $args );
		$form_html = '<div class="comments__form">' . ob_get_clean() . '</div>';
		wp_enqueue_script( 'comment-reply' );
		echo str_replace( '<input name="submit" type="submit" id="submit" class="submit" value="" />','<button type="submit" class="qedbtn">' . esc_html__( 'Submit', 'swishdesign' ) . '</button>', $form_html );
	}
}

if ( ! function_exists( 'qed_comment_renderer' ) ) {
	/**
	 * Comment renderer function.
	 *
	 * @param  Comment $comment comment instance.
	 * @param  array   $args    array of options.
	 * @param  int     $depth   current depth level.
	 * @return void
	 */
	function qed_comment_renderer( $comment, $args, $depth ) {
		$comment_html = get_avatar( $comment, 90 ) .
		               '<div class="comments__item__info">' .
		               '<div class="comments__item__name">' . get_comment_author_link() . '</div>' .
		               '<div class="comments__item__reply-link">' .
		               get_comment_reply_link(array(
				               'depth' => $depth,
				               'max_depth' => $args['max_depth'],
				               'reply_text' => esc_html__( 'Reply', 'swishdesign' ),
				               'login_text' => '',
		               )) .
		               '</div>' .
		               '</div>' .
		               '<div class="comments__item__date">' . get_comment_date() . '</div>' .
		               '<div class="comments__item__text">' . get_comment_text() . '</div>';

		printf( '<div class="%s" id="comment-%s">%s%s',
			implode( ' ', get_comment_class( 'comments__item' ) ),
			get_comment_ID(),
			$comment_html,
			! empty( $args['has_children'] ) ? '</div><div class="comments__item__reply">' : ''
		);
	}
}

if ( have_comments() ) {
echo '<div class="comments margin-top" id="comments">';
echo '<div class="section-title title title--center">' .
	 '<h3 class="title__primary">' . esc_html__( 'Comments', 'swishdesign' ) . '</h3>' .
	 '</div>';
	echo '<div class="padding-all comments-wrap margin-bottom">';
	wp_list_comments( array(
			'style' => 'div',
			'callback' => 'qed_comment_renderer',
			'type' => 'all',
			'avatar_size' => 60,
	) );
	echo '</div>';
	qed_comments_pagination();
};
qed_comment_form_renderer();
echo '</div><!-- end .comments -->';

