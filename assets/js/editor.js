"use strict";

import Vditor from "https://cdn.vyi.me/vditor/3.11.2/dist/index.esm.js";
import WpVditorUpload from "/wp-content/plugins/typora-md-vditor/assets/js/upload.js";
import TurndownService from "https://cdn.vyi.me/turndown/7.2.4/turndown.es.js";

/**
 * Vditor 编辑器配置类
 */
class VditorOptions {
	/**
	 * 构造函数
	 * @param {Object} init 初始化参数
	 * @param {Object} opts 编辑器配置项
	 */
	constructor(init = {}, opts = {}) {
		Object.assign(this, opts);
        // Html to Markdown
		const turndown = new TurndownService();

		this.cdn       = "https://cdn.vyi.me/vditor/3.11.2";
		this.upload    = new WpVditorUpload(init);
		this.initOpts  = init;
		this.value     = turndown.turndown(init.post_content ?? "");
		this.minHeight = 400;
		// 编辑器UI主题
        this.theme     = opts.theme;
		this.cache     = { enable: false };
		this.resize    = {
			enable: true,
			position: "bottom",
		};
        // 定义内容主题
		this.preview   = {
			theme: {
				current: opts.theme,
			},
		};
        // 计算输入文本
		this.counter   = {
			enable: true,
			type: "markdown",
		};
		this.customWysiwygToolbar = () => [];
		// 监听输入
        this.input = () => this.syncContent();
	}

	/**
	 * 编辑器渲染完成后的回调
	 * @returns {void}
	 */
	after = () => {
		this.syncContent();
        // 自适应高度
		const applyHeight = () => {
			const height = this.calcVditorHeight();
			const editorEl = document.getElementById(this.initOpts.editor_id);

			if (editorEl) {
				editorEl.style.height = `${height}px`;
			}
		};
        // 监听重置窗口
		addEventListener("resize", applyHeight);
		applyHeight();
	};

	/**
	 * 同步编辑器内容到隐藏字段
	 * @returns {void}
	 */
	syncContent() {
		if (!this.vditor) {
			return;
		}
		if (this.initOpts.hiddenInputHt) {
			this.initOpts.hiddenInputHt.value = this.vditor.getHTML();
		}
		if (this.initOpts.hiddenInputMd) {
			this.initOpts.hiddenInputMd.value = this.vditor.getValue();
		}
	}

	/**
	 * 计算编辑器高度
	 * @returns {number}
	 */
	calcVditorHeight() {
		const windowHeight = window.innerHeight;
		const offset = 300;

		return Math.max(400, windowHeight - offset);
	}
}

/**
 * DOM 加载完成后初始化编辑器
 * @param {Event} event
 */
addEventListener("DOMContentLoaded", (event) => {
	if (typeof TyporaMdVditor === "undefined") {
		console.error("TyporaMdVditor configuration object is not defined.");
		return;
	}

	const { init, opts } = TyporaMdVditor;

	init.hiddenInputHt = document.getElementById("vditor-ht-content");
	init.hiddenInputMd = document.getElementById("vditor-md-content");

	const options  = new VditorOptions(init, opts);
	const vditor   = new Vditor(init.editor_id, options);
	options.vditor = vditor;
});
