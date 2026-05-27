<?php

/**
 * 插件管理后台菜单功能
 * 
 * @author soroy <skiss.cc@gmail.com>
 */

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();

class Admin_Menu
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        // 移除Post Format
        add_action('admin_menu', function () {
            remove_meta_box('formatdiv', 'post', 'side');
        });
    }

    /**
     * 添加菜单
     */
    public function add_menu(): void
    {
        add_options_page(
            __('Typora MD 编辑器设置', 'typora-md-vditor'),
            __('Typora MD', 'typora-md-vditor'),
            'manage_options',
            'typora-md-vditor',
            [$this, 'render_page']
        );
    }

    /**
     * 渲染设置页面
     */
    public function render_page(): void
    {
        if (current_user_can('manage_options') === false) {
            return;
        }
        Helpers::require(PLUGIN_DIR . '/templates/plugin-options.php');
    }
}
