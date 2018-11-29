<div>
    <input class="select2-enabled" type="hidden" id="{{$name}}{{$postfix}}" name="{{$name}}" style="width:100%;">
    @if ($insert)
        <p><a onclick="$(this).parents('div').find('.input-file').show(); $(this).parent().hide();">Добавить</a></p>
        <div class="input input-file" style="margin-top: 10px; display: none">

            <span class="button" onclick="addNewRecort{{$name}}{{$postfix}}('{{$name}}', '{{ $attributes }}')">Добавить</span>
            <input type="text"  placeholder="Введите новое значение" class="form-control insert_many_to_many{{$name}}">
        </div>
    @endif
</div>
<script>

    jQuery(document).ready(function() {

         $select2{{$name}}{{$postfix}} = jQuery('#{{$name}}{{$postfix}}').select2({
            placeholder: "{{ $search['placeholder'] ?? 'Поиск' }}",
            minimumInputLength: {{ $search['minimum_length'] ?? '3' }},
            multiple: true,
            language: "ru",
            ajax: {
                url: jQuery('#{{$name}}{{$postfix}}').parents('form').attr('action'),
                dataType: 'json',
                type: 'POST',
                quietMillis: {{ $search['quiet_millis'] ?? '350' }},
                data: function (term, page) { // page is the one-based page number tracked by Select2
                    return {
                        q: term, //search term
                        limit: {{ $search['per_page'] ?? '20' }}, // page size
                        page: page, // page number
                        @if (isset($row['id']))
                            page_id : {{$row['id']}},
                        @endif
                        ident: '{!! $name !!}',
                        query_type: 'many_to_many_ajax_search',
                    };
                },
                results: function (data, page) {
                    return data;
                }
            },
            formatResult: function(item) {
                return item.name;
            },
            formatSelection: function(item) {
                return item.name + '<span class="item_id" data-id="' + item.id + '"></span>';
            },
            formatNoMatches : function () {
                return 'По результату поиска ничего не найдено';
            },
            formatSearching: function () { return "Ищет..."; },
            formatInputTooShort: function (input, min) { var n = min - input.length; return "Введите еще " + n + "   символ "; },

            dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
            escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
        });

        @if ($selected != '[]')
            $select2{{$name}}{{$postfix}}.select2("data", {!! $selected !!});
        @endif
    });


    function addNewRecort{{$name}}{{$postfix}}(nameField, attributes) {

        var newTitle = $('.insert_many_to_many' + nameField);
        if (!newTitle) return;

        jQuery.ajax({
            type: "POST",
            url: "/admin/insert-new-record-for-many-to-many",
            data: {
                'title' : newTitle.val(),
                'paramsJson' : attributes,
            },
            dataType: 'json',
            success: function(responseId) {

                var obj = $select2{{$name}}{{$postfix}}.select2("data");
                obj.push({
                    'id' : responseId,
                    'name' : newTitle.val()
                });

                $select2{{$name}}{{$postfix}}.select2("data", obj);
                $('.select2-drop').hide();
                newTitle.val('')
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorResult = jQuery.parseJSON(xhr.responseText);

                TableBuilder.showErrorNotification(errorResult.message);
            }
        });
    }

    $('.select2-choices').sortable(
        {
            items: "> li.select2-search-choice",
            update: function (event, ui) {

               var ids = $(this).parent().find('.item_id');
                var arrIds = [];
                ids.each(function(i, elem) {
                    arrIds.push($(this).attr('data-id'));
                });

                $('[name={{$name}}]').val(arrIds.join(','));
            }
        }
    );
</script>
