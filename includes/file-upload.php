<?php
/**
 * 文件上传处理
 * @author soroy <skiss.cc@gmail.com>
 */

declare(strict_types=1);
namespace TyporaMdVditor;
defined('ABSPATH') || exit();

class File_Upload
{
    private int $max_file_size;
    private int $head_size;
    private int $tail_size;

    public function __construct()
    {
        $general             = Typora_Config::get_plugin_options('general');
        $this->max_file_size = (int)($general['max_file_size']);
        $this->head_size     = (int)($general['head_size']);
        $this->tail_size     = (int)($general['tail_size']);

        add_action('wp_ajax_typora_md_vditor_upload', [$this, 'handle_upload']);
        add_action('wp_ajax_typora_md_instant_upload', [$this, 'instant_upload']);
        // 重命名文件
        add_filter('wp_handle_upload_prefilter', function ($file) {
            $file['name'] = Helpers::safe_file_name($file['name']);
            return $file;
        });
    }

    /**
     * 处理秒传
     */
    public function instant_upload(): void
    {
        $this->security_check();
        $hash = isset($_POST['fileHash']) ? $_POST['fileHash'] : '';
        $info = $this->find_file_by_hash($hash);
        if ($info !== null) {
            wp_send_json_success($info, 200);
        }
        wp_send_json_success(['code' => 201], 201);
    }

    /**
     * 处理文件上传
     */
    public function handle_upload(): void
    {
        //1. 安全检查
        $this->security_check();
        //2. 文件尺寸限制
        if ($_FILES['file']['size'] > $this->max_file_size) {
            wp_send_json_error(['message' => __('文件大小超过限制', 'typora-md-vditor')], 400);
        }
        //3. 验证文件类型
        try {
            Helpers::validate_file_type($_FILES['file']);
        } catch (\Throwable $th) {
            wp_send_json_error(['message' => $th->getMessage()], 400);
        }
        //4. 计算文件哈希
        $hash = $this->compute_file_hash($_FILES['file']);
        //5. 根据hash值查找文件
        $info = $this->find_file_by_hash($hash);
        if ($info !== null) {
            wp_send_json_success($info, 200);
        }
        //6. 没找到文件 就上传文件
        $attachment_id = media_handle_upload('file', 0, ['post_content' => $this->hash_to_md5($hash)]);
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => $attachment_id->get_error_message()], 500);
        }
        //7. 返回文件件信息
        $attachment = get_post($attachment_id);
        wp_send_json_success([
            'url'  => wp_get_attachment_url($attachment_id),
            'name' => $attachment->post_title,
            'code' => 200,
        ], 200);
    }

    /**
     * 使用与前端的 computeHash 相同的算法计算文件 SHA-256
     * 
     * @param array $file 上传的文件信息
     * @return string SHA-256 hash
     */
    private function compute_file_hash(array $file): string
    {
        $file_size = (int)$file['size'];                    // 文件大小
        $file_path = $file['tmp_name'];                     // 临时文件路径
        if ($file_size <= $this->head_size) {
            return hash_file('sha256', $file_path);         // 文件小于等于{$this->head_size}, 直接计算完整文件hash
        }
        $head       = file_get_contents($file_path, false, null, 0, $this->head_size);  // 读取文件前 {$this->head_size} 字节
        $tail       = file_get_contents($file_path, false, null, max(0, $file_size - $this->tail_size), $this->tail_size); // 读取文件后 {$this->tail_size} 字节
        $size_bytes = pack('V', $file_size); // 文件大小4字节小端序
        return hash('sha256', $head . $tail . $size_bytes); // 计算组合后的hash
    }

    /**
     * 安全检查
     * @return void
     */
    private function security_check()
    {
        if (current_user_can('upload_files') === false) {
            wp_send_json_error(['message' => __('没有上传权限', 'typora-md-vditor')], 403);
        }
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (wp_verify_nonce($nonce, VDITOR_NONCE_NAME) === false) {
            wp_send_json_error(['message' => __('安全验证失败', 'typora-md-vditor')], 403);
        }
    }

    /**
     * 通过 hash 查找附件
     * 
     * @param string $hash 文件 hash
     * @return array|null 附件元数据，如果未找到返回 null
     */
    private function find_file_by_hash(string $hash): ?array
    {
        global $wpdb;

        $hash = $this->hash_to_md5($hash);
        $sql  = 'select ID, post_content, guid, post_title from ' . $wpdb->posts . ' where post_type = "attachment" and post_content = "%s"';
        $sql  = $wpdb->prepare($sql, $hash);
        $row  = $wpdb->get_row($sql, ARRAY_A);
        if ($row) {
            $row['meta'] = wp_get_attachment_metadata($row['ID']);
            return [
                'url'  => $row['guid'],
                'name' => $row['post_title'],
                'code' => 200
            ];
        }
        return null;
    }

    /**
     * 将字符串转换为 MD5 哈希
     * 
     * @param string $hash 输入字符串
     * @return string MD5 哈希值
     */
    private function hash_to_md5(string $hash): string
    {
        return md5($hash);
    }
}
