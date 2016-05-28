WPJB.upload.dropbox = {

    uniqueid: function () {
      // Math.random should be unique because of its seeding algorithm.
      // Convert it to base 36 (numbers + letters), and grab the first 9 characters
      // after the decimal.
      return 'ufd_' + Math.random().toString(36).substr(2, 9);
    },

    click: function(e) {
        e.preventDefault();

        Dropbox.choose({
            success: jQuery.proxy(WPJB.upload.dropbox.success, this),
            cancel: WPJB.upload.dropbox.cancel,
            linkType: "direct",
            multiselect: false,
            extensions: []
        });
    },

    success: function(result) {

        var container = jQuery(this).closest(".wpjb-upload").attr("id");

        var data = {
            action: "upload_from_dropbox",
            form: null,
            object: null,
            field: null,
            id: null,
            data: result[0]
        };

        var file = {
            id: WPJB.upload.dropbox.uniqueid(),
            name: result[0].name
        };

        jQuery("#"+container+" .wpjb-uploads").append(WPJB.upload.progress(file));
        jQuery("#" + file.id + " span.wpjb-upload-progress-bar-inner").css("width", "50%");

        jQuery.each(WPJB.upload.instance, function(index, item) {
            if(item.settings.container == container) {
                data.form = item.settings.multipart_params.form;
                data.object = item.settings.multipart_params.object;
                data.field = item.settings.multipart_params.field;
                data.id = item.settings.multipart_params.id;
            }
        });

        jQuery.ajax({
            url: WPJB.upload.ajaxurl,
            context: file,
            type: "post",
            dataType: "json",
            data: data,
            success: WPJB.upload.dropbox.uploaded
       });

    },

    cancel: function() {

    },

    uploaded: function(result) {

        if(result.result < 1) {
            WPJB.upload.error(jQuery("#"+this.id), result.msg);
        } else {
            jQuery("#"+this.id).replaceWith(WPJB.upload.addFile(result));
        }

    }

}

jQuery(function($) {
    $(".wpjb-upload-dropbox").click(WPJB.upload.dropbox.click);
});
