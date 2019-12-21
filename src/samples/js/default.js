var halasz = halasz || {};

halasz.SupportForm = halasz.SupportForm || {};

var axleFF = halasz.SupportForm;

axleFF.showFileNames = function (files) {
    var fileNames = '';
    console.log(files);
    $.each(files, function(idx,elm){
        if (fileNames !== '') {
            fileNames += ', ';
        }
        fileNames += elm.name;
    });

    $('.halasz_file_selector').val(fileNames);
};

axleFF.showThumbnail = function () {
    var image = $('#halasz_screenshot');

    image.attr('src', $('#halasz_feedback_form_image').val());
};

axleFF.init = function () {
    $('#halasz_feedback_form_files_selector').on('click', function (e) {
        $('#halasz_feedback_form_files').trigger('click');
    });
    
    $('.halasz_file_selector').on('click', function (e) {
        $('#halasz_feedback_form_files').trigger('click');
    });
    
    $('#halasz_feedback_form_files').on('change', function (e) {
        axleFF.showFileNames(e.target.files);
    });
    
    axleFF.showFileNames($('#halasz_feedback_form_files').prop('files'));
    axleFF.showThumbnail();
    
    document.querySelector("#btn-halasz-inform").addEventListener("click", function() {
        $(".modal-backdrop").attr('data-html2canvas-ignore', 'true') ;
        html2canvas(
            document.body, 
            { 
                scale: 1
            }
        ).then(function(canvas) {
            var image = $('#halasz_screenshot');
            var canvasData = canvas.toDataURL();

            image.attr('src', canvasData);

            $('#halasz_feedback_form_image').val(canvasData);
        });
    }, false);
};





