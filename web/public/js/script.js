function clock() {
    var d = new Date();
    var month_num = d.getMonth()
    var day = d.getDate();
    var hours = d.getHours();
    var minutes = d.getMinutes();
    var seconds = d.getSeconds();

    month = new Array("січня", "лютого", "березня", "квітня", "травня", "червня",
        "липня", "серпня", "вересня", "жовтня", "листопада", "грудня");

    if (day <= 9) day = "0" + day;
    if (hours <= 9) hours = "0" + hours;
    if (minutes <= 9) minutes = "0" + minutes;
    if (seconds <= 9) seconds = "0" + seconds;

    date_time = "<div class='time'>" + hours + ":" + minutes + ":" + seconds + "<br>" + day + " " + month[month_num] + " " + d.getFullYear() +
        "р.</div>";

    if (document.layers) {
        document.layers.doc_time.document.write(date_time);
        document.layers.doc_time.document.close();
    }
    else document.getElementById("doc_time").innerHTML = date_time;
    setTimeout("clock()", 1000);
}

function ajaxSearch(el) {
   var string = $(el).val();
   var filed = $(el).attr('data-field');  
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "/ajax-search",
        data: {
            'string':string,
            'field': filed
        },
        async: true
    })
        .done(function (response) {

            if(response.result == 1){
                var phrase = [];
                for (var i = 0; response.content.length; i++  ){
                    var name = response.content[i];
                    if(name != undefined){
                        phrase[i] = name.phrase;
                    }else {
                        break;
                    }
                }
            }

            $('#appbundle_visitor_sName').autocomplete(
                {
                    source: phrase
                }
            );

            $('#appbundle_visitor_fName').autocomplete(
                {
                    source: phrase
                }
            );

            $('#appbundle_visitor_tName').autocomplete(
                {
                    source: phrase
                }
            );

            $('#searchSName').autocomplete(
                {
                    source: phrase
                }
            );


        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.log('Error : ' + errorThrown);
        });
 
}