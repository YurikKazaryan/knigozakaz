/**
 * Created by revil on 21.08.17.
 */
$( document ).ready( function () {
    $("#savePupil").click(save_pupil);

    $.ajax({
        url: "/include/ajax/show_saved_pupil_count.php",
        method: "POST",
        data: {},
        cache: false,
        async: false,
        success: function (data) {
            var result = $.parseJSON(data);
            $.each(result, function (code, value) {
                $("#" + code).val(value || 0);
            })
        },
        error: function () {
            alert("Ajax Error! Свяжитесь с администратором!");
        }
    })
});

function save_pupil() {
    $.ajax({
        url: "/include/ajax/save_pupil.php",
        method: "POST",
        data: {
            "K1" : $("#K1").val(),
            "K2" : $("#K2").val(),
            "K3" : $("#K3").val(),
            "K4" : $("#K4").val(),
            "K5" : $("#K5").val(),
            "K6" : $("#K6").val(),
            "K7" : $("#K7").val(),
            "K8" : $("#K8").val(),
            "K9" : $("#K9").val(),
            "K10" : $("#K10").val(),
            "K11" : $("#K11").val(),
        },
        cache: false,
        async: false,
        success: function (data) {
            var result = $.parseJSON(data);
            if (result.error == 0) {
                alert("Код ошибки: " + result.error + ". " + result.error_text);
            } else {
                alert("Данные успешно сохранены!");
            }
        },
        error: function () {
            alert("Ajax Error! Свяжитесь с администратором!");
        }
    })
}