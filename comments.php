<?php
/**
 * 评论区
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( post_password_required() ) {
    return;
}

/**
 * 自定义评论渲染回调
 *
 * 必须在 wp_list_comments 调用前定义。
 *
 * @since 1.0.0
 *
 * @param WP_Comment $comment 评论对象
 * @param array      $args    参数
 * @param int        $depth   深度
 *
 * @return void
 */
function slv_render_comment( $comment, $args, $depth ): void {
    $GLOBALS['comment'] = $comment;
    ?>
    <li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'slv-comment' ); ?>>
        <div id="div-comment-<?php comment_ID(); ?>" class="slv-comment__inner">

            <div class="slv-comment__avatar">
                <?php echo get_avatar( $comment, isset( $args['avatar_size'] ) ? (int) $args['avatar_size'] : 40 ); ?>
            </div>

            <div class="slv-comment__body">

                <div class="slv-comment__header">
                    <span class="slv-comment__author">
                        <?php echo esc_html( get_comment_author( $comment ) ); ?>
                    </span>
                    <time class="slv-comment__date" datetime="<?php echo esc_attr( get_comment_date( DATE_W3C, $comment ) ); ?>">
                        <?php echo esc_html( get_comment_date( '', $comment ) ); ?>
                    </time>
                </div>

                <div class="slv-comment__content">
                    <?php comment_text( $comment ); ?>
                </div>

                <div class="slv-comment__actions">
                    <?php
                    if ( '0' == $comment->comment_approved ) {
                        echo '<em class="slv-comment__moderation">' . esc_html__( '评论正在等待审核。', 'sunlyvo-nexus' ) . '</em>';
                    }
                    ?>
                </div>

            </div>
        </div>
    <?php
    // 注意：不输出 </li>，由 wp_list_comments 自动闭合
}
?>
<section id="comments" class="slv-comments">

    <?php if ( have_comments() ) : ?>

        <h2 class="slv-comments__title">
            <?php
            $slv_count = get_comments_number();

            if ( 1 === (int) $slv_count ) {
                esc_html_e( '1 条评论', 'sunlyvo-nexus' );
            } else {
                printf(
                    /* translators: %s: comment count */
                    esc_html__( '%s 条评论', 'sunlyvo-nexus' ),
                    esc_html( number_format_i18n( (int) $slv_count ) )
                );
            }
            ?>
        </h2>

        <ol class="slv-comments__list">
            <?php
            wp_list_comments(
                [
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 40,
                    'callback'    => 'slv_render_comment',
                ]
            );
            ?>
        </ol>

        <?php
        the_comments_pagination(
            [
                'prev_text' => '‹ ' . esc_html__( '较早', 'sunlyvo-nexus' ),
                'next_text' => esc_html__( '较新', 'sunlyvo-nexus' ) . ' ›',
            ]
        );
        ?>

    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() > 0 ) : ?>
        <p class="slv-comments__closed">
            <?php esc_html_e( '评论已关闭。', 'sunlyvo-nexus' ); ?>
        </p>
    <?php endif; ?>

    <?php
    $slv_commenter = wp_get_current_commenter();

    comment_form(
        [
            'class_form'           => 'slv-comment-form',
            'title_reply'          => esc_html__( '发表评论', 'sunlyvo-nexus' ),
            'title_reply_before'   => '<h3 id="reply-title" class="slv-comment-form__title">',
            'title_reply_after'    => '</h3>',
            'comment_notes_before' => '',
            'comment_notes_after'  => '',
            'comment_field'        => sprintf(
                '<p class="slv-comment-form__field"><label for="comment" class="screen-reader-text">%s</label><textarea id="comment" name="comment" cols="45" rows="6" placeholder="%s" required></textarea></p>',
                esc_html__( '评论内容', 'sunlyvo-nexus' ),
                esc_attr__( '写下你的评论…', 'sunlyvo-nexus' )
            ),
            'fields'               => [
                'author' => sprintf(
                    '<p class="slv-comment-form__field"><label for="author" class="screen-reader-text">%s</label><input id="author" name="author" type="text" value="%s" placeholder="%s" required></p>',
                    esc_html__( '姓名', 'sunlyvo-nexus' ),
                    esc_attr( $slv_commenter['comment_author'] ?? '' ),
                    esc_attr__( '姓名', 'sunlyvo-nexus' )
                ),
                'email'  => sprintf(
                    '<p class="slv-comment-form__field"><label for="email" class="screen-reader-text">%s</label><input id="email" name="email" type="email" value="%s" placeholder="%s" required></p>',
                    esc_html__( '邮箱', 'sunlyvo-nexus' ),
                    esc_attr( $slv_commenter['comment_author_email'] ?? '' ),
                    esc_attr__( '邮箱', 'sunlyvo-nexus' )
                ),
                'url'    => sprintf(
                    '<p class="slv-comment-form__field"><label for="url" class="screen-reader-text">%s</label><input id="url" name="url" type="url" value="%s" placeholder="%s"></p>',
                    esc_html__( '网址', 'sunlyvo-nexus' ),
                    esc_attr( $slv_commenter['comment_author_url'] ?? '' ),
                    esc_attr__( '网址（可选）', 'sunlyvo-nexus' )
                ),
            ],
            'label_submit'         => esc_html__( '发表', 'sunlyvo-nexus' ),
            'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s slv-btn slv-btn--primary">%4$s</button>',
            'submit_field'         => '<p class="slv-comment-form__submit">%1$s %2$s</p>',
        ]
    );
    ?>

</section>