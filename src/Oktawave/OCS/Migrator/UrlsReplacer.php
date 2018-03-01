<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/OktawaveOCS.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Utils/FilesPath.php';

/**
 * Changes old URLs in posts' content to the new one.
 *
 * It walks over all posts and extracts URLs. If object from given URL exists
 * on Oktawave OCS, then its URL is being replaced with a OCS' one.
 *
 * @author Antoni Orfin <aorfin@octivi.com>
 */
class Oktawave_OCS_Migrator_UrlsReplacer
{
    const PER_PAGE = 5;

    public function __construct()
    {
    }

    /**
     * Replace content of posts.
     *
     * @see replaceUrls
     *
     * @param int $offset
     * @param int $perPage
     *
     * @return array
     */
    public function runOnPosts($offset = 0, $perPage = self::PER_PAGE)
    {
        remove_action('upload_dir', array(Oktawave_OCS_OktawaveOCS::getAttachmentsObserver(), 'onUploadDir'));

        $args = array(
            'post_type' => 'any',
            'posts_per_page' => $perPage,
            'offset' => $offset,
            'order' => 'ASC',
            'orderdby' => 'ID',
        );

        $query = new WP_Query($args);
        $totalPosts = $query->found_posts;
        $processed = 0;

        $posts = $query->get_posts();

        foreach ($posts as $post) {
            $post->post_content = $this->replaceUrls($post->post_content);
            $post->post_excerpt = $this->replaceUrls($post->post_excerpt);

            wp_update_post($post);
            $processed++;
        }

        add_action('upload_dir', array(Oktawave_OCS_OktawaveOCS::getAttachmentsObserver(), 'onUploadDir'));

        return array(
            'total' => $totalPosts,
            'processed' => $processed,
        );
    }

    /**
     * Extracts all URLs and replaces only those that exist on Oktawave OCS.
     *
     * @param string $content
     *
     * @return string
     */
    protected function replaceUrls($content)
    {
        $mediaUrl = Oktawave_OCS_OktawaveOCS::getMediaUrl();

        $baseUploadUrl = Oktawave_OCS_Utils_FilesPath::getBaseUploadUrl()."/";
        $regexp = "#\b({$baseUploadUrl})([^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#";

        preg_match_all($regexp, $content, $matches);

        if (isset($matches[2]) && $matches[2]) {
            foreach ($matches[2] as $filepath) {
                // Check if object exists on Oktawave OCS
                $checked = Oktawave_OCS_OktawaveOCS::getOCS()->checkObject($filepath);

                $fullUrl = $baseUploadUrl.$filepath;
                $oktawaveUrl = $mediaUrl."/".$filepath;

                // If so replace its URL
                if ($checked) {
                    $content = str_replace($fullUrl, $oktawaveUrl, $content);
                }
            }
        }

        return $content;
    }
}
