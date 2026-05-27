"use strict";

/**
 * Typora MD Vditor - 文件上传
 */
export default class WpVditorUpload {
	/**
	 * 构造函数
	 * @param {Object} configs
	 * @param {string[]} [configs.file_ext_list=[]]
	 * @param {number} [configs.head_size=0]
	 * @param {number} [configs.tail_size=0]
	 * @param {number} [configs.max_file_size=Infinity]
	 * @param {string} [configs.nonce='']
	 * @param {string} [configs.upload_url='']
	 */
	constructor(configs = {}) {
		const { file_ext_list = [], head_size = 0, tail_size = 0, max_file_size = Infinity, nonce = "", upload_url = "" } = configs;
		this.fileExtList   = file_ext_list;
		this.headSize      = head_size;
		this.tailSize      = tail_size;
		this.fileSizeLimit = max_file_size;
		this.nonce         = nonce;
		this.url           = upload_url;
        this.multiple      = false;
		this.accept        = this.fileExtList.map((ext) => `.${ext}`).join(",");
	}

	/**
	 * ArrayBuffer / Uint8Array → SHA-256 hex
	 * @param {ArrayBuffer|Uint8Array} input
	 * @returns {Promise<string>}
	 */
	async hashBuffer(input) {
		if (!(input instanceof ArrayBuffer || input instanceof Uint8Array)) {
			throw new TypeError("hashBuffer: invalid input");
		}

		const buffer = input instanceof ArrayBuffer ? input : input.slice().buffer;
		const hashBuffer = await crypto.subtle.digest("SHA-256", buffer);
		const arr = new Uint8Array(hashBuffer);

		return Array.from(arr, (b) => b.toString(16).padStart(2, "0")).join("");
	}

	/**
	 * 计算文件哈希
	 * @param {File} file
	 * @returns {Promise<string|false>}
	 */
	async computeHash(file) {
		try {
			// 小文件
			if (file.size <= this.headSize) {
				const buffer = await file.arrayBuffer();
				return await this.hashBuffer(buffer);
			}
			// 大文件
			const headBlob = file.slice(0, this.headSize);
			const tailBlob = file.slice(-this.tailSize);
			// 获取对应的文件分部 arrayBuffer
			const [headBuffer, tailBuffer] = await Promise.all([headBlob.arrayBuffer(), tailBlob.arrayBuffer()]);
			// 转成 Uint8Array
			const head = new Uint8Array(headBuffer);
			const tail = new Uint8Array(tailBuffer);
			// 
			const meta = new Uint8Array(4);
			new DataView(meta.buffer).setUint32(0, file.size, true);

			const combined = new Uint8Array(headBuffer.byteLength + tailBuffer.byteLength + meta.byteLength);

			combined.set(head, 0);
			combined.set(tail, headBuffer.byteLength);
			combined.set(meta, headBuffer.byteLength + tailBuffer.byteLength);

			return await this.hashBuffer(combined);
		} catch (err) {
			console.warn("Hash failed:", err);
			return false;
		}
	}

	/**
	 * 校验文件
	 * @param {File[]|FileList} files
	 * @returns {true|string}
	 */
	verifyFile(files) {
		if (!files?.length) {
			return "No file selected.";
		}

		for (const file of files) {
			const ext = file.name.split(".").pop().toLowerCase();
			if (!this.fileExtList.includes(ext)) {
				return `File type not allowed: ${file.name}`;
			}

			if (file.size > this.fileSizeLimit) {
				return `File size exceeds limit: ${file.name}`;
			}
		}

		return true;
	}

	/**
	 * 上传处理入口
	 * @param {File[]|FileList} files
	 * @returns {Promise<string|null>}
	 */
	handler = async (files) => {
        // 验证文件
		const verifyResult = this.verifyFile(files);
		if (verifyResult !== true) {
			return verifyResult;
		}
        // 获取 filehash
		const file = files[0];
		const fileHash = await this.computeHash(file);
        // 
		if (fileHash === false) {
			return "Failed to retrieve file.";
		}
        // 秒传
		const instantResult = await this.instantUpload(fileHash);
		if (instantResult === 200) {
			return null;
		}
        // 上传文件
		const uploadResult = await this.upload(file, fileHash);

		if (uploadResult.success) {
			this.markdownInsert(uploadResult.data.name, uploadResult.data.url);
			return null;
		}
		return uploadResult.data?.message || "Upload failed";
	};

	/**
	 * 执行文件上传
	 * @param {File} file
	 * @param {string} fileHash
	 * @returns {Promise<Object>}
	 */
	async upload(file, fileHash) {
		const formData = new FormData();
		formData.append("action", "typora_md_vditor_upload");
		formData.append("file", file);
		formData.append("nonce", this.nonce);
		formData.append("fileHash", fileHash);

		try {
			const response = await fetch(this.url, {
				method: "POST",
				body: formData,
			});
			return await response.json();
		} catch (err) {
			return {
				success: false,
				data: {
					message: err.message || "Network Error",
				},
			};
		}
	}

	/**
	 * 插入 Markdown
	 * @param {string} name
	 * @param {string} url
	 * @returns {null}
	 */
	markdownInsert(name, url) {
		const ext = url.split(".").pop().toLowerCase();
		const imageExts = ["apng", "bmp", "gif", "ico", "cur", "jpg", "jpeg", "jfif", "pjp", "pjpeg", "png", "svg", "webp"];

		const md = imageExts.includes(ext) ? `![${name}](${url})` : `[${name}](${url})`;

		document.execCommand("insertHTML", false, md);
		return null;
	}

	/**
	 * 秒传检测
	 * @param {string} fileHash
	 * @returns {Promise<number|false>}
	 */
	async instantUpload(fileHash) {
		if (!fileHash) {
			return false;
		}
        // 构建参数
		const params = new URLSearchParams({
			nonce: this.nonce,
			action: "typora_md_instant_upload",
			fileHash,
		});
        // 发送请求
		const response = await fetch(this.url, { 
            method: "POST", 
            body: params 
        });
        // 获取结果
		const result = await response.json();
        // 
		if (result.success && result.data?.code === 200) {
			this.markdownInsert(result.data.name, result.data.url);
			return result.data.code;
		}
		return result.data?.code ?? false;
	}
}
