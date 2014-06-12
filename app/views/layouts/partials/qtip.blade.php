@if(!isset($classes) || $classes == '') 
    <?php $classes = "qtip-light qtip-shadow qtip-rounded"; ?>
@endif

<script type="text/javascript">
    // Grab all elements with the class "hasTooltip"
    $('.hasTooltip').each(function() { // Notice the .each() loop, discussed below
        $(this).qtip({
            content: {
                text: $(this).attr('title')
            },
        style: {
            classes: "<?=$classes?>"
        },
        position: {
            viewport: $(window)
        }
        });
    });
</script>