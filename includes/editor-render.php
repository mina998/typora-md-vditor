<?php

/**
 * 插件编辑器渲染类
 * 主要用来渲染编辑器的HTML代码
 * @author soroy <skiss.cc@gmail.com>
 */

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();

class Editor_Render
{
    public $vditor_version = '3.11.2';
    public function __construct()
    {
        // 禁用 Gutenberg
        add_filter('use_block_editor_for_post_type', [$this, 'disable_gutenberg'], 100, 2);
        // 渲染编辑器容器
        add_action('edit_form_after_title', [$this, 'render_editor_container']);
        // 加载静态资源
        add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
    }

    /**
     * 禁用 Gutenberg 编辑器
     */
    public function disable_gutenberg(bool $use_block_editor, string $post_type): bool
    {
        $is_block_editor =  Helpers::is_supported_post_type($post_type) ? false : $use_block_editor;
        return $is_block_editor;
    }

    /**
     * 渲染编辑器容器
     */
    public function render_editor_container(): void
    {
        global $post_type;
        if (Helpers::is_supported_post_type($post_type) === false) {
            return;
        }
        Helpers::require(PLUGIN_DIR . '/templates/vditor-container.php');
    }

    /**
     * 加载静态资源
     */
    public function enqueue_editor_assets(string $hook)
    {
        global $post_type, $post;
        if (in_array($hook, ['post.php', 'post-new.php'], true) === false) {
            return;
        }
        if (Helpers::is_supported_post_type($post_type) === false) {
            return;
        }
        // Vditor Style
        wp_enqueue_style('vditor', CDN_PREFIX . '/dist/index.css', [], $this->vditor_version);
        // Vditor Override Styles
        wp_enqueue_style('editor', plugin_dir_url(PLUGIN_FILE) . 'assets/css/editor.css', ['vditor'], $this->vditor_version);
        // editor JS
        wp_enqueue_script_module('editor', plugin_dir_url(PLUGIN_FILE) . 'assets/js/editor.js', [], $this->vditor_version, ['in_footer' => true]);
        // 本地化数据
        $frontend_parameters = $this->frontend_options($post);
        wp_localize_script('editor', LOCALIZE_SCRIPT_NAME, $frontend_parameters);
    }

    /**
     * 构建前端使用的全局变量参数
     */
    private function frontend_options(\WP_Post $post): array
    {
        $locale = get_locale();
        $langs  = Typora_Config::options_by_all('vidtor')['langs'];
        // 如果VDitor 不支持WordPress语言 设置成英文
        if (in_array($locale, $langs, true) === false) {
            $locale = 'en_US';
        }
        // 通用变量参数
        $general               = Typora_Config::get_plugin_options('general');
        $general['upload_url'] = admin_url('admin-ajax.php');
        $general['nonce']      = wp_create_nonce(VDITOR_NONCE_NAME);
        $general['post_md']    = wp_specialchars_decode( $post->post_content_filtered, ENT_QUOTES );
        $general['editor_id']  = 'typora-md';
        // Vditor 内部初始化参数
        $vidtor                = Typora_Config::get_plugin_options('vidtor');
        $vidtor['lang']        = $locale;
        $vidtor['icon']        = 'material'; // ant, material
        // 返回值
        return [ 'init' => $general, 'opts' => $vidtor, ];
    }
    //
}
