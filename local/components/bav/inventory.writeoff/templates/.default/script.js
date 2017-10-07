/**
 * Created by revil on 27.08.17.
 */
$(document).ready(function () {
    $.ajax({
        url: "/include/ajax/show_inv_books.php",
        method: "POST",
        data: {},
        cache: false,
        async: false,
        success: function (data) {
            var result = $.parseJSON(data);
            var html = "";
            $.each(result, function (key, book) {
                html += "<tr><td colspan='3'>"
                html += book.BOOK_NAME + "<br>";
                html += "<b>Автор: </b> " + book.AUTHOR + "<br>";
                html += "<b>Издательство: </b> " + book.IZD + "<br>";
                html += "<b>Код ФП: </b> " + book.FP_CODE + "<br>";
                html += "</tr>"
                $.each(book.INV_INFO, function (key, YC) {
                    html += "<tr><td width='80%'><b>Год приобретения:</b> " + YC.YEAR_PURCHASE + "<br>";
                    html += "</td><td>" + YC.COUNT + "</td>"
                    html += "<td><input class='form-control' type='text' name='" + YC.INV_ID + "' value='" + (YC.WRITEOFF || 0) + "'/></td></tr>";
                })
            });

            $("#book-data tbody").append(html);
        },
        error: function () {
            alert("Ajax Error! Свяжитесь с администратором!");
        }
    })
});

function saveWriteOff() {
    var book_ids = $('#book_ids').serialize();

    $.ajax({
        url: "/include/ajax/save_writeoff_info.php",
        method: "POST",
        data: book_ids,
        cache: false,
        async: false,
        success: function (data) {
            if (data == 1)
                alert("Данные успешно сохранены!");
            else
                alert("Ошибка изменения данных! Обратитесь к администратору!");
        },
        error: function () {
            alert("Ajax Error! Свяжитесь с администратором!");
        }
    })

}