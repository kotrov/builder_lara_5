<!DOCTYPE html>
<html lang="en-us">
    <head>
        <meta charset="utf-8">
        <title> @yield('title') - {{ __cms(config('builder.admin.caption')) }}</title>
        <meta name="description" content="">
        <meta name="author" content="VIS-A-VIS">
        <meta name="HandheldFriendly" content="True">
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="MobileOptimized" content="320">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" href="{{ config('builder.admin.favicon_url') }}" type="image/x-icon">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="/packages/vis/builder/css/all.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700">

        <script src="/packages/vis/builder/js/all_header1.js"></script>

        @yield('styles')
        @yield('scripts_header')

        <script src="/packages/vis/builder/js/all_header2.js"></script>

        @if (isset($customJs) && count($customJs))
            @foreach($customJs as $jsFile)
                <script src="{{$jsFile}}"></script>
            @endforeach
         @endif

        @if (isset($customCss) && count($customCss))
            @foreach($customCss as $cssFile)
                <link type="text/css" rel="stylesheet" href="{{$cssFile}}" />
            @endforeach
        @endif

        <script type="text/javascript" src="/packages/vis/builder/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="/packages/vis/builder/js/dataTables.bootstrap4.min.js"></script>

    </head>
    <body class="{{ Cookie::get('tb-misc-body_class', '') }} {{ $skin }}">
        <div id="modal_wrapper" class="modal_popup_first"></div>
        <div class="table_form_create modal_popup_first"></div>
        <div class="foreign_popups"></div>
        @include('admin::partials.header')
        @include('admin::partials.navigation')
        <div id="main" role="main">
            <div id="main-content">
                <div id="ribbon">
                    @yield('ribbon')
                </div>
                <div id="content">
                    @yield('headline')
                    <div id="content">
                        <div class="row" id="content_admin">

                            @yield('main')

                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('admin::partials.scripts')
        @yield('scripts')
        @include('admin::partials.translate_phrases')

        <div class="load_page" style="position: fixed; display: none; opacity: 0.7; z-index: 1111111; height: 50px; top: 10px; right: 30px"><i class="fa fa-spinner fa-spin" style="font-size: 40px"></i></div>
        @include('admin::partials.popup_cropp')

        <script src="/packages/vis/builder/js/cropper_model.js"></script>
    </body>
</html>
