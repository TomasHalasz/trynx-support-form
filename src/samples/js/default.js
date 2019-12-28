var halasz = halasz || {};

halasz.SupportForm = halasz.SupportForm || {};

var axleFF = halasz.SupportForm;

axleFF.showFileNames = function (files) {
    var fileNames = '';
    
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

axleFF.hideFlashes = function () {
    $('.halasz-support-form-flashes').slideUp('slow');
};

axleFF.init = function () {

    $(document).on('click','#halasz_feedback_form_files_selector', function(e) {
        $('#halasz_feedback_form_files').trigger('click');
    });
    
    $(document).on('click','.halasz_file_selector', function(e) {
        $('#halasz_feedback_form_files').trigger('click');
    });
    
    $(document).on('change','#halasz_feedback_form_files', function(e) {
        axleFF.showFileNames(e.target.files);
    });
    
    axleFF.showFileNames($('#halasz_feedback_form_files').prop('files'));
    axleFF.showThumbnail();
    
    $(document).on('click','#btn-halasz-inform', function(e) {
        $(".modal-backdrop").attr('data-html2canvas-ignore', 'true');
        html2canvas(
            document.body,
            {
                scale: 1
            }
        ).then(function (canvas) {
            var image = $('#halasz_screenshot');
            var canvasData = canvas.toDataURL();

            image.attr('src', canvasData);

            $('#halasz_feedback_form_image').val(canvasData);
        });
    });
    
    $( document ).ajaxComplete(function() {
        setTimeout(axleFF.hideFlashes, 3000);
    });
};





