<?php

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();

// 处理表单提交
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['_typora_nonce'])
    && check_admin_referer('typora_md_vditor_settings', '_typora_nonce')
    && current_user_can('manage_options')
) {
    $current_vidtor  = Typora_Config::get_plugin_options('vidtor');
    $current_admin   = Typora_Config::get_plugin_options('admin');
    $current_general = Typora_Config::get_plugin_options('general');

    $input = $_POST[OPTION_NAME] ?? [];

    $update = [
        'vidtor' => [
            'theme'   => sanitize_text_field($input['theme'] ?? $current_vidtor['theme']),
            'mode'    => sanitize_text_field($input['mode'] ?? $current_vidtor['mode']),
            'toolbar' => $current_vidtor['toolbar'],
        ],
        'admin' => [
            'post_types'     => array_map('sanitize_text_field', $input['post_types'] ?? []),
            'backup_enabled' => !empty($input['backup_enabled']),
            'backup_dir'     => $current_admin['backup_dir'],
        ],
        'general' => [
            'max_file_size' => absint($input['upload_max_size'] ?? 5) * 1024 * 1024,
            'head_size'     => $current_general['head_size'],
            'tail_size'     => $current_general['tail_size'],
            'file_ext_list' => array_map('sanitize_text_field', $input['upload_file_types'] ?? []),
        ],
    ];

    Typora_Config::update_plugin_options($update);

    echo '<div class="notice notice-success"><p>' . esc_html__('设置已保存。', 'typora-md-vditor') . '</p></div>';
}
?>

<link rel="stylesheet" href="<?php echo plugin_dir_url(PLUGIN_FILE) . 'assets/css/options.css'; ?>">

<div class="wrap typora-md-settings">
    <h1 id="typora-md-settings-title"><?php echo esc_html__('Typora MD 编辑器设置', 'typora-md-vditor'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('typora_md_vditor_settings', '_typora_nonce'); ?>
        <?php
        require __DIR__ . '/options-general.php';
        require __DIR__ . '/options-admin.php';
        require __DIR__ . '/options-vditor.php';
        ?>
        <?php submit_button(__('保存设置', 'typora-md-vditor')); ?>
    </form>
</div>