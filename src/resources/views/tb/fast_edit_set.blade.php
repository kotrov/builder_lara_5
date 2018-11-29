
<table class="tb-inline-edit-container">
    <tbody>
    <?php foreach($options as $setIdent => $caption): ?>
    <tr style="white-space: nowrap;">
        <td>
            <span class="">
                {{$caption}}:
            </span>
        </td>
        <td>
            <span class="onoffswitch">
                <input onchange="TableBuilder.sendInlineEditForm(this, '{{ $ident }}', {{$row['id']}});" type="checkbox"
                       name="{{ $ident }}[{{$setIdent}}]"
                       class="onoffswitch-checkbox"
                       {{is_array($selected) && in_array($setIdent, $selected) ? 'checked' : ''}}
                       id="{{$row['id']}}-{{ $ident }}-{{$setIdent}}"
                       value="{{$setIdent}}"
                >
                <label class="onoffswitch-label" for="{{$row['id']}}-{{ $ident }}-{{$setIdent}}">
                    <span class="onoffswitch-inner" data-swchon-text="ДА" data-swchoff-text="НЕТ"></span>
                    <span class="onoffswitch-switch"></span>
                </label>
            </span>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
