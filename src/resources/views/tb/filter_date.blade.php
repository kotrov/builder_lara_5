
<div style="position: relative;">
    <input type="text"
           id="f-{{$name}}"
           value="{{$value}}"
           name="filter[{{ $name }}]"
           class="form-control input-small datepicker" style="text-align: center">

    <span class="input-group-addon form-input-icon form-input-filter-icon">
        <i class="fa fa-calendar"></i>
    </span>
</div>

<script>
    $("#f-{{$name}}").datepicker({
        changeMonth: true,
        numberOfMonths: {{ $months ?? '1' }},
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        dateFormat: "yy-mm-dd",
        //showButtonPanel: true,
        regional: ["ru"],
        onClose: function (selectedDate) {}
    });
</script>
