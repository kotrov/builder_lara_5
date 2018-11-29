<!-- Modal -->
<div class="modal fade tb-modal modal_form_{{$def['db']['table']}}" id="modal_form_edit"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" style="width: 920px;" data-width="920px">

        <div class="form-preloader smoke_lol"><i class="fa fa-gear fa-4x fa-spin"></i></div>

        <div class="modal-content">
            <div class="modal-header">
                <button class="btn btn-default close_button" style="float: right; margin: 0 5px" data-dismiss="modal" type="button" onclick="TableBuilder.doClosePopup('{{$def['db']['table']}}')"  > {{__cms('Отмена')}} </button>
                <button class="btn btn-success btn-sm" style="float: right" type="button"
                     onclick="$('#edit_form_{{$def['db']['table']}}').submit();">
                     <span class="glyphicon glyphicon-floppy-disk"></span>
                    {{__cms('Сохранить')}}
                </button>
                <h4 class="modal-title" id="modal_form_edit_label">{{$def['options']['caption'] ? :  'Структура сайта'}} : {{__cms('Редактирование')}}</h4>
            </div>
            
            @include('admin::tb.form_edit')

        </div>
    </div>
</div>
@include('admin::tb.form_js')
@include('admin::tb.form_edit_validation')
