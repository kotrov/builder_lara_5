<li >
    <img class="image-attr-editable"
         data-tbnum="{{$key ?? ""}}"
         @if (strpos($value, '.svg'))
            width = '120'
            height='120'
            src="/{{$value}}"
         @else
            src="{{glide($value, ['w'=>'120','h'=>'120']) }}"
         @endif
         data_src_original= "{{$value}}"
         src_original = "{{$value}}"
         data-width = '120'
         data-height = '120'
    />

    <div class="tb-btn-delete-wrap">
        <button class="btn2 btn-default btn-sm tb-btn-image-delete"
                type="button"
                onclick="TableBuilder.deleteImage(this);">
            <i class="fa fa-times"></i>
        </button>
    </div>
</li>
