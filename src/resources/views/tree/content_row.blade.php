<tr data-id="{{ $item->id }}">
    <td class="tb-sort-me-gently" style="cursor:s-resize;">
        <i class="fa fa-sort"></i>
    </td>
    <td>
        <i class="fa {{$item->isHasChilder() ? 'fa-folder' : 'fa-file-o'}}"></i>&nbsp;
        <a href="?node={{ $item->id }}" class="node_link">{{ $item->title }}</a>
    </td>
    @if(config('builder.' . $treeName . '.list_fields'))
        @foreach(config('builder.' . $treeName . '.list_fields') as $nameBDField => $field)
            <td style="text-align: center">
                 @if (isset($item->$nameBDField))
                        <?php $fieldClass =  $controller->getField($nameBDField); ?>

                        {!! $fieldClass->getAttribute('fast-edit') ?
                                $fieldClass->getListValueFastEdit($item->toArray(), $nameBDField) :
                                $fieldClass->getListValue($item->toArray()) !!}
                 @endif
            </td>
        @endforeach
    @else
        <td>
            <a class="tpl-editable" href="javascript:void(0);"
                data-type="select"
                data-name="template"
                data-pk="{{ $item->id }}"
                data-value="{{ $item->template }}"
                data-original-title="{{__cms("Выберите шаблон")}}">
                    @if (isset(config('builder.' . $treeName . '.templates')[$item->template]['title']))
                        {{config('builder.' . $treeName . '.templates')[$item->template]['title']}}
                    @else
                        {{ $item->template }}
                    @endif
            </a>
        </td>
        <td style="white-space: nowrap;">{{ $item->slug }}</td>
        <td style="position: relative;">

            @if (isset($controller->getFields()['is_active']) && $controller->getField('is_active')->getAttribute('type') == 'set')
                {!! $controller->getField('is_active')->getListValueFastEdit($item, 'is_active') !!}
            @else
                <span class="onoffswitch">
                    <input onchange="Tree.activeToggle('{{$item->id}}', this.checked);" type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" @if ($item->is_active) checked="checked" @endif id="myonoffswitch{{$item->id}}">
                    <label class="onoffswitch-label" for="myonoffswitch{{$item->id}}">
                        <span class="onoffswitch-inner" data-swchon-text="{{__cms('ДА')}}" data-swchoff-text="{{__cms("НЕТ")}}"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </span>
            @endif

        </td>
    @endif

    <td style="text-align: center">
       <div style="display: inline-block">
         <div class="btn-group hidden-phone pull-right">
              <a class="btn dropdown-toggle btn-default" data-toggle="dropdown"><i class="fa fa-cog"></i> <i class="fa fa-caret-down"></i></a>
                <ul class="dropdown-menu">
                    @include('admin::tree.partials.update', ['type' => 'update'])
                    @include('admin::tree.partials.preview', ['type' => 'preview'])
                    @include('admin::tree.partials.clone', ['type' => 'clone'])
                    @include('admin::tree.partials.revisions', ['type' => 'revisions'])
                    @include('admin::tree.partials.constructor', ['type' => 'constructor'])
                  {{--  @include('admin::tree.partials.statistic', ['type' => 'statistic'])--}}
                    @include('admin::tree.partials.delete', ['type' => 'delete'])
                </ul>
         </div>
    </div>
    </td>
</tr>
