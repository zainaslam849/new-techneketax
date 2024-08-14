
FilePond.registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
    //   FilePondPluginImageEdit
);

// Select the file input and use
// create() to turn it into a pond

var pond = FilePond.create(

    document.querySelector('.filepond'),
    {
        imagePreviewHeight: 170,
        imageCropAspectRatio: '1:1',
        imageResizeTargetWidth: 200,
        imageResizeTargetHeight: 200,
        stylePanelLayout: 'compact circle',
        styleLoadIndicatorPosition: 'center bottom',
        styleProgressIndicatorPosition: 'right bottom',
        styleButtonRemoveItemPosition: 'left bottom',
        styleButtonProcessItemPosition: 'right bottom',
        files: [
            {
                // the server file reference
                source: '../uploads/profile/'+profile_image,

                // set type to limbo to tell FilePond this is a temp file
                options: {
                    type: 'image/png',
                },
            },
        ],
    }
);

var pond1 = FilePond.create(
    document.querySelector('.filepond1'),
    {
        imagePreviewHeight: 170,
        imageCropAspectRatio: '1:1',
        imageResizeTargetWidth: 200,
        imageResizeTargetHeight: 200,
        stylePanelLayout: 'compact circle',
        styleLoadIndicatorPosition: 'center bottom',
        styleProgressIndicatorPosition: 'right bottom',
        styleButtonRemoveItemPosition: 'left bottom',
        styleButtonProcessItemPosition: 'right bottom',
        files: [
            {
                source: '../uploads/profile/'+company_image,
                options: {
                    type: 'image/png',
                },
            },
        ],
    }
);


