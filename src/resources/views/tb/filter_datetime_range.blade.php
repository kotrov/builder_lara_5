<div style="white-space: nowrap">
   <table>
       <tr>
           <td>
               <div style="position: relative">
                   <input type="text"
                                  id="f-from-{{$name}}"
                                  value="{{$valueFrom ?? ''}}"
                                  name="filter[{{ $name }}][from]"
                                  class="form-control input-small datepicker datepicker_range" >

                           <span class="input-group-addon form-input-icon form-input-filter-icon">
                        <i class="fa fa-calendar"></i>
                    </span>
               </div>
           </td>
           <td>
               <div><i class="fa fa-minus"></i></div>
           </td>
           <td>
               <div style="position: relative">
                   <input type="text"
                          id="f-to-{{$name}}"
                          value="{{$valueTo ?? ''}}"
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

    jQuery("#f-from-{{$name}}").datepicker({
        changeMonth: true,
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        dateFormat: "yy-mm-dd",
        //showButtonPanel: true,
        regional: ["ru"],
        onClose: function (selectedDate) {}
    });
    
    jQuery("#f-to-{{$name}}").datepicker({
        changeMonth: true,
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        dateFormat: "yy-mm-dd",
        //showButtonPanel: true,
        regional: ["ru"],
        onClose: function (selectedDate) {}
    });

</script>
