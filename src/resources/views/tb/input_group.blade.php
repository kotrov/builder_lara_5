
<div class="group" name="{{$name}}">
    <div class="other_section">
        @foreach($rows as $k => $filds)
            <div class="section_group">
                @if (!$hideDelete)
                <a class="delete_group" onclick="TableBuilder.deleteGroup(this)" style="float: right; cursor: pointer; position: absolute; right: 0; z-index: 10"><i class="fa red fa-times"></i> Удалить</a>
                @endif
                @foreach($filds as $titleField => $fild)
                    <section class="{{$fild['class_name'] ?? ''}}" @if(isset($fild['tabs'])) style="margin-top:20px" @endif>

                        @if (!isset($fild['tabs']))
                            @if (!isset($fild['no_show_caption']) || !$fild['no_show_caption'])
                                <label class="label">{{$fild['caption']}}</label>
                            @endif
                        @endif
                        <div style="position: relative;">
                            <div class="tabs_section">
                                {!! $fild['html'] !!}
                                @if (isset($fild['multi']) && $fild['multi'])
                                    <input type="hidden" name="{{$titleField}}" value=''>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endforeach

    </div>
    @if (!$hideAdd)
        <a class="add_group" onclick="TableBuilder.addGroup(this); groupTabsRefresh('{{$name}}');"><i class="fa fa-plus-square"></i> Добавить</a>
    @endif
</div>
<script>

    var i = 0;
    $(".group[name={{$name}}] input, .group[name={{$name}}] select, .group[name={{$name}}] textarea" ).each(function( index ) {

        if ($(this).attr("name") != undefined) {
            i++;
            $(this).attr("id", "{{$name}}_" + $(this).attr("name"));
            $(this).attr("name", "{{$name}}[" + $(this).attr("name")+ "][]");

            if ($(this).attr("data-multi") == 'multi') {
                $(this).removeAttr('name');
            }
          //  $(this).addClass($(this).attr("id") + '_' + i);
        }
    });

    //group for tabs
    function groupTabsRefresh(name) {

        i = 0;
        $(".group[name=" + name + "] .tabs_section").each(function(){
            i++;
            $(this).find(".nav-tabs a").each(function(){
                var hrefOld = $(this).attr("href");
                $(this).attr("href", hrefOld + "_" + i);
            });
            $(this).find(".tab-content .tab-pane").each(function(){
                var idOld = $(this).attr("id");
                $(this).attr("id", idOld + "_" + i);
            });
        });
    }
    groupTabsRefresh('{{$name}}');
</script>
