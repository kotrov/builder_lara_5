<style>
    .filter_autocomplete .select2-container .select2-choice .select2-arrow{
        display: none;
    }
    .filter_autocomplete .select2-container .select2-choice{
        cursor: text
    }
</style>
<div class="filter_autocomplete" style="position: relative; min-width: 125px">
    <input class="select2-enabled filter_{{ $name }}" type="hidden" id="filter[{{ $name }}]" name="filter[{{$name}}]" style="width:100%;" value="{{$value}}">
    @if ($value)
        <button onclick="$(this).parent().find('input').val(''); setTimeout(function(){ TableBuilder.search(); }, 200); return false;" class="close" style="position: absolute; top: 8px; right: 6px;">
            ×
        </button>
    @endif
</div>

<script>
    $(document).ready(function() {
       var filter_{{ $name }} =  $('.filter_{{ $name }}').select2({
            minimumInputLength: 3,
            multiple: false,
            language: "ru",
            ajax: {
                url: '/admin/handle/{{$definitionName}}',
                dataType: 'json',
                type: 'POST',
                quietMillis: 200,
                data: function (term, page) {
                    return {
                        q: term,
                        limit: 20,
                        ident: '{!! $name !!}',
                        query_type: 'foreign_ajax_search',
                        template: '{{ $search['template'] ?? '%[q]%' }}',
                    };
                },
                results: function (data, page) {
                    return data;
                }
            },
            formatResult: function (item) {
                return item.name;
            },
            formatSelection: function (item) {
                return item.name;
            },
            formatNoMatches: function () {
                return 'Ничего не найдено';
            },
            formatSearching: function () {
                return "Ищет...";
            },
            formatInputTooShort: function (input, min) {
                var n = min - input.length;
                return "Введите текст";
            },

            dropdownCssClass: "bigdrop",

            escapeMarkup: function (m) {
                return m;
            }
        });

        @if ($value)
            filter_{{ $name }}.select2("data", {'id' : '{{$value}}', 'name': '{{implode (" ", $valueJson)}}'});
        @endif

    });
 </script>
