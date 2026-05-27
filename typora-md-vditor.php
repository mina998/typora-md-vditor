<?php
/**
 * Plugin Name: Typora MD Vditor
 * Plugin URI: https://github.com/mina998/typora-md-vditor
 * Description: WordPress Markdown 编辑器, 基于Vditor, 类似Typora的编辑体验
 * Version: 1.0.0
 * Author: soroy
 * Author URI: https://vyi.me
 * Text Domain: typora-md-vditor
 * Domain Path: /languages
 * Requires at least: 6.7
 * Requires PHP: 8.0
 */

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();

// 插件版本
const VERSION = '1.0.0';
const OPTION_NAME = 'typora_md_vditor';
const LOCALIZE_SCRIPT_NAME = __NAMESPACE__;
const VDITOR_NONCE_NAME = 'typora_md_vditor_nonce';
const PLUGIN_DIR  = __DIR__;
const PLUGIN_FILE = __FILE__;
/**
 * 自动加载类
 */
spl_autoload_register(function(string $class) {
    // 如果不是当前命名空间的类，直接返回
    if (str_starts_with($class, __NAMESPACE__) === false) {
        return;
    }
    // 前缀长度
    $prefix_len = strlen(__NAMESPACE__ . DIRECTORY_SEPARATOR);
    // 规范文件路径
    $class_name = substr($class, $prefix_len);
    $class_name = str_replace('_', '-', $class_name);
    $class_path = PLUGIN_DIR . '/includes/' . strtolower($class_name) . '.php';
    $class_path = wp_normalize_path($class_path);
    if (file_exists($class_path)) {
        require $class_path;
    }
});

/**
 * 插件初始化
 */
add_action('init', function () {
    // 加载多语言
    load_plugin_textdomain('typora-md-vditor', false, basename(PLUGIN_DIR) . '/languages');
    // 管理后台功能
    if (is_admin()) {
        new Admin_Menu();
        new Editor_Render();
        new Save_Post();
        new File_Upload();
    }
});
