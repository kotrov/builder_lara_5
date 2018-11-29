<label class="textarea"><textarea rows="{{$rows ?? '3'}}"
          class="custom-scroll"
          id="{{$name}}"

          @if ($disabled)
                disabled="disabled"
          @endif

          name="{{ $name }}">{{ $value }}</textarea></label>
@if (isset($comment) && $comment)
  <div class="note">
      {{$comment}}
  </div>
@endif
