<?php

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();

$admin_opts = Typora_Config::get_plugin_options('admin');
$admin_meta = Typora_Config::options_by_all('admin');
$post_types = $admin_meta['post_types'];
?>
<div>
    <div class="typora-section">
        <h2><?php echo esc_html__('编辑器适用范围', 'typora-md-vditor'); ?></h2>

        <div class="typora-field-row">
            <div class="typora-label">
                <?php echo esc_html__('启用的内容类型', 'typora-md-vditor'); ?>
            </div>
            <div class="typora-input">
                <div class="typora-checkbox-grid">
                    <?php foreach ($post_types as $type_name => $type_label) : ?>
                        <label>
                            <input
                                type="checkbox"
                                name="<?php echo esc_attr(OPTION_NAME); ?>[post_types][]"
                                value="<?php echo esc_attr($type_name); ?>"
                                <?php checked(in_array($type_name, $admin_opts['post_types'] ?? [], true)); ?> />
                            <?php echo esc_html($type_label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="description">
                    <?php echo esc_html__('选择使用 Vditor 编辑器的内容类型。', 'typora-md-vditor'); ?>
                </p>
            </div>
        </div>

        <div class="typora-field-row">
            <div class="typora-label">
                <?php echo esc_html__('内容备份', 'typora-md-vditor'); ?>
            </div>
            <div class="typora-input">
                <label>
                    <input type="checkbox"
                        name="<?php echo esc_attr(OPTION_NAME); ?>[backup_enabled]"
                        value="1"
                        <?php checked(!empty($admin_opts['backup_enabled'])); ?> />
                    <?php echo esc_html__('保存文章时自动备份 Markdown 内容', 'typora-md-vditor'); ?>
                </label>
            </div>
        </div>
    </div>
</div>