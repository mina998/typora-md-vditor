<?php

/**
 * 前端处理类
 * 
 * @author soroy <skiss.cc@gmail.com>
 */

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();

class Frontend
{

    public function __construct()
    {
        add_filter('the_content', [$this, 'post_content_handle']);
        add_filter('post_class', [$this, 'post_class']);
        add_action('wp_footer', [$this, 'output_md_content']);
        add_action('wp_enqueue_scripts', [$this, 'vditor_render_assets']);
    }

    public function post_content_handle(string $content): string
    {
        $post_type = get_post_type();
        if (Helpers::is_supported_post_type($post_type) === false) {
            return $content;
        }
        // Wrap HTML content as fallback (SEO/noscript), Vditor.preview() will replace it
        return '<div id="typora-md-render">' . $content . '</div>';
    }

    public function post_class(array $classes)
    {
        $post_type = get_post_type();
        if (is_singular($post_type) === false) {
            return $classes;
        }
        if (Helpers::is_supported_post_type($post_type) === true) {
            $classes[] = 'typora-mdc';
        }
        return $classes;
    }

    public function output_md_content(): void
    {
        if (is_singular() === false) {
            return;
        }
        $post_id   = get_the_ID();
        $post_type = get_post_type();
        if (Helpers::is_supported_post_type($post_type) === false) {
            return;
        }
        $md = get_post_field('post_content_filtered', $post_id);
        if (empty($md)) {
            return;
        }
        printf(
            '<script>window._typoraMdData = %s;</script>',
            wp_json_encode($md)
        );
    }

    public function vditor_render_assets()
    {
        $post_type = get_post_type();
        if (is_singular($post_type) === false) {
            return;
        }
        if (Helpers::is_supported_post_type($post_type) === false) {
            return;
        }
        // 前端代码高亮
        wp_enqueue_style('highlight', CDN_PREFIX . '/dist/js/highlight.js/styles/github.min.css');
        wp_enqueue_script('highlight', CDN_PREFIX . '/dist/js/highlight.js/highlight.min.js');
        // Vditor 渲染办法
        wp_enqueue_style('vditor', CDN_PREFIX . '/dist/index.css');
        wp_enqueue_script('vditor', CDN_PREFIX . '/dist/index.min.js', [], VERSION, true);
        //重置样式
        wp_enqueue_style('vditor-reset', plugin_dir_url(PLUGIN_FILE) . '/assets/css/typora-reset.css');
        //
        wp_enqueue_script('frontend-md', plugin_dir_url(PLUGIN_FILE) . '/assets/js/frontend.js', ['vditor'], VERSION, true);
    }
}
