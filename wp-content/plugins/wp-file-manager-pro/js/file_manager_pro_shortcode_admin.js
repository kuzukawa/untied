jQuery(document).ready(function () {
  var selectedFilesArr = {};
  var filepaths = [];
  var filesH = fmparams.fmkey;
  var ajax_url = fmparams.ajaxurl;
  var fm_url = ajax_url.indexOf('?') > -1 ? ajax_url+"&" : ajax_url+"?";
  var shortcode_fm = jQuery("#wp_file_manager")
    .elfinder({
      url: fm_url+"action=mk_file_folder_manager&is_type=sc",
      uploadMaxChunkSize: fmparams.uploadMaxChunkSize,

      lang: fmparams.lang,
      defaultView: fmparams.view,
      uiOptions: {
        toolbar: fmparams.hide_toolbar ? [] : {},
        toolbarExtra : {
          preferenceInContextmenu: false,
        },
      },

      contextmenu: {
        files: fmparams.hide_context_menu ? [] : {},
        navbar: fmparams.hide_context_menu ? [] : {},
        cwd: fmparams.hide_context_menu ? [] : {},
      },

      handlers: {
        dblclick: function(event, elfinderInstance)
        {
          if(fmparams.disable_download_dcl == 'yes'){
            var fileData = elfinderInstance.file(event.data.file);
            if(fileData.mime != "directory"){
              return false;
            }
          }
        },
        select : function(event, elfinderInstance) {
          var selected = event.data.selected;
          if(selected.length > 0){
            for (i in selected) {
              var file = elfinderInstance.file(selected[i]);
              if(typeof(file) !== 'undefined') {
                 selectedFilesArr[file.name] = elfinderInstance.url(selected[i]);
              }
            }
          }
        },
        /* Upload */
        upload: function (event, instance) {
          if (fmparams.allow_upload_notifications == "yes") {
            var filepaths = [];
            var fileNames = [];
            var uploadedFiles = event.data.added;
            for (i in uploadedFiles) {
              var file = uploadedFiles[i];
              filepaths.push(btoa(file.url)+'-m-'+filesH);
              fileNames.push(file.name);
            }
            if (filepaths != "") {
              var data = {
                action: "mk_file_folder_manager_uc",
                uploadedby: fmparams.userID,
                uploadefiles: filepaths,
                uploadedFilesNames: fileNames,
                uploadNonce : fmparams.uploadNonce
              };
              jQuery.post(fmparams.ajaxurl, data, function (response) {});
            }
          }
        },

        /* Download */
        download: function (event, elfinderInstance) {
          if (fmparams.allow_download_notifications == "yes") {
            var downloadFiles = {};
            var downloadfiles = event.data.files;
            for (i in downloadfiles) {
              var filenames = downloadfiles[i];
              downloadFiles[filenames.name] = selectedFilesArr[filenames.name] ? btoa(selectedFilesArr[filenames.name])+'-m-'+filesH : '';
            }
            if (downloadFiles != "") {
              var data = {
                action: "mk_file_folder_manager_dc",
                downloadedby: fmparams.userID,
                downloadedFiles: JSON.stringify(downloadFiles),
                downloadNonce : fmparams.downloadNonce
              };
              jQuery.post(fmparams.ajaxurl, data, function (response) {});
            }
          }
        },
      },

      /* END */
      commandsOptions: {
        edit: {
          mimes: [],

          editors: [
            {
              mimes: [
                "text/plain",
                "text/html",
                "text/javascript",
                "text/css",
                "text/x-php",
                "application/x-php",
              ],

              load: function (textarea) {
                var mimeType = this.file.mime;
                return CodeMirror.fromTextArea(textarea, {
                  mode: mimeType,
                  indentUnit: 4,
                  lineNumbers: true,
                  theme: fmparams.code_editor_theme,
                  viewportMargin: Infinity,
                  lineWrapping: true,
                });
              },

              close: function (textarea, instance) {
                this.myCodeMirror = null;
              },

              save: function (textarea, editor) {
                jQuery(textarea).val(editor.getValue());
                /* Start */
                if (fmparams.allow_edit_notifications == "yes") {
                  var data = {
                    action: "mk_file_folder_manager_fn",
                    editedby: fmparams.userID,
                    file: this.file.name,
                    filePath: selectedFilesArr[this.file.name] ? btoa(selectedFilesArr[this.file.name])+'-m-'+filesH : '',
                    editNonce: fmparams.editNonce
                  };
                  jQuery.post(fmparams.ajaxurl, data, function (response) {});
                }
                /* End */
              },
            },
          ],
        },
        quicklook: {
          sharecadMimes: [
            "image/vnd.dwg",
            "image/vnd.dxf",
            "model/vnd.dwf",
            "application/vnd.hp-hpgl",
            "application/plt",
            "application/step",
            "model/iges",
            "application/vnd.ms-pki.stl",
            "application/sat",
            "image/cgm",
            "application/x-msmetafile",
          ],
          googleDocsMimes: [
            "application/pdf",
            "image/tiff",
            "application/vnd.ms-office",
            "application/msword",
            "application/vnd.ms-word",
            "application/vnd.ms-excel",
            "application/vnd.ms-powerpoint",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation",
            "application/postscript",
            "application/rtf",
          ],
          officeOnlineMimes: [
            "application/vnd.ms-office",
            "application/msword",
            "application/vnd.ms-word",
            "application/vnd.ms-excel",
            "application/vnd.ms-powerpoint",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation",
            "application/vnd.oasis.opendocument.text",
            "application/vnd.oasis.opendocument.spreadsheet",
            "application/vnd.oasis.opendocument.presentation",
          ],
        },
      },
    })
    .elfinder("instance");

    if(fmparams.openFullwidth == 'yes'){
      shortcode_fm.bind('dialogopened', function(e) {
        var dialog = e.data.dialog;
        if (dialog.hasClass('elfinder-dialog-edit')) {
          dialog.find('.elfinder-titlebar-button.elfinder-titlebar-full').trigger('mousedown');
        }
      });
    }
  // mac fix
  if (navigator.userAgent.indexOf("Mac OS X") != -1) {
    jQuery("body").addClass("mac");
  } else {
    jQuery("body").addClass("windows");
  }
});
