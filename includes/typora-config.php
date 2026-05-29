<?php

/**
 * PHP
 * 8.0+ 语法
 * 插件配置类
 * 主要用来设置插件的一些默认参数 保存更新参数到数据库
 */

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();


class Typora_Config
{

    /**
     * 获取所有支持的文件类型
     * @return array
     */
    static private function default_file_mime_all(): array
    {
        $file_type_all = [
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
            'gif'  => ['image/gif'],
            'webp' => ['image/webp'],
            'bmp'  => ['image/bmp'],
            'pdf'  => ['application/pdf'],
            'doc'  => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls'  => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'txt'  => ['text/plain'],
            'csv'  => ['text/csv', 'text/plain'],
            'zip'  => ['application/zip'],
            'mp3'  => ['audio/mpeg'],
            'mp4'  => ['video/mp4'],
        ];
        return $file_type_all;
    }


    /**
     * 获取用户选择的支持的文件类型
     * @return array
     */
    static public function supported_file_types(): array
    {
        // 获取用户选择的文件类型
        $user_selected_file_ext_lsit = self::get_plugin_options('general')['file_ext_list'] ?? [];
        // 转换为键值对数组，键为文件扩展名，值为true
        $user_selected_file_ext_lsit = array_flip($user_selected_file_ext_lsit);
        // 获取所有支持的文件类型
        $all_supported_file_types    = self::default_file_mime_all();
        // 返回用户选择的文件类型
        return array_intersect_key($all_supported_file_types, $user_selected_file_ext_lsit);
    }


    /**
     * 插件设置页面用户可用到的 编辑器所有选项配置参数
     * @param string $key 选项键名
     * @return array
     */
    static public function options_by_all(string $key = ''): array
    {
        // 主题
        $vidtor['themes']  = [
            'dark'          => __('暗黑主题', 'typora-md-vditor'),
            'light'         => __('亮色主题', 'typora-md-vditor'),
        ];
        // 编辑模式
        $vidtor['modes']   = [
            'wysiwyg'       => __('所见即所得', 'typora-md-vditor'),
            'ir'            => __('即时渲染', 'typora-md-vditor'),
            'sv'            => __('分屏预览', 'typora-md-vditor'),
        ];
        // 语言选项
        $vidtor['langs']   = ['de_DE', 'en_US', 'es_ES', 'fr_FR', 'ja_JP', 'ko_KR', 'pt_BR', 'ru_RU', 'sv_SE', 'vi_VN', 'zh_CN', 'zh_TW',];

        // 管理员选项
        $admin['post_types']     = Helpers::optional_post_types();  // 所有文章类型
        $admin['backup_enabled'] = ['yes', 'no'];                   // 启用状态选项

        // 所有允许的文件类型
        $general['file_types'] = self::default_file_mime_all();
        $all = ['vidtor' => $vidtor, 'admin' => $admin, 'general' => $general];
        if ($key) {
            return $all[$key] ?? [];
        }
        return $all;
    }


    /**
     * 保存用户定义的插件选项
     * @param array $options 插件选项数组
     * @return void
     */
    static public function update_plugin_options(array $options): void
    {
        update_option(OPTION_NAME, $options);
    }


    /**
     * 从数据库获取用户保存的插件选项
     * @param string $key 选项键名
     * @return array
     */
    static public function get_plugin_options($key = ''): array
    {
        // 从数据库获取用户保存的插件选项
        $options = get_option(OPTION_NAME, []);
        // 获取默认选项
        $default = self::plugin_default_options();
        // 合并默认选项和用户选项
        $options = array_merge($default, $options);

        if (empty($key)) {
            return $options;
        }
        if (isset($options[$key])) {
            return $options[$key];
        }
        return [];
    }


    /**
     * 插件 默认选项 
     * 如果用户没有设置插件选项 则使用这里的默认参数
     * @return array
     */
    static private function plugin_default_options(): array
    {
        // Vditor 编辑器选项
        $vidtor['theme']   = 'light';   // 默认主题
        $vidtor['mode']    = 'ir';      // 默认编辑模式
        // 默认工具栏项
        $vidtor['toolbar'] = [
            'emoji', 'headings', 'bold', 'italic', 'strike', 'link', '|', 'list', 'ordered-list', 'check', '|', 'quote', 'line', 'code', 'inline-code', '|', 
            'upload', 'table', '|', 'undo', 'redo', '|', 'preview', 'fullscreen', 'outline', 'edit-mode',
        ];

        // 管理员选项
        $admin['post_types']      = ['post'];    // 默认备份选项
        $admin['backup_enabled']  = true;        // 默认备份启用
        $admin['backup_dir']      = 'typora-md'; // 默认备份目录

        // 通用选项
        $general['max_file_size'] = 5 * 1024 * 1024;    // 5MB   - 最大上传文件大小
        $general['head_size']     = 1024 * 1024;        // 1MB   - hash算法 头部截取大小
        $general['tail_size']     = 256 * 1024;         // 256KB - hash算法 尾部截取大小
        $general['file_ext_list'] = ['jpg', 'jpeg', 'png', 'gif', 'webp',]; // 默认选中的文件类型

        return ['vidtor' => $vidtor, 'admin' => $admin, 'general' => $general];
    }
}
