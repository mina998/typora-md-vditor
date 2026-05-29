<?php
/**
 * 保存文章
 * @author soroy <skiss.cc@gmail.com>
 */

declare(strict_types=1);
namespace TyporaMdVditor;
defined('ABSPATH') || exit();

class Save_Post
{
    /**
     * 初始化
     */
    public function __construct()
    {
        // 禁用修订版本
        add_filter('wp_revisions_to_keep', '__return_zero');
        // 修改保存的数据
        add_filter('wp_insert_post_data', [$this, 'insert_post_data'], 10, 2);
        // 保存文章后触发备份
        add_action('save_post', [$this, 'trigger_backup'], 10, 3);
    }

    /**
     * 过滤文章数据，写入 Vditor HTML 内容
     *
     * @param array<string, mixed> $data    要保存的数据
     * @param array<string, mixed> $postarr 原始数据
     * @return array<string, mixed>
     */
    public function insert_post_data(array $data, array $postarr): array
    {
        // 检查文章类型
        $post_type = $postarr['post_type'] ?? 'post';
        if (Helpers::is_supported_post_type($post_type) === false) {
            return $data;
        }
        // 检查是否是自动保存
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $data;
        }

        // 验证 nonce
        $post_id = $postarr['ID'] ?? 0;
        $nonce = $_POST['_wpnonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'update-post_' . $post_id)) {
            return $data;
        }
        // 检查权限
        if (!current_user_can('edit_posts')) {
            return $data;
        }

        // 写入 HTML 内容到 post_content
        $data['post_content']          = wp_kses_post(
            wp_unslash($_POST['vditor_ht_content'] ?? '')
        );
        $data['post_content_filtered'] = wp_kses_post(
            wp_unslash($_POST['vditor_md_content'] ?? '')
        );

        return $data;
    }

    /**
     * 保存文章后触发备份
     *
     * @param int      $post_id 文章 ID
     * @param \WP_Post $post    文章对象
     * @param bool     $update  是否是更新
     */
    public function trigger_backup(int $post_id, \WP_Post $post, bool $update): void
    {
        // 跳过自动保存
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        // 跳过修订版本
        if (wp_is_post_revision($post_id)) {
            return;
        }
        // 跳过自动草稿
        if ($post->post_status === 'auto-draft') {
            return;
        }
        // 检查文章类型
        if (Helpers::is_supported_post_type($post->post_type) === false) {
            return;
        }
        // 检查权限
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        // 获取前端传来的 Markdown 内容
        $vditor_md   = $_POST['vditor_md_content'] ?? '';
        $vditor_html = $_POST['vditor_ht_content'] ?? '';
        
        if (empty($vditor_md)) {
            return;
        }
        // 触发备份钩子
        do_action('typora_md_vditor_before_backup', $post_id, $vditor_md, $vditor_html);
    }


}
