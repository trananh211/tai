<script>
    $(document).ready(function(){
        $("#woo-store-select").change(function(){
            var element = $("option:selected", this);
            var store_name = element.attr('store_name');
            var url = element.attr('url');
            var consumer_key = element.attr('consumer_key');
            var consumer_secret = element.attr('consumer_secret');
            $("#url").val(url);
            $("#store_name").val(store_name);
            $("#consumer_key").val(consumer_key);
            $("#consumer_secret").val(consumer_secret);
        });
    });
</script>
