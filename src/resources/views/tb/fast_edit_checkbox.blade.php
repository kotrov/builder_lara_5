<div style="position: relative">
     <span class="onoffswitch">
        <input onchange="TableBuilder.activeToggle('{{$row['id']}}', '{{ $ident }}', this.checked);"
               type="checkbox" name="onoffswitch" class="onoffswitch-checkbox"  id="myonoffswitch{{$row['id']}}_{{ $ident }}"
                 {{ $row[$ident] ? 'checked' : '' }}
        >
        <label class="onoffswitch-label" for="myonoffswitch{{$row['id']}}_{{ $ident }}">
            <span class="onoffswitch-inner" data-swchon-text="ДА" data-swchoff-text="НЕТ"></span>
            <span class="onoffswitch-switch"></span>
        </label>
    </span>
</div>
