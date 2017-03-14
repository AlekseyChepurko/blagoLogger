/**
 * Created by Алексей on 14.03.2017.
 */

let Logger = (function (){

    let getAllInputs = function()
    {
        return $("input");
    }

    let saveInputValues = function(){
        let inputs = getAllInputs();
        $.ajax({
            type: "POST",
            data: {
                inputs: inputs.serialize(),
            },
            url: "../logger/loggerHandler.php",
            success: function(data){
                console.log("all clear");
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