<?php

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();

$vidtor_opts = Typora_Config::get_plugin_options('vidtor');
$vditor_meta = Typora_Config::options_by_all('vidtor');
?>
<div>
    <div class="typora-section">
        <h2><?php echo esc_html__('Vditor 编辑器选项', 'typora-md-vditor'); ?></h2>

        <div class="typora-field-row">
            <div class="typora-label">
                <label for="mode">
                    <?php echo esc_html__('编辑模式', 'typora-md-vditor'); ?>
                </label>
            </div>
            <div class="typora-input">
                <select id="mode" name="<?php echo esc_attr(OPTION_NAME); ?>[mode]">
                    <?php foreach ($vditor_meta['modes'] as $id => $name) : ?>
                        <option value="<?php echo esc_attr($id); ?>"
                            <?php selected($vidtor_opts['mode'], $id); ?>>
                            <?php echo esc_html($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">
                    <?php echo esc_html__('sv：源码模式；ir：即时渲染模式；wysiwyg：所见即所得模式。', 'typora-md-vditor'); ?>
                </p>
            </div>
        </div>

        <div class="typora-field-row">
            <div class="typora-label">
                <label for="theme">
                    <?php echo esc_html__('编辑器主题', 'typora-md-vditor'); ?>
                </label>
            </div>
            <div class="typora-input">
                <select id="theme" name="<?php echo esc_attr(OPTION_NAME); ?>[theme]">
                    <?php foreach ($vditor_meta['themes'] as $id => $name) : ?>
                        <option value="<?php echo esc_attr($id); ?>"
                            <?php selected($vidtor_opts['theme'], $id); ?>>
                            <?php echo esc_html($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

    </div>
</div>