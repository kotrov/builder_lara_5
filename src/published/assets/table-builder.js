'use strict';

var TableBuilder = {

    options: null,
    files: {},
    admin_prefix: '',
    action_url: '',
    table: '#widget-grid',
    preloader: '#table-preloader',
    form_preloader: '.form-preloader',
    form: '#modal_form',
    form_edit: '#modal_form_edit',
    export_form: '#tb-export-form',
    form_label: '#modal_form_edit_label',
    form_wrapper: '#modal_wrapper',
    create_form: '#create_form',
    edit_form: '#edit_form',
    filter: '#filters-row :input',
    is_page_form: false,
    thisFileElementInGroup : null,
    thisPictureElementInGroup : null,

    onDoEdit: null,
    onDoCreate: null,
    onDoDelete: null,
    handlerCreate: null,
    tableEditorImg : null,
    sort : true,

    init: function (options) {
        TableBuilder.options = TableBuilder.getOptions(options);
        TableBuilder.initSelect2Hider();
    },

    optionsInit : function (options) {
        TableBuilder.options = TableBuilder.getOptions(options);
    },

    afterLoadPage : function () {
        $('.selectable').editable();

        $('.tb-sort-me-gently', '.tb-table').on('mousedown', function () {
            $('.widget-body', '.table-builder').css('overflow-x', 'visible');
        });
        $('.tb-sort-me-gently', '.tb-table').on('mouseup', function () {
            $('.widget-body', '.table-builder').css('overflow-x', 'scroll');
        });

        $('tbody', '#datatable_fixed_column').sortable({
            scroll: true,
            axis: "y",
            handle: ".tb-sort",
            update: function () {

                var order = $('tbody', '#datatable_fixed_column').sortable("serialize");
                TableBuilder.saveOrder(order);
            }
        });

    },

    initFroalaEditor: function (table) {

        var langEditor = "en";
        var textBlock = table == undefined ? '.text_block' : '.modal_form_' + table + ' .text_block';

        langEditor =  langCms == "ua" ? 'uk' : langCms;

        $(textBlock).each(function ( index ) {

            var csrfToken = $("meta[name=csrf-token]").attr("content");

            var option =  {
                initOnClick: true,
                inlineMode: false,
                imageUploadURL: '/admin/upload_image?_token=' + csrfToken,
                imageManagerDeleteURL: "/admin/delete_image?_token=" + csrfToken,
                heightMin: 100,
                heightMax: 500,
                fileUploadURL: "/admin/upload_file?_token=" + csrfToken,
                imageManagerLoadURL: "/admin/load_image?_token=" + csrfToken,
                imageDeleteURL: "/admin/delete_image?_token=" + csrfToken,
                language: langEditor,
                imageEditButtons: ['imageReplace', 'imageAlign', 'imageRemove', '|', 'imageLink', 'linkOpen', 'linkEdit', 'linkRemove', '-', 'imageDisplay', 'imageStyle', 'imageAlt', 'imageSize', 'crop'],
            };

            if ($(this).attr("toolbar")) {
                var toolbar = $(this).attr("toolbar");
                var arrayToolbar = toolbar.split(",");
                arrayToolbar = $.map(arrayToolbar, $.trim);
                option.toolbarButtons = arrayToolbar;
                option.toolbarButtonsMD = arrayToolbar;
                option.toolbarButtonsSM = arrayToolbar;
                option.toolbarButtonsXS = arrayToolbar;
            }

            if ($(this).attr("inlinestyles")) {
                option.inlineStyles = JSON.parse($(this).attr("inlinestyles"))
            }

            if ($(this).attr("options")) {
                var optionsConfig = JSON.parse($(this).attr("options"));
                for (var key in optionsConfig) {
                    option[key] = optionsConfig[key];
                }
            }

            $(this).froalaEditor(option);
        });

        $('.text_block').on('froalaEditor.initialized', function (e, editor) {
            $(this).parent().removeClass('no_active_froala');
        });

        $('.group').on('keyup, blur', '[data-multi=multi]', function () {
            TableBuilder.multiInputAction($(this));
        });

        $(".group [data-multi=multi]").each(function (  ) {
            TableBuilder.multiInputAction($(this));
        });

    },

    multiInputAction : function (context) {
        var inputThisBlock = context.parents(".input_content").find("input"),
            arrayData = [],
            hideInput = context.parents('.tabs_section').find("input[type=hidden]");

        $(inputThisBlock).each(function ( index ) {
            arrayData.push($(this).val())
        });

        hideInput.val(JSON.stringify(arrayData));
    },

    getActionUrl: function (content) {
        if (content != undefined) {
            return content.parents('form').attr('action');
        }

        return TableBuilder.action_url ? TableBuilder.action_url : '/admin/tree';
    }, // end getActionUrl

    initSearchOnEnterPressed: function () {
        $(document).on('keypress', '.filters-row input', function (event) {
            var keyCode   = event.keyCode ? event.keyCode : event.which;
            var enterCode = '13';

            if (keyCode == enterCode) {
                TableBuilder.search();
                event.preventDefault();
            }
        });

    }, // end initSearchOnEnterPressed

    getOptions: function (options) {
        var defaultOptions = {
            lang: {},
            ident: null,
            table_ident: null,
            form_ident: null,
            action_url: null,
            list_url: null,
            is_page_form: false,
            onSearchResponse: null,
            onFastEditResponse: null,
            onShowEditFormResponse: null
        };

        var options = jQuery.extend(defaultOptions, options);

        TableBuilder.checkOptions(options);

        return options;
    }, // end getOptions

    checkOptions: function (options) {
        var requiredOptions = [
            'ident',
            'table_ident',
            'form_ident',
            'action_url'
        ];

        jQuery.each(requiredOptions, function (index, value) {
            if (typeof options[value] === null) {
                alert('TableBuilder: [' + value + '] is required option.');
            }
        });
    }, // end checkOptions

    lang: function (ident) {
        if (typeof TableBuilder.options.lang[ident] != "undefined") {
            return TableBuilder.options.lang[ident];
        }

        return ident;
    }, // end lang

    search: function () {
        TableBuilder.showProgressBar();

        var $form = $('form[target=submiter]');
        var data = $form.serializeArray();

        data.push({ name: "query_type", value: "search" });
        data.push({ name: "__node", value: TableBuilder.getUrlParameter('node') });

        /* Because serializeArray() ignores unset checkboxes and radio buttons: */
        data = data.concat(
            $form.find('input[type=checkbox]:not(:checked)')
                .map(function () {
                    return {"name": this.name, "value": 0};
                }).get()
        );

        var url = TableBuilder.getActionUrl();

        var $posting = jQuery.post(url, data);

        $posting.done(function (response) {
            doAjaxLoadContent(location.pathname);
        });

    }, // end search

    showProgressBar: function () {
        jQuery('#' + TableBuilder.options.ident).find('.ui-overlay').fadeIn();
    }, // end showProgressBar

    hideProgressBar: function () {
        jQuery('#' + TableBuilder.options.ident).find('.ui-overlay').fadeOut();
    }, // end hideProgressBar

    showFastEdit : function (thisElement) {
        $(".fast-edit-buttons").hide();
        $(thisElement).parent().find(".fast-edit-buttons").show();
    },

    closeFastEdit: function (context, type, response) {
        var $editElem = $(context).parent().hide();
    }, // end closeFastEdit

    saveFastEdit: function (context, rowId, rowIdent) {
        var $context = $(context).parent().parent();
        var value = $context.find('.dblclick-edit-input').val();
        $("tr[id-row=" + rowId + "] .element_" + rowIdent).text(value);
        TableBuilder.closeFastEdit(context);
        var data = [
            {name: "query_type", value: "fast_save"},
            {name: "id", value: rowId},
            {name: "name", value: rowIdent},
            {name: "value", value: value}
        ];

        jQuery.post(TableBuilder.getActionUrl(), data);
    }, // end saveFastEdit

    activeToggle : function (rowId, rowIdent, isActive) {

        var value =  isActive ? 1 : 0;;

        var data = [
            {name: "query_type", value: "fast_save"},
            {name: "id", value: rowId},
            {name: "name", value: rowIdent},
            {name: "value", value: value}
        ];

        $.post(TableBuilder.getActionUrl(), data);
    },

    getUrlParameter: function (sParam) {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++) {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam) {
                return sParameterName[1];
            }
        }
    }, // end getUrlParameter

    getCreateForm: function () {
        jQuery(TableBuilder.form_edit).remove()
        jQuery(TableBuilder.form).remove();

        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: {"query_type" : "show_add_form"},
            dataType: 'json',
            success: function (data) {
                $(".table_form_create").html(data);
                jQuery(TableBuilder.form).modal('show');
                TableBuilder.initFroalaEditor();
                TableBuilder.hidePreloader();
                TableBuilder.handleActionSelect();

                TableBuilder.refreshMask();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });

    }, // end getCreateForm

    refreshMask : function () {
        jQuery("#modal_form_edit form, #modal_form form").find('input[data-mask]').each(function () {
            var $input = jQuery(this);
            $input.mask($input.attr('data-mask'));
        });
    },

    initSelect2Hider: function () {
        jQuery('.modal-dialog').on('click', function () {
            jQuery('.select2-enabled[id^="many2many"]').select2("close");
            jQuery('.select2-hidden-accessible').hide();
        });

    }, // end initSelect2Hider

    getCloneForm: function (id, context) {
        $.post(TableBuilder.getActionUrl(context), {"query_type" : "clone_record", "id" : id})
            .done(function ( data ) {
                doAjaxLoadContent(location.href);
            }).fail(function (xhr, ajaxOptions, thrownError) {
            var errorResult = jQuery.parseJSON(xhr.responseText);
            TableBuilder.showErrorNotification(errorResult.message);
            TableBuilder.hidePreloader();
        });
    },

    handleStartLoad: function () {
        var idPage = Core.urlParam('id');
        if ($.isNumeric(idPage)) {
            TableBuilder.getEditForm(idPage);
        }

        var idRevisionPage = Core.urlParam('revision_page');
        if ($.isNumeric(idRevisionPage)) {
            TableBuilder.getRevisions(idRevisionPage);
        }
        var idViewsStatistic = Core.urlParam('views_statistic');
        if ($.isNumeric(idViewsStatistic)) {
            TableBuilder.getViewsStatistic(idViewsStatistic);
        }


    }, // end handleStartLoad

    doCustomAction: function (url) {

        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: url,
            data: {},
            dataType: 'json',
            success: function (response) {

                if (response.status === true) {
                    jQuery.smallBox({
                        title : response.message,
                        content : "",
                        color : "#659265",
                        iconSmall : "fa fa-check fa-2x fadeInRight animated",
                        timeout : 4000
                    });
                } else {
                    jQuery.smallBox({
                        title : "Something went wrong, try again later",
                        content : "",
                        color : "#C46A69",
                        iconSmall : "fa fa-times fa-2x fadeInRight animated",
                        timeout : 4000
                    });
                }

                TableBuilder.hidePreloader();
            },
            error: function () {
                jQuery.smallBox({
                    title : "Something went wrong, try again later",
                    content : "",
                    color : "#C46A69",
                    iconSmall : "fa fa-times fa-2x fadeInRight animated",
                    timeout : 4000
                });
            }
        });
    },

    getEditForm: function (id, context) {
        var urlPage = "?id=" + id;
        window.history.pushState(urlPage, '', urlPage);

        TableBuilder.showPreloader();

        jQuery('#wid-id-1').find('tr[data-editing="true"]').removeAttr('data-editing');

        var data = [
            {name: "query_type", value: "show_edit_form"},
            {name: "id", value: id},
            {name: "__node", value: TableBuilder.getUrlParameter('node')}
        ];

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(context),
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    $(TableBuilder.form_wrapper).html(response.html);
                    $(TableBuilder.form_edit).modal('show').css("top", $(window).scrollTop());;

                    TableBuilder.initFroalaEditor();

                    TableBuilder.handleActionSelect();
                } else {
                    TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end getEditForm

    getViewsStatistic : function (id, context) {
        var urlPage = "?views_statistic=" + id;
        window.history.pushState(urlPage, '', urlPage);
        TableBuilder.showPreloader();

        var data = [
            {name: "query_type", value: "show_views_statistic"},
            {name: "id", value: id},
        ];

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl($(context)),
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    jQuery(TableBuilder.form_wrapper).html(response.html);
                    jQuery(TableBuilder.form_edit).modal('show').css("top", $(window).scrollTop());
                } else {
                    TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });


    },

    getRevisions: function (id, context) {
        var urlPage = "?revision_page=" + id;
        window.history.pushState(urlPage, '', urlPage);
        TableBuilder.showPreloader();

        var data = [
            {name: "query_type", value: "show_revisions"},
            {name: "id", value: id},
        ];
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(context),
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    jQuery(TableBuilder.form_wrapper).html(response.html);
                    jQuery(TableBuilder.form_edit).modal('show').css("top", $(window).scrollTop());
                } else {
                    TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    },

    getReturnHistory: function (id) {
        var data = [
            {name: "query_type", value: "return_revisions"},
            {name: "id", value: id},
        ];
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification("Сохранено");

                    jQuery(TableBuilder.form_edit).modal('hide');
                    window.history.back();
                    return;
                } else {
                    TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    },

    doDelete: function (id, context) {
        jQuery.SmartMessageBox({
            title : phrase["Удалить?"],
            content : phrase["Эту операцию нельзя будет отменить."],
            buttons : '[' + phrase["Нет"] + '][' + phrase["Да"] + ']'
        }, function (ButtonPressed) {
            if (ButtonPressed === phrase["Да"]) {
                TableBuilder.showPreloader();

                jQuery.ajax({
                    type: "POST",
                    url: TableBuilder.getActionUrl(context),
                    data: { id: id, query_type: "delete_row", "__node": TableBuilder.getUrlParameter('node') },
                    dataType: 'json',
                    success: function (response) {

                        if (response.status) {
                            TableBuilder.showSuccessNotification(phrase['Поле удалено успешно']);
                            jQuery('tr[id-row="' + id + '"]').remove();
                        } else {
                            TableBuilder.showErrorNotification(phrase["Что-то пошло не так, попробуйте позже"]);
                        }

                        if (TableBuilder.onDoDelete) {
                            TableBuilder.onDoDelete(TableBuilder.getActionUrl());
                        }

                        TableBuilder.hidePreloader();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        var errorResult = jQuery.parseJSON(xhr.responseText);

                        TableBuilder.showErrorNotification(errorResult.message);
                        TableBuilder.hidePreloader();
                    }
                });
            }

        });
    }, // end doDelete

    doEdit: function (id, table, foreign_field_id, foreign_attributes) {
        var form = '#edit_form_' + table;

        TableBuilder.edit_form = form;
        TableBuilder.action_url = $(form).attr('action');

        TableBuilder.showPreloader();
        TableBuilder.showFormPreloader(TableBuilder.form_edit);

        $('.fr-popup').remove();

        var values = $(TableBuilder.edit_form).serializeArray();

        values.push({ name: 'id', value: id });
        values.push({ name: 'query_type', value: "save_edit_form" });
        values.push({ name: "__node", value: TableBuilder.getUrlParameter('node') });

        if ($(".file_multi").size() != 0) {
            TableBuilder.buildFiles();
            for (var key in TableBuilder.files) {
                var filesRes = TableBuilder.files[key];
                var json = JSON.stringify(filesRes);
                values.push({ name: key, value: json });
            }
        }

        /* Because serializeArray() ignores unset checkboxes and radio buttons: */
        values = values.concat(
            jQuery(TableBuilder.edit_form).find('input[type=checkbox]:not(:checked)')
                .map(function () {
                    return {"name": this.name, "value": 0};
                }).get()
        );

        var selectMultiple = [];
        jQuery(TableBuilder.edit_form).find('select[multiple="multiple"]').each(function (i, value) {
            if (!$(this).val()) {
                selectMultiple.push({"name": this.name, "value": ''});
            }
        });

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: values,
            dataType: 'json',
            success: function (response) {

                TableBuilder.hideFormPreloader(TableBuilder.form_edit);

                if (response.id) {
                    if (foreign_field_id != '' && foreign_attributes != '') {
                        ForeignDefinition.callbackForeignDefinition(foreign_field_id, foreign_attributes);
                        TableBuilder.doClosePopup(table);
                        return;
                    }

                    TableBuilder.showSuccessNotification(phrase['Сохранено']);

                    $(document).height($(window).height());

                    if (TableBuilder.options.is_page_form) {
                        window.history.back();
                        TableBuilder.doClosePopup(table);
                        return;
                    }

                    $(TableBuilder.form_edit).modal('hide');
                    $(document).height($(window).height());

                    $('#wid-id-1').find('tr[id-row="' + id + '"]').replaceWith(response.html);

                    if (TableBuilder.onDoEdit) {
                        TableBuilder.onDoEdit(TableBuilder.getActionUrl());
                    }

                    TableBuilder.clearParamsWithUrl();
                    TableBuilder.doClosePopup(table);
                } else {
                    var errors = '';
                    $(response.errors).each(function (key, val) {
                        errors += val + '<br>';
                    });

                    TableBuilder.showBigErrorNotification(errors);
                }
                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = $.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
                TableBuilder.hideFormPreloader(TableBuilder.form_edit);
            }
        });
    }, // end doEdit

    buildFiles : function () {
        $(".file_multi").each(function ( index ) {
            var ident = $(this).attr("nameident") ;
            TableBuilder.files[ident] = {};
        });

        var k = 0;
        $(".file_multi").each(function ( index ) {
            var ident = $(this).attr("nameident");
            TableBuilder.files[ident][k] = $(this).attr("value");
            k++;
        });

    },

    removeInputValues: function (context) {
        jQuery(':input', context)
            .removeAttr('checked')
            .removeAttr('selected')
            .not(':button, :submit, :reset, input[type="hidden"], :radio, :checkbox')
            .val('');
        jQuery('textarea', context).text('');

        jQuery('input, textarea', context).removeClass('valid').removeClass('invalid');
        jQuery('.state-success, .state-error', context).removeClass('state-success').removeClass('state-error');
    }, // end removeInputValues

    doCreate: function (create_form, foreign_field_id, foreign_attributes) {
        TableBuilder.create_form = create_form;
        TableBuilder.action_url = $(create_form).attr('action');

        TableBuilder.showPreloader();
        TableBuilder.showFormPreloader(TableBuilder.form);

        $('.fr-popup').remove();

        var values = jQuery(TableBuilder.create_form).serializeArray();

        values.push({ name: "query_type", value: "save_add_form" });
        values.push({ name: "__node", value: TableBuilder.getUrlParameter('node') });

        // take values from temp storage (for images)

        if ($(".file_multi").size() != 0) {
            TableBuilder.buildFiles();
            for (var key in TableBuilder.files) {
                var filesRes = TableBuilder.files[key];
                var json = JSON.stringify(filesRes);
                values.push({ name: key, value: json });
            }
        }

        var selectMultiple = [];
        jQuery(TableBuilder.create_form).find('select[multiple="multiple"]').each(function (i, value) {
            if (!$(this).val()) {
                selectMultiple.push({"name": this.name, "value": ''});
            }
        });

        values = values.concat(selectMultiple);

        if (TableBuilder.onDoCreate) {
            values = TableBuilder.onDoCreate(values);
        }

        /* Because serializeArray() ignores unset checkboxes and radio buttons: */
        values = values.concat(
            jQuery(TableBuilder.create_form).find('input[type=checkbox]:not(:checked)')
                .map(function () {
                    return {"name": this.name, "value": 0};
                }).get()
        );

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: values,
            dataType: 'json',
            success: function (response) {
                TableBuilder.hideFormPreloader(TableBuilder.form);

                if (response.id) {
                    if (foreign_field_id != '' && foreign_attributes != '') {
                        ForeignDefinition.callbackForeignDefinition(foreign_field_id, foreign_attributes);
                        return;
                    }

                    if (TableBuilder.handlerCreate) {
                        TableBuilder.handlerCreate(TableBuilder.getActionUrl(), response.id);
                        return;
                    }

                    TableBuilder.showSuccessNotification(phrase['Сохранено']);

                    if (TableBuilder.options.is_page_form) {
                        //window.location.href = TableBuilder.options.list_url;
                        window.history.back();
                        return;
                    }

                    var form = $(create_form).parents('#modal_form');

                    TableBuilder.removeInputValues(form);
                    form.modal('hide');

                    if (form.parent().attr('class') == 'foreign_popup') {
                        form.remove();
                    } else {
                        jQuery('#wid-id-1 .widget-body').find('tbody').prepend(response.html);
                    }

                    if (TableBuilder.onDoCreate) {
                        TableBuilder.onDoCreate(TableBuilder.getActionUrl());
                    }
                } else {
                    var errors = '';
                    jQuery(response.errors).each(function (key, val) {
                        errors += val + '<br>';
                    });
                    TableBuilder.showBigErrorNotification(errors);
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
                TableBuilder.hideFormPreloader(TableBuilder.form);
            }

        });
    }, // end doCreate

    showPreloader: function () {
        $(TableBuilder.preloader).show();
    }, // end showPreloader

    hidePreloader: function () {
        $(TableBuilder.preloader).hide();
    }, // end hidePreloader

    showFormPreloader: function (context) {
        jQuery(TableBuilder.form_preloader, context).show();
    }, // end showPreloader

    hideFormPreloader: function (context) {
        $(TableBuilder.form_preloader, context).hide();
    }, // end hidePreloader

    uploadImage: function (context, ident, baseIdent) {
        var data = new FormData();
        data.append("image", context.files[0]);
        data.append('ident', ident);
        data.append('query_type', 'upload_photo');
        data.append('type', "single_photo");
        data.append('baseIdent', baseIdent);


        if (TableBuilder.getUrlParameter('id_tree') != undefined) {
            data.append('page_id', TableBuilder.getUrlParameter('id_tree'));
        }

        if (TableBuilder.getUrlParameter('id') != undefined) {
            data.append('page_id', TableBuilder.getUrlParameter('id'));
        }


        var $progress = $(context).parents('.picture_block').find('.progress-bar');

        jQuery.ajax({
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    console.log(evt);
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        percentComplete = percentComplete * 100;
                        percentComplete = percentComplete + '%';
                        $progress.width(percentComplete);
                    }
                }, false);

                xhr.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                    }
                }, false);

                return xhr;
            },
            data: data,
            type: "POST",
            url: TableBuilder.getActionUrl($(context)),
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.status) {
                    $progress.width('0%');

                    $(context).parents(".picture_block").find('.tb-uploaded-image-container').html(response.html);
                    $(context).parents(".picture_block").find('[type=hidden]').val(response.data.sizes.original);
                } else {
                    TableBuilder.showErrorNotification(phrase["Ошибка при загрузке изображения"]);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end uploadImage

    uploadMultipleImages: function (context, ident, baseIdent) {
        $(".no_photo").hide();
        var arr = context.files;
        for (var index = 0; index < arr.length; ++index) {
            var data = new FormData();
            data.append("image", context.files[index]);
            data.append('ident', ident);
            data.append('baseIdent', baseIdent);

            data.append('query_type', 'upload_photo');
            if (TableBuilder.getUrlParameter('id_tree') != undefined) {
                data.append('page_id', TableBuilder.getUrlParameter('id_tree'));
            }

            if (TableBuilder.getUrlParameter('id') != undefined) {
                data.append('page_id', TableBuilder.getUrlParameter('id'));
            }

            var $progress = jQuery(context).parent().parent().parent().parent().parent().find('.progress-bar');

            jQuery.ajax({
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        console.log(evt);
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = percentComplete * 100;

                            percentComplete = percentComplete + '%';
                            $progress.width(percentComplete);
                        }
                    }, false);

                    xhr.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                        }
                    }, false);

                    return xhr;
                },
                data: data,
                type: "POST",
                url: TableBuilder.getActionUrl($(context)),
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.status) {
                        $progress.width('0%');

                        $(context).parents(".multi_pictures").find(".tb-uploaded-image-container ul").append(response.html);

                        TableBuilder.setInputImages(context);
                    } else {
                        TableBuilder.showErrorNotification(phrase["Ошибка при загрузке изображения"]);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    var errorResult = jQuery.parseJSON(xhr.responseText);

                    TableBuilder.showErrorNotification(errorResult.message);
                    TableBuilder.hidePreloader();
                }
            });
        }
    }, // end uploadMultipleImages

    setInputImages : function (context) {
        var arrImages = new Array();
        $(context).parents('.multi_pictures').find('.tb-uploaded-image-container ul li img').each(function ( index ) {
            arrImages.push($(this).attr("data_src_original"));
        });
        var jsonArray = JSON.stringify(arrImages);

        $(context).parents('.multi_pictures').find("[type=hidden]").val(jsonArray);
    },

    deleteImage: function (context) {
        var contextFile = $(context).parents('.multi_pictures').find("[type=file]");
        var $li = $(context).parent().parent();
        $li.remove();
        TableBuilder.setInputImages(contextFile);
    }, // end deleteImage

    deleteSingleImage: function (ident, context) {
        var $imageWrapper = $(context).parent().parent();
        var $imageInput = $(context).parents('.picture_block').find("input[type=hidden]");
        $imageWrapper.hide();
        $imageInput.val("")

    }, // end deleteSingleImage

    doChangeSortingDirection: function (ident, context) {
        if (!this.sort) {
            return;
        }

        var $context = $(context);

        var isAscDirection = $context.hasClass('sorting_asc');
        var direction = isAscDirection ? 'desc' : 'asc';

        var vals = [
            {name: "query_type", value: "change_direction"},
            {name: "direction", value: direction},
            {name: "field", value: ident},
            {name: "__node", value: TableBuilder.getUrlParameter('node')}
        ];

        jQuery.ajax({
            data: vals,
            type: "POST",
            url: TableBuilder.getActionUrl(),
            cache: false,
            dataType: "json",
            success: function (response) {
                doAjaxLoadContent(window.location.href);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);
                TableBuilder.showErrorNotification(errorResult.message);
            }
        });
    },

    doClearOrder : function () {
        this.sort = false;

        jQuery.ajax({
            data: [
                {name: "query_type", value: "clear_order_by"},
            ],
            type: "POST",
            url: TableBuilder.getActionUrl(),
            cache: false,
            dataType: "json",
            success: function (response) {
                doAjaxLoadContent(window.location.href);
                TableBuilder.sort = true;
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);
                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.sort = true;
            }
        });

    },

    uploadFile: function (context, ident) {
        var data = new FormData();
        data.append("file", context.files[0]);
        data.append('query_type', 'upload_file');
        data.append('ident', ident);
        data.append('__node', TableBuilder.getUrlParameter('id_tree'));

        jQuery.ajax({
            data: data,
            type: "POST",
            url: TableBuilder.getActionUrl(),
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.status) {
                    var html = '<a href="' + response.link + '" target="_blank">Скачать</a>';
                    $(context).parents('.input-file').next().html(html);
                    $(context).parents('.files_type_fields').find('input[type=text]').val(response.long_link);
                } else {
                    TableBuilder.showErrorNotification("Ошибка при загрузке файла");
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end uploadFile


    uploadFileMulti : function (context, ident) {
        var arr = context.files;
        for (var index = 0; index < arr.length; ++index) {
            var data = new FormData();
            data.append("file", context.files[index]);
            data.append('query_type', 'upload_file');
            data.append('ident', ident);
            data.append('__node', TableBuilder.getUrlParameter('id_tree'));
            var $progress = jQuery(context).parent().parent().parent().parent().parent().find('.progress-bar');

            jQuery.ajax({
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        console.log(evt);
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = percentComplete * 100;
                            percentComplete = percentComplete + '%';
                            $progress.width(percentComplete);
                        }
                    }, false);

                    xhr.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                        }
                    }, false);

                    return xhr;
                },
                data: data,
                type: "POST",
                url: TableBuilder.getActionUrl(),
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.status) {
                        $progress.width('0%');
                        var html = '<li>' + response.short_link + '<a href="' + response.link + '" target="_blank" path="' + response.long_link + '">Скачать</a> <a class="delete" onclick="TableBuilder.doDeleteFile(this)">Удалить</a></li>';

                        $(context).parents(".multi_files").find(".uploaded-files ul").append(html);

                        TableBuilder.doSetInputFiles($(context).parents(".multi_files").find(".uploaded-files ul"));

                        TableBuilder.doSortFileUpload();
                    } else {
                        TableBuilder.showErrorNotification("Ошибка при загрузке файла");
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    var errorResult = jQuery.parseJSON(xhr.responseText);

                    TableBuilder.showErrorNotification(errorResult.message);
                    TableBuilder.hidePreloader();
                }
            });
        }
    }, // end uploadFileMulti

    selectImgInStorage : function (content) {

        var type = content.parents('tbody').attr('data-type');

        if (type == 'one_file') {
            content.parents('tbody').find('.one_img_uploaded').removeClass('selected');
        }

        if (content.hasClass('selected')) {
            content.removeClass('selected');
        } else {
            content.addClass('selected');
        }
    },

    selectWithUploaded : function (name, type, content) {

        var section = content.parents('.files_type_fields');

        section.find("#files_uploaded_table_" + name).show();

        TableBuilder.thisFileElementInGroup = content;

        var data = {
            query_type: "select_with_uploaded",
        };
        section.find('#files_uploaded_table_' + name + ' tbody').html('<tr><td colspan="5" style="text-align: center">Загрузка...</td></tr>');
        $.post(
            TableBuilder.getActionUrl(content),
            data,
            function (response) {

                section.find('tbody').html(response.data);
                section.find('tbody').attr('data-type', type);
            },
            'json'
        );
    },

    selectWithUploadedImages : function (name, type, thisFileElement, baseName, pageId) {

        TableBuilder.thisPictureElementInGroup = thisFileElement;

        var section = thisFileElement.parents('.pictures_input_field');

        section.find("#files_uploaded_table_" + name).show();

        var data = {
            query_type: "select_with_uploaded_images",
            ident : name,
            baseName : baseName,
            page_id : pageId
        };
        section.find('#files_uploaded_table_' + name + ' tbody').html('<tr><td colspan="5" style="text-align: center">Загрузка...</td></tr>');
        $.post(
            TableBuilder.getActionUrl(thisFileElement),
            data,
            function (response) {
                section.find('#files_uploaded_table_' + name + ' tbody').html(response.data);
                section.find('#files_uploaded_table_' + name + ' tbody').attr('data-type', type);
            },
            'json'
        );
    },

    selectFilesUploaded : function (name, type) {

        if (type == 'multi') {
            $("#files_uploaded_table_" + name + " input:checked").each(function ( index ) {
                var html = '<li> ' + $(this).attr('data-basename') + ' <a href="' + $(this).val() + '" target="_blank" path ="' + $(this).val() + '">Скачать</a> <a class="delete" onclick="TableBuilder.doDeleteFile(this)">Удалить</a></li>';
                $('.tb-uploaded-file-container-' + name + " .ui-sortable").append(html);
            });

            TableBuilder.doSetInputFiles($('.tb-uploaded-file-container-' + name + " ul"));

            TableBuilder.doSortFileUpload();
        } else {
            var file = $("#files_uploaded_table_" + name + " input:checked").val();
            var elementForUpdate = TableBuilder.thisFileElementInGroup.parent().find('input[type=text]');
            elementForUpdate.val(file);
            elementForUpdate.parents('.input-file').next().html('<a href="' + file + '" target="_blank">Скачать</a> | <a class="delete" style="color:red;" onclick="$(this).parents(\'.files_type_fields\').find(\'input[type=text]\').val(\'\'); $(this).parent().hide()">Удалить</a>');
        }

        $(".files_uploaded_table").hide();
    },

    selectImageUploaded : function (name, type) {
        var section = TableBuilder.thisPictureElementInGroup.parents('.pictures_input_field');

        if (type == 'multi') {
            section.find('#files_uploaded_table_' + name + ' .one_img_uploaded.selected img').each(function ( index ) {
                var img = $(this).attr('data-path');
                var html = '<li><img src="/' + img + '" data_src_original = "' + img + '" width="120px"><div class="tb-btn-delete-wrap"><button class="btn2 btn-default btn-sm tb-btn-image-delete" type="button" onclick="TableBuilder.deleteImage(this);"><i class="fa fa-times"></i></button></div></li>';
                section.find('.tb-uploaded-image-container_' + name + ' ul').append(html);

                TableBuilder.setInputImages('.tb-uploaded-image-container_' + name);
                $('.no_photo').hide();
            });
        } else {
            var img = section.find('#files_uploaded_table_' + name + ' .one_img_uploaded.selected img').attr('data-path');
            if (img != undefined) {
                section.find('[type=hidden]').val(img);
                section.find('.image-container_' + name).html('<div style="position: relative; display: inline-block;"><img src="/' + img + '" width="200px"><div class="tb-btn-delete-wrap"><button class="btn btn-default btn-sm tb-btn-image-delete" type="button" onclick="TableBuilder.deleteSingleImage(\'picture\', this);"><i class="fa fa-times"></i></button></div></div>');
            }
        }
        section.find('tbody').html('');
        $(".files_uploaded_table").hide();
    },

    closeWindowWithPictures : function () {
        $('.files_uploaded_table tbody').html('');
        $('.files_uploaded_table').hide();
    },

    doSetInputFiles : function (context) {
        var arrFiles = new Array();
        $(context).find('li a').each(function ( index ) {
            if ($(this).attr("path") != null) {
                arrFiles.push($(this).attr("path"));
            }
        });
        var jsonArray = JSON.stringify(arrFiles);
        if (jsonArray == "[]") {
            jsonArray = "";
        }
        $(context).parent().parent().parent().find("[type=hidden]").val(jsonArray);
    },

    doDeleteFile : function (context) {
        var ul = $(context).parent().parent();
        $(context).parent().remove();
        TableBuilder.doSetInputFiles(ul);

        return false;
    },

    changeGalleryAndTags: function (context) {
        var idGallary = context.parents('.filter_gallary_images').find('[name=id_gallery]').val();
        var idTag = context.parents('.filter_gallary_images').find('[name=id_tag]').val();
        var searchQuery = context.parents('.filter_gallary_images').find('[name=q]').val();
        var ident = context.parents('.filter_gallary_images').find('[name=ident]').val();
        var baseName = context.parents('.filter_gallary_images').find('[name=baseName]').val();

        var data = {
            query_type: "select_with_uploaded_images",
            ident : ident,
            baseName : baseName,
            tag : idTag,
            gallary : idGallary,
            q : searchQuery
        };
        var section = context.parents('tbody');

        $.post(
            TableBuilder.getActionUrl(),
            data,
            function (response) {
                section.html(response.data);
            }
        );
    },

    doSortFileUpload : function () {
        $('.uploaded-files ul').sortable(
            {
                items: "> li",
                update: function ( event, ui ) {
                    TableBuilder.doSetInputFiles($(this));
                }
            }
        );
    },

    showErrorNotification: function (message) {
        jQuery.smallBox({
            title : message,
            content : "",
            color : "#C46A69",
            iconSmall : "fa fa-times fa-2x fadeInRight animated",
            timeout : 10000,
            class : "error"
        });
    }, // end showErrorNotification

    showSuccessNotification: function (message) {
        jQuery.smallBox({
            title : message,
            content : "",
            color : "#659265",
            iconSmall : "fa fa-check fadeInRight animated",
            timeout : 3000
        });
    }, // end showSuccessNotification

    setPerPageAmount: function (perPage) {
        TableBuilder.showProgressBar();

        var data = {
            query_type: "set_per_page",
            per_page: perPage,
            "__node": TableBuilder.getUrlParameter('node')
        };

        var $posting = jQuery.post(TableBuilder.getActionUrl(), data);

        $posting.done(function (response) {
            doAjaxLoadContent(location.href);
        });
    }, // end setPerPageAmount

    doExport: function (type, urlBasic) {
        var values = $(TableBuilder.export_form).serializeArray();
        values.push({ name: 'type', value: type });
        values.push({ name: 'query_type', value: "export" });

        var out = new Array();
        $.each(values, function (index, val) {
            out.push(val['name'] + '=' + val['value']);
        });

        if (urlBasic == undefined) {
            urlBasic = document.location.pathname;
        }

        if (document.location.search) {
            var url = urlBasic + document.location.search + '&' + out.join('&');
        } else {
            var url = urlBasic + '?' + out.join('&');
        }

        location.href = url;

    }, // end doExport

    flushStorage: function () {
        TableBuilder.storage = {};
    }, // end flushStorage

    showBigErrorNotification: function (errors) {
        jQuery.bigBox({
            content : errors,
            color   : "#C46A69",
            icon    : "fa fa-warning shake animated"
        });
    }, // end showBigErrorNotification

    doImport: function (context, type, url) {
        if (url == undefined) {
            url = TableBuilder.getActionUrl();
        }

        TableBuilder.showPreloader();

        var data = new FormData();
        data.append("file", context.files[0]);
        data.append('type', type);
        data.append('query_type', 'import');

        jQuery.SmartMessageBox({
            title : "Произвести импорт?",
            content : "Результат импорта нельзя отменить",
            buttons : '[Нет][Да]'
        }, function (ButtonPressed) {
            if (ButtonPressed === "Да") {
                jQuery.ajax({
                    data: data,
                    type: "POST",
                    url: url,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.status) {
                            if (response.callback && window[response.callback]) {
                                window[response.callback](response);
                            } else {
                                TableBuilder.showSuccessNotification('Импорт прошел успешно');
                            }
                        } else {
                            if (typeof response.errors === "undefined") {
                                TableBuilder.showErrorNotification('Что-то пошло не так');
                            } else {
                                var errors = '';
                                jQuery(response.errors).each(function (key, val) {
                                    errors += val + '<br>';
                                });
                                TableBuilder.showBigErrorNotification(errors);
                            }
                        }
                        TableBuilder.hidePreloader();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        var errorResult = jQuery.parseJSON(xhr.responseText);

                        TableBuilder.showErrorNotification(errorResult.message);
                        TableBuilder.hidePreloader();
                    }
                });
            } else {
                TableBuilder.hidePreloader();
            }
        });
    }, // end doImport

    doDownloadImportTemplate: function (type) {
        TableBuilder.showPreloader();

        var $iframe = jQuery("#submiter");

        var values = new Array();
        values.push('type=' + type);
        values.push('query_type=get_import_template');

        var url = document.location.pathname + '?' + values.join('&');

        $iframe.attr('src', url);

        TableBuilder.hidePreloader();
    }, // end doDownloadImportTemplate

    doSelectAllMultiCheckboxes: function (context) {
        var isChecked = jQuery('input', context).is(':checked');
        var $multiActionCheckboxes = jQuery('.multi-checkbox input');

        $multiActionCheckboxes.prop('checked', isChecked);
    }, // end doSelectAllMultiCheckboxes

    doMultiActionCall: function (type) {
        TableBuilder.showPreloader();

        var ids = [];
        $("tbody .multi-checkbox [type=checkbox]:checked").each(function () {
            ids.push($(this).val());
        });

        var values = [];
        values.push({ name: 'type', value: type });
        values.push({ name: 'query_type', value: 'multi_action' });
        values.push({ name: '__node', value: TableBuilder.getUrlParameter('node') });
        values.push({ name: 'multi_ids', value: ids });

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: values,
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    if (response.is_hide_rows) {
                        $(response.ids).each(function (key, val) {
                            $('tr[id-row="' + val + '"]').remove();
                        });
                    }

                    TableBuilder.showSuccessNotification(response.message);
                } else {
                    if (typeof response.errors === "undefined") {
                        TableBuilder.showErrorNotification('Что-то пошло не так');
                    } else {
                        var errors = '';
                        jQuery(response.errors).each(function (key, val) {
                            errors += val + '<br>';
                        });
                        TableBuilder.showBigErrorNotification(errors);
                    }
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end doMultiActionCall

    doMultiActionCallWithOption: function (context, type, option) {
        TableBuilder.showPreloader();

        var values = jQuery('#' + TableBuilder.options.table_ident).serializeArray();
        values.push({ name: 'type', value: type });
        values.push({ name: 'option', value: option });
        values.push({ name: 'query_type', value: 'multi_action_with_option' });
        values.push({ name: '__node', value: TableBuilder.getUrlParameter('node') });

        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: values,
            dataType: 'json',
            success: function (response) {
                jQuery(context).parent().parent().parent().removeClass('open');

                if (response.status) {
                    if (response.is_hide_rows) {
                        jQuery(response.ids).each(function (key, val) {
                            jQuery('tr[id-row="' + val + '"]', '#' + TableBuilder.options.table_ident).remove();
                        });
                    }

                    TableBuilder.showSuccessNotification(response.message);
                } else {
                    if (typeof response.errors === "undefined") {
                        TableBuilder.showErrorNotification(phrase['Что-то пошло не так, попробуйте позже']);
                    } else {
                        var errors = '';
                        jQuery(response.errors).each(function (key, val) {
                            errors += val + '<br>';
                        });
                        TableBuilder.showBigErrorNotification(errors);
                    }
                }

                TableBuilder.hidePreloader();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end doMultiActionCallWithOption

    saveOrder: function (order) {
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: { order: order, params: TableBuilder.getUrlParameter('page'), query_type: 'change_order' },
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification(phrase['Порядок следования изменен']);
                } else {
                    if (response.message) {
                        TableBuilder.showErrorNotification(response.message);
                    } else {
                        TableBuilder.showErrorNotification(phrase['Что-то пошло не так, попробуйте позже']);
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end saveOrder

    openImageStorageModal: function (context, storageTypeSelect) {
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: { query_type: 'image_storage', storage_type: 'show_modal', "__node": TableBuilder.getUrlParameter('node'), storage_type_select: storageTypeSelect },
            dataType: 'json',
            success: function (response) {

                if (response.status) {
                    $(TableBuilder.image_storage_wrapper).html(response.html);
                    $('.image_storage_wrapper').show();
                    $('.tb-modal:visible').addClass('superbox-modal-hide').hide();

                    Superbox.input = $(context).parent().parent().find('input');
                    Superbox.type_select = storageTypeSelect;
                } else {
                    TableBuilder.showErrorNotification(phrase['Что-то пошло не так, попробуйте позже']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end openImageStorageModal

    closeImageStorageModal: function () {
        $('.image_storage_wrapper').hide();
        $('.superbox-modal-hide').removeClass('superbox-modal-hide').show();
    }, // end closeImageStorageModal

    openFileStorageModal: function (context) {
        jQuery.ajax({
            type: "POST",
            url: TableBuilder.getActionUrl(),
            data: { query_type: 'file_storage', storage_type: 'show_modal', "__node": TableBuilder.getUrlParameter('node') },
            dataType: 'json',
            success: function (response) {

                if (response.status) {
                    $(TableBuilder.image_storage_wrapper).html(response.html);
                    $('.image_storage_wrapper').show();
                    $('.tb-modal:visible').addClass('superbox-modal-hide').hide();

                    FileStorage.input = $(context).parent().parent().find('input');
                } else {
                    TableBuilder.showErrorNotification(phrase['Что-то пошло не так, попробуйте позже']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
                TableBuilder.hidePreloader();
            }
        });
    }, // end openFileStorageModal

    reLoadTable : function () {
        //  alert(window.location.href);
    }, //end reLoadTable

    addGroup : function (context) {
        var sectionGroup = $(context).parent().find(".section_group").first().clone();
        if ($(sectionGroup).find('input[data-multi=multi]').size() > 1) {
            $(sectionGroup).find('input[data-multi=multi]').not(":first").remove();
        }
        $(sectionGroup).find("input, textarea").val("");
        $(sectionGroup).find(".tb-uploaded-image-container").html("<ul class='dop_foto'></ul>");
        $(sectionGroup).find(".uploaded-files").html("<ul class='ui-sortable'></ul>");
        $(sectionGroup).find(".tb-uploaded-file-container").html("");

        $(context).parent().find(".other_section").append(sectionGroup);

        TableBuilder.refreshMask();
    },

    deleteGroup : function (context) {
        var sizeGroup = $(context).parents('.group').find(".section_group").size();
        var sectionGroup = $(context).parents('.section_group');
        if (sizeGroup == 1) {
            $(sectionGroup).find("input, textarea").val("");
            $(sectionGroup).find(".tb-uploaded-image-container").html("");
        } else {
            $(sectionGroup).remove();
        }
    },

    handleActionSelect : function () {
        var selectAction = $("select.action");
        if (selectAction.size() != 0) {
            var value = selectAction.val();
            TableBuilder.checkActionSelect(value);

            selectAction.change(function () {
                var value = selectAction.val();
                TableBuilder.checkActionSelect(value);
            });
        }
    },

    checkActionSelect : function (value) {
        $("section.section_field").hide();
        $("section.section_field." + value).show();
    },

    doActiveMenu : function () {

        var thisUrl = window.location.pathname;

        $('nav a[href="' + thisUrl + '"]').parent().addClass('active');
        if (!$('nav a[href="' + thisUrl + '"]').parents('.level1').hasClass('active')) {
            $('nav a[href="' + thisUrl + '"]').parents('.level1').addClass('open');
        }
        $('nav a[href="' + thisUrl + '"]').parents('.level1').find('ul').show();
    },

    urlRusLat : function (str) {
        str = str.toLowerCase(); // все в нижний регистр
        var cyr2latChars = new Array(
            ['а', 'a'],
            ['б', 'b'],
            ['в', 'v'],
            ['г', 'g'],
            ['д', 'd'],
            ['е', 'e'],
            ['є', 'ye'],
            ['ё', 'yo'],
            ['ж', 'zh'],
            ['з', 'z'],
            ['і', 'i'],
            ['и', 'i'],
            ['й', 'y'],
            ['к', 'k'],
            ['л', 'l'],
            ['м', 'm'],
            ['н', 'n'],
            ['о', 'o'],
            ['п', 'p'],
            ['р', 'r'],
            ['с', 's'],
            ['т', 't'],
            ['у', 'u'],
            ['ф', 'f'],
            ['х', 'h'],
            ['ц', 'c'],
            ['ч', 'ch'],
            ['ш', 'sh'],
            ['щ', 'shch'],
            ['ъ', ''],
            ['ы', 'y'],
            ['ь', ''],
            ['э', 'e'],
            ['ю', 'yu'],
            ['я', 'ya'],
            ['А', 'A'],
            ['Б', 'B'],
            ['В', 'V'],
            ['Г', 'G'],
            ['Д', 'D'],
            ['Е', 'E'],
            ['Ё', 'YO'],
            ['Ж', 'ZH'],
            ['З', 'Z'],
            ['И', 'I'],
            ['Й', 'Y'],
            ['К', 'K'],
            ['Л', 'L'],
            ['М', 'M'],
            ['Н', 'N'],
            ['О', 'O'],
            ['П', 'P'],
            ['Р', 'R'],
            ['С', 'S'],
            ['Т', 'T'],
            ['У', 'U'],
            ['Ф', 'F'],
            ['Х', 'H'],
            ['Ц', 'C'],
            ['Ч', 'CH'],
            ['Ш', 'SH'],
            ['Щ', 'SHCH'],
            ['Ъ', ''],
            ['Ы', 'Y'],
            ['Ь', ''],
            ['Э', 'E'],
            ['Ю', 'YU'],
            ['Я', 'YA'],
            ['a', 'a'],
            ['b', 'b'],
            ['c', 'c'],
            ['d', 'd'],
            ['e', 'e'],
            ['f', 'f'],
            ['g', 'g'],
            ['h', 'h'],
            ['i', 'i'],
            ['j', 'j'],
            ['k', 'k'],
            ['l', 'l'],
            ['m', 'm'],
            ['n', 'n'],
            ['o', 'o'],
            ['p', 'p'],
            ['q', 'q'],
            ['r', 'r'],
            ['s', 's'],
            ['t', 't'],
            ['u', 'u'],
            ['v', 'v'],
            ['w', 'w'],
            ['x', 'x'],
            ['y', 'y'],
            ['z', 'z'],
            ['A', 'A'],
            ['B', 'B'],
            ['C', 'C'],
            ['D', 'D'],
            ['E', 'E'],
            ['F', 'F'],
            ['G', 'G'],
            ['H', 'H'],
            ['I', 'I'],
            ['J', 'J'],
            ['K', 'K'],
            ['L', 'L'],
            ['M', 'M'],
            ['N', 'N'],
            ['O', 'O'],
            ['P', 'P'],
            ['Q', 'Q'],
            ['R', 'R'],
            ['S', 'S'],
            ['T', 'T'],
            ['U', 'U'],
            ['V', 'V'],
            ['W', 'W'],
            ['X', 'X'],
            ['Y', 'Y'],
            ['Z', 'Z'],
            [' ', '-'],
            ['0', '0'],
            ['1', '1'],
            ['2', '2'],
            ['3', '3'],
            ['4', '4'],
            ['5', '5'],
            ['6', '6'],
            ['7', '7'],
            ['8', '8'],
            ['9', '9'],
            ['-', '-']
        );
        var newStr = new String();
        for (var i = 0; i < str.length; i++) {
            var ch = str.charAt(i);
            var newCh = '';
            for (var j = 0; j < cyr2latChars.length; j++) {
                if (ch == cyr2latChars[j][0]) {
                    newCh = cyr2latChars[j][1];
                }
            }
            // Если найдено совпадение, то добавляется соответствие, если нет - пустая строка

            newStr += newCh;
        }
        // Удаляем повторяющие знаки - Именно на них заменяются пробелы.
        // Так же удаляем символы перевода строки, но это наверное уже лишнее

        return newStr.replace(/[_]{2,}/gim, '_').replace(/\n/gim, '');
    },

    doClosePopup : function (table) {

        if ($('.modal_form_' + table).parent().hasClass('modal_popup_first')) {
            TableBuilder.hideBackgroundForm();
        }
        try {
            TableBuilder.destroyFroala(table);
        } catch (err) {
        }

        $('.modal_form_' + table).remove();
    },

    destroyFroala : function (table) {
        var textBlocks = ".modal_form_" + table + " .text_block";

        $(textBlocks).each(function ( index ) {
            $(this).froalaEditor('destroy');
        });
    },

    hideBackgroundForm : function () {
        $('body').removeClass('modal-open').css('padding-right', '0');
        $('.modal-backdrop').remove();
        $('#table-preloader').hide();

        TableBuilder.clearParamsWithUrl();
    },

    clearParamsWithUrl : function () {
        var url = Core.delPrm("id");

        if (url != undefined) {
            window.history.pushState(url, '', url);
        }
    },
    initSortableValue : function ($target) {
        if ($target.length) {
            $target.find(".sortable-values").sortable({
                items: ".sortable-item",
                handle: ".sortable-handler",
                axis: "y",
                update: function (event, ui) {
                    TableBuilder.updateSortableValue($target);
                }
            }).disableSelection();
        }
    },
    updateSortableValue : function ($target) {
        var $items = $target.find('.sortable-item'),
            values = [],
            $input = $target.find('[data-sortable-result]');

        $items.each(function () {
            var $optionActivator = $(this).find('input[data-option-activation]');

            if ($optionActivator.length) {
                if ($optionActivator.is(':checked')){
                    values.push(this.getAttribute('data-value'));
                }
            } else {
                values.push(this.getAttribute('data-value'));
            }

        });

        $input.val(values.join(','));
    },


    sendInlineEditForm: function(context, field, idRow)
    {
        var data = $(context).closest('.tb-inline-edit-container').find("select, textarea, input").serializeArray();
        data.push({ name: 'id', value: idRow });
        data.push({ name: 'name', value: field });
        data.push({ name: 'query_type', value: 'fast_save' });

        var url = TableBuilder.getActionUrl($(context)) == undefined ? '/admin/tree' : TableBuilder.getActionUrl($(context));

        jQuery.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Сохранено успешно');
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });
    }, // end sendInlineEditForm


    editFastSetField : function (content) {

        var value = content.attr('data-is-json') ? JSON.stringify(content.val()) : content.val();

        $.post( TableBuilder.getActionUrl(content), {
            name: content.attr('data-name'),
            id: content.attr('data-id'),
            value: value,
            query_type : 'fast_save'
        } );
    }
};

$(document).on('change', '[data-interface="sortable"] [data-option-activation]', function() {
    var $target = $(this).closest('[data-interface="sortable"]');
    TableBuilder.updateSortableValue($target);
});

$(window).load(function () {

    TableBuilder.handleStartLoad();
    TableBuilder.initSearchOnEnterPressed();

    $(document).on('click', 'a.node_link', function (e) {
        var href = $(this).attr('href');
        doAjaxLoadContent(href);
        e.preventDefault();
    });

});
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
