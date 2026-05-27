<?php
/**
 * 插件帮助函数类
 * 主要用来提供一些通用的功能, 如导入文件, 获取所有可编辑的帖子类型等
 * @author soroy <skiss.cc@gmail.com>
 */

declare(strict_types=1);
namespace TyporaMdVditor;
defined('ABSPATH') || exit();

class Helpers
{
    /**
     * 导入文件
     * @param string $path 文件路径
     * @param bool $once 是否只包含一次
     * @return void
     */
    static public function require(string $path, $once = true): void
    {
        $path = wp_normalize_path($path);
        if (file_exists($path) === false) {
            return;
        }
        if ($once) {
            require($path);
            return;
        }
        require_once($path);
    }

    /**
     * 获取所有可编辑的帖子类型
     * @return array
     * */
    static public function optional_post_types(): array
    {
        $types = get_post_types(['public' => true], 'objects');
        unset($types['attachment']);
        $result = [];
        foreach ($types as $type) {
            $result[$type->name] = $type->label;
        }
        return $result;
    }

    /**
     * 当前文章类型是否支持使用 VDitor 编辑器
     * @param string $post_type 文章类型
     * @return bool
     */
    static public function is_supported_post_type(string $post_type): bool
    {
        if (!$post_type) {
            return false;
        }
        // 获取所有支持的文章类型 数据库
        $admin = Typora_Config::get_plugin_options('admin');
        $types = $admin['post_types'] ?? [];
        return in_array($post_type, $types, true);
    }

    /**
     * 安全处理文件名
     *
     * - 保留中文
     * - 保留扩展名
     * - 移除非法字符
     * - 避免 Markdown/URL 问题
     * - 避免 Windows/Linux 非法文件名
     *
     * @param string $name 文件名
     * @return string
     */
    static public function safe_file_name(string $name): string
    {
        // 去除路径
        $name      = basename($name);
        // 分离文件名和扩展名
        $info      = pathinfo($name);
        // 获取文件名
        $filename  = $info['filename'] ?? 'file';
        // 获取扩展名
        $extension = isset($info['extension']) ? '.' . strtolower($info['extension']) : '';
        // 移除 Markdown / URL / Windows 非法字符
        $filename  = preg_replace('/[\\\\\/:*?"<>|#\[\]\(\)\{\}`!$&\'=;+,%@~]/u', '', $filename);
        // 空格转 -
        $filename  = preg_replace('/\s+/u', '-', $filename);
        // 只允许：中文、英文、数字、点、下划线、横线
        $filename  = preg_replace('/[^\p{Han}a-zA-Z0-9._-]/u', '', $filename);
        // 合并多个 -
        $filename  = preg_replace('/-+/', '-', $filename);
        // 去掉开头结尾特殊字符
        $filename  = trim($filename, '.-_');
        // 防止空文件名
        if ($filename === '') {
            $filename = 'file';
        }
        // 限制长度
        $filename = mb_substr($filename, 0, 100);
        return $filename . $extension;
    }

    /**
     * 验证上传文件
     *
     * @param array $file $_FILES['file']
     * @return array
     * @throws \RuntimeException
     */
    static public function validate_file_type(array $file): array
    {
        //1. 检查文件是否有效
        if (empty($file['tmp_name']) || empty($file['name'])) {
            throw new \RuntimeException(__('文件数据无效', 'typora-md-vditor'));
        }
        //2. 检查文件类型是否支持
        $supported_type_list = Typora_Config::supported_file_types();
        if (empty($supported_type_list)) {
            throw new \RuntimeException(__('禁止上传文件', 'typora-md-vditor'));
        }
        //3. 获取文件信息
        $file_info = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        $ext  = strtolower((string) ($file_info['ext'] ?? ''));
        $mime = strtolower((string) ($file_info['type'] ?? ''));
        //4. 检查扩展名是否支持
        if (!$ext || !isset($supported_type_list[$ext])) {
            throw new \RuntimeException(__('不支持的文件类型', 'typora-md-vditor'));
        }
        //5. 检查 MIME 是否在允许列表中
        $allowed_mimes = $supported_type_list[$ext] ?? [];
        if (in_array($mime, $allowed_mimes, true) === false) {
            throw new \RuntimeException(__('文件 MIME 类型非法', 'typora-md-vditor'));
        }
        //6. 真实 MIME 校验
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            throw new \RuntimeException(__('服务端 MIME 检测失败', 'typora-md-vditor'));
        }
        //7. 获取真实 MIME
        $real_mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (in_array($real_mime, $allowed_mimes, true) === false) {
            throw new \RuntimeException(__('文件内容与扩展名不匹配', 'typora-md-vditor'));
        }

        return ['ext'  => $ext, 'type' => $real_mime, ];
    }

}
