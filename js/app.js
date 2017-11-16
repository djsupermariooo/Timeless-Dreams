$(document).ready(function () {

    //User clicked on Photos link
    $('#photos-link').click(function () {

        //Grab list of images in uploads directory
        $.getJSON('listimages.php', function (data) {
            //If images already exist, do nothing..otherwise append img tags to parent
            if ($('#container img').length > 0) {
                return;
            } else {
                $.each(data, function (i, v) {
                    $('#container').append('<a href="/images/uploads/' + v + '"><img src="/images/uploads/' + v + '"></a>');
                });
            }
        });
    });
});