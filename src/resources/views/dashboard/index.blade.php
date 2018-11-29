@extends('admin::layouts.default')

@section('title')
    {{__cms('Рабочий стол')}}
@stop
@section('ribbon')
    <ol class="breadcrumb">
        <li><a href="/admin">{{__cms('Главная')}}</a></li>
        <li>{{__cms('Рабочий стол')}}</li>
    </ol>
@stop

@section('main')
   <div class="row">
       @foreach($columns as $column)
           <article class="col-sm-12 col-md-12 col-lg-{{$nameClassGrid}} sortable-grid ui-sortable">

               @foreach($column as $block)

               <div id="wid-id-{{$loop->index}}" class="jarviswidget  jarviswidget-color-blueDark jarviswidget-sortable" data-widget-fullscreenbutton="false" data-widget-editbutton="false" style="" role="widget">
                   <header>
                       @if (isset($block['icon']))
                            <span class="widget-icon"> <i class="fa fa-{{$block['icon'] ?? ""}} txt-color-white"></i> </span>
                       @endif
                       <h2> {{$block['title'] ?? ""}} </h2>

                   </header>
                   <div role="content">
                       <div class="widget-body no-padding">
                           <table id="datatable_fixed_column" class="table table-hover table-bordered">
                               <thead>
                               <tr>
                                   <td>sd</td>
                                   <td>adsd</td>
                               </tr>
                               </thead>
                               <tbody>
                                    <tr>
                                        <td>asa</td>
                                        <td>asd</td>
                                    </tr>
                               </tbody>
                           </table>
                       </div>
                   </div>
               </div>

               @endforeach

           </article>
        @endforeach
   </div>
@stop
