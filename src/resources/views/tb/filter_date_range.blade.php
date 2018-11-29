<div style="text-align: center">
    <table style="margin: 0 auto">
        <tr>
            <td>
                <div style="position: relative">
                    <input type="text"
                           id="f-from-{{$name}}"
                           value="{{$value['from'] ?? ''}}"
                           name="filter[{{ $name }}][from]"
                           class="form-control input-small datepicker datepicker_range" >

                    <span class="input-group-addon form-input-icon form-input-filter-icon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </td>
            <td style="padding: 0 5px">
                <div><i class="fa fa-minus"></i></div>
            </td>
            <td>
                <div style="position: relative">
                    <input type="text"
                           id="f-to-{{$name}}"
                           value="{{$value['to'] ?? ''}}"
                           name="filter[{{ $name }}][to]"
                           class="form-control input-small datepicker datepicker_range" >

                    <span class="input-group-addon form-input-icon form-input-filter-icon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </td>
        </tr>
    </table>

</div>

<script>

    $( function() {
        var dateFormat = "yy-mm-dd",
            from = $( "#f-from-{{$name}}" )
                .datepicker({
                    defaultDate: "+1w",
                    prevText: '<i class="fa fa-chevron-left"></i>',
                    nextText: '<i class="fa fa-chevron-right"></i>',
                    changeMonth: true,
                    numberOfMonths: 2,
                    dateFormat: dateFormat,
                })
                .on( "change", function() {
                    to.datepicker( "option", "minDate", getDate( this ) );
                }),
            to = $( "#f-to-{{$name}}" ).datepicker({
                defaultDate: "+1w",
                prevText: '<i class="fa fa-chevron-left"></i>',
                nextText: '<i class="fa fa-chevron-right"></i>',
                changeMonth: true,
                numberOfMonths: 2,
                dateFormat: dateFormat,
            })
            .on( "change", function() {
                from.datepicker( "option", "maxDate", getDate( this ) );
            });

        function getDate( element ) {
            var date;
            try {

                date = $.datepicker.parseDate( dateFormat, element.value );
            } catch( error ) {
                date = null;
            }

            return date;
        }
    } );

</script>
