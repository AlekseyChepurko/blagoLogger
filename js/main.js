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
            dataType: "json",
            data: {
                inputs: inputs.serialize(),
            },
            url: "../logger/loggerHandler.php",
            success: function(data){
                console.log(data);
            },
            error: function(error){
                console.log(error);
            }


        });
    }

    return {
        getAllInputs: getAllInputs,
        saveInputValues: saveInputValues,
    }

}());

$("input").change(function() {

    Logger.saveInputValues();
    // console.log(window.location.pathname);
    // Logger.getAllInputs().each(function(){
    //     if(this.value)
    //         console.log(this.value);
    // });
});