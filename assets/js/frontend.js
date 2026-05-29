"use script";

document.addEventListener("DOMContentLoaded", function () {
    var container = document.getElementById('typora-md-render');
    if (!container || typeof window._typoraMdData === 'undefined') return;
    if (typeof Vditor === 'undefined') return;

    Vditor.preview(
        container,
        window._typoraMdData,
        {
            cdn: 'https://cdn.vyi.me/vditor/3.11.2',
            hljs: {
                lineNumber: true,
                renderMenu: function (codeElement, copyDiv) {
                    var m = codeElement.className.match(/language-(\S+)/);
                    if (!m) return;
                    var label = document.createElement('span');
                    label.className = 'vditor-lang';
                    label.textContent = m[1];
                    copyDiv.insertAdjacentElement('afterbegin', label);
                },
            },
        }
    );
});
