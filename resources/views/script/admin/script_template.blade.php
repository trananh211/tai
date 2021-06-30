<script>
    $(document).ready(function(){
        $("#woo-store-select").change(function(){
            var element = $("option:selected", this);
            var url = element.attr('url');
            var consumer_key = element.attr('consumer_key');
            var consumer_secret = element.attr('consumer_secret');
            // alert( consumer_key +' --- '+ consumer_secret);
            $("#url").val(url);
            $("#consumer_key").val(consumer_key);
            $("#consumer_secret").val(consumer_secret);
        });
    });
</script>
