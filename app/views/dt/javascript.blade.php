<script type="text/javascript">
    var asInitVals = new Array();

    $(document).ready(function(){
        // dynamic table
        // alert("doo");
        var oTable = $('.{{ $class }}').dataTable({
            "iDisplayLength": 50,
            "bJQueryUI": true,
            "sPaginationType": "full_numbers",

            @foreach ($options as $k => $o)
        {{ json_encode($k) }}: {{ json_encode($o) }},
        @endforeach
            @foreach ($callbacks as $k => $o)
        {{ json_encode($k) }}: {{ $o }},
        @endforeach
    });

        $("thead input").keyup( function () {
            // alert("boos");
            /* Filter on the column (the index) of this element */
                        // alert($("thead input").index(this));
            oTable.fnFilter( this.value, $("thead input").index(this));
        } );
        $("thead input").each( function (i) {
            asInitVals[i] = this.value;
        } );
        
        $("thead input").focus( function () {
            if ( this.className == "search_init" )
            {
                this.className = "";
                this.value = "";
            }
        } );
        
        $("thead input").blur( function (i) {
            if ( this.value == "" )
            {
                this.className = "search_init";
                this.value = asInitVals[$("thead input").index(this)];
            }
        } );

    });
</script>