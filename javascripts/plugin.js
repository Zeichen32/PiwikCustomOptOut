/*!
 * CustomOptOut Plugin
 */

$(document).ready(function () {

    // Check for CodeMirror
    if(typeof CodeMirror == "undefined") {
        return;
    }

    $("textarea.codemirror-textarea").each(function() {

        var myCodeMirror = CodeMirror.fromTextArea(this, {
            mode : 'css',
            lineNumbers: true,
            gutters: ["CodeMirror-lint-markers"],
            lint: true
        });
    });
});