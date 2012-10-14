$(document).ready(function() {
    $('form .textarea').tinymce({
        // Location of TinyMCE script
        script_url : '/admin/libs/tinymce/jscripts/tiny_mce/tiny_mce.js',

        // Update validation status on change
        onchange_callback: function(editor) {
            tinyMCE.triggerSave();
            $("#" + editor.id).valid();
        },

        // General options
        theme : "advanced",
        skin : "thebigreason",
        plugins : "paste",
        width: "500",
        height: "200",
        force_p_newlines : true,
        force_br_newlines : false,
        paste_create_paragraphs: true,
        theme_advanced_styles : "Paragraph=p;Header 1=h2;Header 2=h3;Header 3=h4",

        // Theme options
        theme_advanced_buttons1 : "styleselect,bold,italic,underline,strikethrough,|,bullist,numlist,|,undo,redo,|,link,unlink,|,code,charmap,fullscreen,|,pastetext,pasteword",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        theme_advanced_source_editor_height: 400,
        theme_advanced_source_editor_width: 500,
        paste_auto_cleanup_on_paste : true,
        paste_preprocess : function(pl, o) {
            // Content string containing the HTML from the clipboard
            // alert(o.content);
            o.content = o.content;
        },
        paste_postprocess : function(pl, o) {
            // Content DOM node containing the DOM structure of the clipboard
            // alert(o.node.innerHTML);
            o.node.innerHTML = o.node.innerHTML;
        }
    });


    $('.credits').on('click', function(e) {
        e.preventDefault();
        $('#credits').modal('show');
    });

    $("li.clear-cache").click(function() {
        var span = $(this).find('span');
        $(this).find('a').html('<i class="icon-trash"></i> Clearing...');
        $(this).find('a').append(span);
        setTimeout(function() {
            $.ajax({
            type: 'POST',
            url: '/admin/',
            data: {clearcache: true},
            success: function() {
                span = $(span).html('0');
                $('li.clear-cache').find('a').html('<i class="icon-trash"></i> Clear cache');
                $('li.clear-cache').find('a').append(span);
            }
            });
        }, 1500);
        return false;
    });

    $('a[href=#top]').click(function(){
        $('html, body').animate({scrollTop:0}, 'slow');
        return false;
    });

    $('.accordion').on('show', function (e) {
        $(e.target).prev('.accordion-heading').find('.accordion-toggle i').removeClass('icon-circle-arrow-down').addClass('icon-circle-arrow-right');
    });

    $('.accordion').on('hide', function (e) {
        $(e.target).prev('.accordion-heading').find('.accordion-toggle i').removeClass('icon-circle-arrow-right').addClass('icon-circle-arrow-down');
    });

    }
)