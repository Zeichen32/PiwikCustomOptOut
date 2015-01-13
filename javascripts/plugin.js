/*!
 * CustomOptOut Plugin
 */

$(document).ready(function () {

    $(".custom-opt-out-use-placeholder").on('click', function() {
        var content = $(this).parent().find('textarea').attr('placeholder') || '';
        $(this).parent().find('textarea').val(content);
        return false;
    });

    // Check for CodeMirror
    if(typeof CodeMirror == "undefined") {
        return;
    }

    $("textarea.codemirror-textarea").each(function() {

        var theme = $(this).attr('data-codemirror-theme');

        CodeMirror.fromTextArea(this, {
            mode : 'css',
            lineNumbers: true,
            gutters: ["CodeMirror-lint-markers"],
            theme: (theme == "default" ? "default" : "blackboard"),
            lint: true
        });
    });
});
