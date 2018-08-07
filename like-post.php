<?php
/*
Plugin Name: like post
Plugin URI: http://hanet.ir/like-post
Description: This plugin allows users of news sites, blogs, or personal to get a list of their favorite posts in their dashboard 
Version: 1.0.0
Author: behzad azand
Author URI: http://hanet.ir/about
*/

namespace LikePost;
if (!defined('LIKE_POST_PLUGIN_URL')) {
    define('LIKE_POST_PLUGIN_URL', plugin_dir_url(__FILE__));
}

class Lp_Handder
{
    protected $like_post_id;

    function __construct()
    {

        $this->create_like_post_table();
        add_action('admin_menu', array(&$this, 'register_like_post_item'));
        add_action('wp_enqueue_scripts', array(&$this, 'load_like_post_css_and_script'));
        add_filter('the_content', array(&$this, 'add_like_post_icon'), 20);
        add_action('wp_footer', array(&$this, 'add_custom_like_post_script'));
        add_action('wp_ajax_my_like_post', array(&$this, 'my_like_post'));

    }

    function register_like_post_item()
    {
        add_menu_page('علاقه های من', 'علاقه های من', 'read', 'my_favorite_list', array(&$this, 'my_custom_like_post'), 'dashicons-heart', 6);
    }

    function my_custom_like_post()
    {
        include_once('favorite_list_page.php');
    }

    function add_like_post_icon($content)
    {

        if (is_single() && is_user_logged_in()) {
            $user_id = get_current_user_id();
            $post_id = get_the_ID();
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(post_id) FROM wp_post_user_like WHERE user_id = $user_id AND post_id=$post_id");


            if ($count > 0) {
                $content .= "<div id='like_post'><a id='heart' class='tooltip'  ><img  src='" . LIKE_POST_PLUGIN_URL . "images/like.png
                '><span class='tooltiptext'>این پست در لیست علاقه مندی های شما قرار گرفت</span></a></div>";

            } else {
                $content .= "<div id='like_post'><a id='like' data-id='$post_id' ><img  src='" . LIKE_POST_PLUGIN_URL . "images/heart.png
'><span ></span></a></div>";
            }

        }
        return $content;
    }

    function add_custom_like_post_script()
    {
        $currentPath = $_SERVER['PHP_SELF'];
        // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
        $pathInfo = pathinfo($currentPath);
        // output: localhost
        $hostName = $_SERVER['HTTP_HOST'];
        // output: http://
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https://' ? 'https://' : 'http://';
        ?>
        <script>
            jQuery(function ($) {
                $('#like').click(function () {
                    var id = $(this).data('id');
                    $.ajax
                    ({
                        url: '<?php echo $protocol . $hostName . $pathInfo['dirname']; ?>/wp-admin/admin-ajax.php',
                        data: {'like_post_id': id, 'action': 'my_like_post'},
                        type: 'POST',
                        success: function (result) {
                            $('#like_post').html(result);
                        }
                    });
                });
            });
        </script>
        <?php
    }

    function load_like_post_css_and_script()
    {
        wp_enqueue_style('style1', LIKE_POST_PLUGIN_URL . 'css/like-post.css');
    }

    function my_like_post()
    {

        $currentPath = $_SERVER['PHP_SELF'];

        $pathInfo = pathinfo($currentPath);

        $hostName = $_SERVER['HTTP_HOST'];

        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https://' ? 'https://' : 'http://';

        $image = $protocol . $hostName . $pathInfo['dirname'] . "/" . "../wp-content/plugins/like-post/images/like.png";


        $table_name = 'wp_post_user_like';
        global $wpdb;
        $post_id = (isset($_POST['like_post_id'])) ? ($_POST['like_post_id']) : (null);

        // verify there is a post with such a number
        $post = get_post((int)$post_id);
        if (empty($post)) {
            return;
        }
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => get_current_user_id(),
                'post_id' => $post_id,
            )
        );

        echo "<div id='like_post'><a id='heart' class='tooltip'  ><img src='$image'><span class='tooltiptext'>این پست در لیست علاقه مندی های شما قرار دارد</span></a></div>";

        wp_die(); // this is required to terminate immediately and return a proper response
    }


    function create_like_post_table()
    {
        global $wpdb;
        $table_name = "wp_post_user_like";
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS wp_post_user_like ( ID int NOT NULL AUTO_INCREMENT, user_id BIGINT(20) UNSIGNED NOT NULL, FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE ON UPDATE CASCADE, post_id BIGINT(20) UNSIGNED NOT NULL, FOREIGN KEY (post_id) REFERENCES wp_posts(ID) ON DELETE CASCADE ON UPDATE CASCADE, PRIMARY KEY (ID)) $charset_collate;";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

}

$GLOBALS['Lp_Handder'] = new Lp_Handder();

?>