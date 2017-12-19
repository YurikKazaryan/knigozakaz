/**
 * Created by revil on 21.08.17.
 */
$( document ).ready( function () {
    var classInfo = [];
    $.ajax({
        url: "/include/ajax/show_saved_pupil_count.php",
        method: "POST",
        data: {},
        cache: false,
        async: false,
        success: function (data) {
            var result = $.parseJSON(data);
            var html = "";
            classInfo = result;
            $.each(result, function (id,info) {
                var parsed = info.split(":");

                html += "<tr>";
                html += "<td>" + parsed[0] + "</td>";
                html += "<td>" + parsed[1] + "</td>";
                html += "<td><button type='button' class='btn btn-success' name='editClass' cid='" + id + "'>" +
                    "<span class='glyphicon glyphicon-edit'></span></button></td>";
                html += "</tr>";
            });

            $("#classTable tbody").append(html);
        }
    });

    $("input[name=addPupil]").click(function(){
        $("#addClassModal").modal();

        $("input[name=savePupil]").click(function(){
            var classs = $("#classs").val();
            var letter = $("#letter").val();
            var pupilCount = $("#pupilCount").val();
            var error = false;

            if (classs === "") {
                $("#classAlert").removeAttr("hidden");
                error = true;
            }
            else
                $("#classAlert").attr("hidden", true);

            if ((letter === "") || (letter.length > 1)) {
                $("#letterAlert").removeAttr("hidden");
                error = true;
            }
            else
                $("#letterAlert").attr("hidden", true);

            if (pupilCount === "") {
                $("#pupilCountAlert").removeAttr("hidden");
                error = true;
            }
            else
                $("#pupilCountAlert").attr("hidden", true);

            if (!error) {
                $.ajax({
                    url: "/include/ajax/save_pupil.php",
                    method: "POST",
                    data: {"CLASS": classs, "LETTER": letter, "PUPILCOUNT": pupilCount},
                    cache: false,
                    async: false,
                    success: function (data) {
                        if (data == "EXIST") {
                            $("#addError .modal-body").html("Данные указанного класса уже имеются!");
                            $("#addError").modal();
                        } else if (data == "OK")
                            window.location.reload();
                        else {
                            $("#addError .modal-body").html("Ошибка создания записи в БД! Обратитесь к администратору!");
                            $("#addError").modal();
                        }
                    }
                })
            }
        })
    })
    
    $("button[name=editClass]").click(function () {
        var cid = $(this).attr("cid");

        $("#addClassModal").modal();

        $("#addBookModalLabel").empty().append("Редактировать класс");

        var info = classInfo[cid];
        var parsed = info.split(":");

        var classs = parseInt(parsed[0].replace(/\D+/g, ""));
        var letter = parsed[0].replace(/\d/g, "");
        var pupilCount = parsed[1];

        $("#classs").val(classs);
        $("#letter").val(letter);
        $("#pupilCount").val(pupilCount);

        $("input[name=savePupil]").addClass("hide");
        
        $("input[name=editPupil]").click(function () {
            classs = $("#classs").val();
            letter = $("#letter").val();
            pupilCount = $("#pupilCount").val();
            error = false;

            if (classs === "") {
                $("#classAlert").removeAttr("hidden");
                error = true;
            }
            else
                $("#classAlert").attr("hidden", true);

            if ((letter === "") || (letter.length > 1)) {
                $("#letterAlert").removeAttr("hidden");
                error = true;
            }
            else
                $("#letterAlert").attr("hidden", true);

            if (pupilCount === "") {
                $("#pupilCountAlert").removeAttr("hidden");
                error = true;
            }
            else
                $("#pupilCountAlert").attr("hidden", true);

            if (!error)
                $.ajax({
                    url: "/include/ajax/save_pupil.php",
                    method: "POST",
                    data: {"CLASS": classs, "LETTER": letter, "PUPILCOUNT": pupilCount, "ID": cid, "MODE": "edit"},
                    cache: false,
                    async: false,
                    success: function (data) {
                        if (data == "UP")
                            window.location.reload();
                    }
                })
        })
    })
});