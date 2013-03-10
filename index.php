<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Organic SKOS</title>
    <script type="text/javascript" src="scripts/jquery-1.9.1.js"></script>
    <script type="text/javascript" src="scripts/jstree/jquery.jstree.js"></script>
    <script type="text/javascript" src="scripts/jquery-ui/js/jquery-ui-1.10.1.custom.js"></script>
    <script>
        $(document).ready(function () {

                $("#list").jstree({

                    "json_data": {
                        "ajax": {
                            "url": "api.php",

                            "data": function (n) {
                                var object = {action: "load", source: "<?php echo $_GET["uri"];?>" };
                                if (n.attr) object.id = n.attr("id");
                                return object;
                            }
                        },
                        "progressive_render": true

                    },
                    "types": {
                        "types": {
                            "default": {
                                "select_node": function (e) {
                                    this.toggle_node(e);
                                    $.getJSON('api.php',{action:"info",source:"<?php echo $_GET["uri"];?>","id": e.attr("id")}, function(data) {
                                        $("#info").html("uri: "+data.uri);

                                    });
                                    return false;
                                }

                            }
                        }
                    },
                    "plugins": [ "themes", "json_data", "ui", "wholerow", "types" ]

                });


            });

    </script>
</head>
<body>
<div id="info"></div>
<!--div class="ui-widget">
    <label for="search">Search: </label>
    <input id="search" />
</div-->
<div id="list">
</div>
</body>
</html>