/**
 * Created by Алексей on 14.03.2017.
 */

var Logger = (function (){

    var getAllInputs = function()
    {
        return $("input");
    }

    var saveInputValues = function(){
        var inputs = getAllInputs(),
            pageURL = window.location.href,
            pageTitle = $(document).find("title").text().replace(" ","_").toLowerCase();
        console.log(pageTitle);
        $.ajax({
            type: "POST",

            data: {
                inputs: inputs.serialize(),
                pageURL: pageURL,
                pageTitle: pageTitle,
            },
            url: "../logger/loggerHandler.php",
            success: function(data){
                console.log("all clear");
                console.log(data);
            },
            error: function(error){
                console.log("error");
                console.log(error);
            }
        });
    }

    return {
        saveInputValues: saveInputValues,
    }

}());