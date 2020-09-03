
(function ($) {
    "use strict";


    /*==================================================================
    [ Validate ]*/
    var input = $('.validate-input .input100');

    $('.validate-form').on('submit',function(){
        var check = true;

        for(var i=0; i<input.length; i++) {
            if(validate(input[i]) == false){
                showValidate(input[i]);
                check=false;
            }
        }

        return check;
    });


    $('.validate-form .input100').each(function(){
        $(this).focus(function(){
           hideValidate(this);
        });
    });

    function validate (input) {
        if($(input).attr('type') == 'email' || $(input).attr('name') == 'email') {
            if($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
                return false;
            }
        }
        else {
            if($(input).val().trim() == ''){
                return false;
            }
        }
    }

    function showValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).addClass('alert-validate');
    }

    function hideValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).removeClass('alert-validate');
    }
    
    

})(jQuery);

function copyCommand() {
    var copyText = document.getElementById("commandinput");

    copyText.select();
    copyText.setSelectionRange(0, 99999);

    document.execCommand("copy");
}

function showScreen(screenToShow) {
    const SCREENS = ["#loadingbox", "#loginbox", "#successbox"];
    SCREENS.forEach(screen => {
        if (screenToShow === screen) {
            $(screen).removeClass("hiddenitem");
        } else {
            $(screen).addClass("hiddenitem");
        }
    });
}

$(document).ready(() => {
    console.log("docready");
    document.getElementById("loginform").addEventListener("submit", (e) => {
        e.preventDefault();
        showScreen("#loadingbox");
        $.ajax({
            type: "POST",
            url: "/api/verifylogin.php",
            data: JSON.stringify({
                "username": $("#usernameinput").val(),
                "password": $("#passwordinput").val()
            }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: (data) => {
                console.log(data);
            },
            failure: (errMsg) => {
                console.log(errMsg);
            }
        })
    });
});