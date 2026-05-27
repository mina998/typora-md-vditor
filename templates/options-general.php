<?php

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();

$general_opts    = Typora_Config::get_plugin_options('general');
$max_size_mb     = $general_opts['max_file_size'] / 1024 / 1024;
$selected_types  = $general_opts['file_ext_list'] ?? [];

$general_meta    = Typora_Config::options_by_all('general');
$file_types_list = $general_meta['file_types'] ?? [];

?>
<div>
    <div class="typora-section">
        <h2><?php echo esc_html__('上传配置', 'typora-md-vditor'); ?></h2>

        <div class="typora-field-row">
            <div class="typora-label">
                <label for="upload_max_size">
                    <?php echo esc_html__('文件尺寸限制', 'typora-md-vditor'); ?>
                </label>
            </div>
            <div class="typora-input">
                <input type="number"
                    id="upload_max_size"
                    name="<?php echo esc_attr(OPTION_NAME); ?>[upload_max_size]"
                    value="<?php echo esc_attr((string) $max_size_mb); ?>"
                    min="1" max="999" />
                <span style="margin-left:4px;">MB</span>
                <p class="description">
                    <?php echo esc_html__('限制上传文件的最大体积，单位 MB，默认 5MB。', 'typora-md-vditor'); ?>
                </p>
            </div>
        </div>

        <div class="typora-field-row">
            <div class="typora-label">
                <?php echo esc_html__('允许的文件类型', 'typora-md-vditor'); ?>
            </div>
            <div class="typora-input">
                <div class="typora-checkbox-grid">
                    <?php foreach ($file_types_list as $ext => $mime) : ?>
                        <label>
                            <input type="checkbox"
                                name="<?php echo esc_attr(OPTION_NAME); ?>[upload_file_types][]"
                                value="<?php echo esc_attr($ext); ?>"
                                <?php checked(in_array($ext, $selected_types, true)); ?> />
                            .<?php echo esc_html($ext); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="description">
                    <?php echo esc_html__('勾选允许上传的文件扩展名。', 'typora-md-vditor'); ?>
                </p>
            </div>
        </div>

    </div>
</div>