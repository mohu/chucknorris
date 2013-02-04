$(document).ready(function() {

    // Editor
    $('form .textarea').wysihtml5({
        "html": true
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
        $(e.target).prev('.accordion-heading').find('.accordion-toggle i').animate({  borderSpacing: 45 }, {
            step: function(now, fx) {
                $(this).css('-webkit-transform', 'rotate(' + now + 'deg)');
                $(this).css('-moz-transform', 'rotate(' + now + 'deg)');
                $(this).css('transform', 'rotate(' + now + 'deg)');
            },
            duration:'slow'
        },'linear');
    });

    $('.accordion').on('hide', function (e) {
        $(e.target).prev('.accordion-heading').find('.accordion-toggle i').animate({  borderSpacing: 0 }, {
            step: function(now, fx) {
                $(this).css('-webkit-transform', 'rotate(' + now + 'deg)');
                $(this).css('-moz-transform', 'rotate(' + now + 'deg)');
                $(this).css('transform', 'rotate(' + now + 'deg)');
            },
            duration:'slow'
        },'linear');
    });

    }
)