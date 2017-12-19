/**
 * Created by revil on 07.09.17.
 */
var rpType = "";

function getBooksByParam() {
    var izd = $("#umk_izd :selected").val();
    var Class = $("#umk_class").val();
    var subject = $("#umk_subj :selected").val();


}

$(document).ready(function () {
    $(".build-step1 li").mouseover(function () {
        $(this).addClass("shadow");
    }).mouseleave(function () {
        $(this).removeClass("shadow");
    });

    $(".build-step1 li").click(function () {
        rpType = $(this).attr("value");

        switch (rpType) {
            case "rpOrders":
                $.ajax({
                    url: "/include/ajax/show_report_fields.php",
                    method: "POST",
                    data: {"rpType": rpType},
                    cache: false,
                    async: false,
                    success: function (data) {
                        var result = $.parseJSON(data);
                        html = "";
                        $.each(result, function (id, name) {
                            html += "<div class='checkbox'>";
                            html += "<label><input type='checkbox' value='" + id + "' name='properties' ";
                            html += "headerName = '" + name + "' jqTitle='PROPERTY" + id + "_VALUE'>" + name + "</label>";
                            html += "</div>";
                        });

                        $(".build-step1").hide(300);
                        $(".build-step2").removeClass("hidden");
                        $("#izdOrders").removeClass("hide");

                        $(".build-step2 #fieldsList").html(html);

                        $("#selectAllFields").click(function () {
                            if ($(this).attr("name") === "selectAll") {
                                $("input[name=properties]").prop("checked", true);
                                $(this).prop("name", "deselectAll");
                                $(this).prop("value", "Убрать выделение");
                            } else {
                                $("input[name=properties]").prop("checked", false);
                                $(this).prop("name", "selectAll");
                                $(this).prop("value", "Выделить все");
                            }
                        });

                        $("input[name=step3]").click(function () {
                            var properties = [];
                            var headers = [];
                            $(".checkbox input:checked").each(function () {
                                properties.push(this.value.replace("_", ""));
                                headers.push({
                                    label: $(this).attr("headerName"),
                                    name: $(this).attr("jqTitle")
                                });
                            });

                            if (properties.length === 0)
                                alert("Выберите поля для отображения в отчёте!");
                            else {
                                //$(".build-step2").hide(300);
                                $(".build-step3").removeClass("hide");
                                $("#jqGridPager").removeClass("hide");
                                $("#dataTable").removeClass("hide");
                                $.jgrid.gridUnload("#dataTable");

                                //$.jgrid.gridUnload("#dataTable");

                                $("#dataTable").jqGrid({
                                    colModel: headers,
                                    width: 800,
                                    height: 500,
                                    rowNum: 30,
                                    datatype: "local",
                                    pager: "#jqGridPager",
                                    shrinkToFit : false,
                                    forceFit: true
                                });

                                $.getJSON("/include/ajax/draw_report.php", {
                                    "fieldIds": properties,
                                    "rpType": rpType,
                                    "IZD": $("#order_izd :selected").val()
                                }, function (response) {
                                    var gridArrayData = [];
                                    //console.log(response);
                                    $.each(response, function (a, b) {
                                        gridArrayData.push(b);
                                    });

                                    $("#dataTable").jqGrid("setGridParam", {data: gridArrayData});
                                    $("#dataTable").trigger("reloadGrid");

                                    $("#dataTable").jqGrid('filterToolbar',
                                        {
                                            autoSearch: true,
                                            stringResult: true,
                                            searchOnEnter: true,
                                            ignoreCase: true,
                                            defaultSearch: "cn"
                                        }
                                    );

                                    $("#rpBudgetTitle").html("Перечень заказов муниципальных образований");

                                    $("#export").removeClass("hide");
                                }).fail(function (e) {
                                    console.log(e);
                                });
                            }
                        })
                        $("#export").on("click", function () {
                            $("#dataTable").jqGrid("exportToExcel", {
                                includeLabels: true,
                                includeGroupHeader: true,
                                includeFooter: false,
                                fileName: "orderData.xlsx"
                            })
                        })
                    },
                    error: function () {
                        alert("Ajax Error! Свяжитесь с администратором!");
                    }
                });
                break;

            case "rpInventory" :
                $.ajax({
                    url: "/include/ajax/show_report_fields.php",
                    method: "POST",
                    data: {"rpType": rpType},
                    cache: false,
                    async: false,
                    success: function(data) {
                        var result = $.parseJSON(data);
                        html = "";
                        $.each(result, function (id, name) {
                            html += "<div class='checkbox'>";
                            html += "<label><input type='checkbox' value='" + id + "' name='properties' ";
                            html += "headerName = '" + name + "' jqTitle='PROPERTY_" + id + "_VALUE'>" + name + "</label>";
                            html += "</div>";
                        });

                        $(".build-step1").hide(300);
                        $(".build-step2").removeClass("hidden");

                        $(".build-step2 .panel-body").html(html);

                        $("#selectAllFields").click(function () {
                            if ($(this).attr("name") === "selectAll") {
                                $("input[name=properties]").prop("checked", true);
                                $(this).prop("name", "deselectAll");
                                $(this).prop("value", "Убрать выделение");
                            } else {
                                $("input[name=properties]").prop("checked", false);
                                $(this).prop("name", "selectAll");
                                $(this).prop("value", "Выделить все");
                            }
                        });

                        $("input[name=step3]").click(function () {
                            var properties = [];
                            var headers = [];
                            $(".checkbox input:checked").each(function () {
                                properties.push(this.value);
                                headers.push({
                                    label: $(this).attr("headerName"),
                                    name: $(this).attr("jqTitle")
                                });
                            });

                            if (properties.length === 0)
                                alert("Выберите поля для отображения в отчёте!");
                            else {
                                $.jgrid.gridUnload("#dataTable");

                                $("#dataTable").jqGrid({
                                    colModel: headers,
                                    width: "800",
                                    height: 500,
                                    rowNum: 30,
                                    datatype: "local",
                                    pager: "#jqGridPager",
                                    shrinkToFit : false,
                                    forceFit: true
                                });

                                $.getJSON("/include/ajax/draw_report.php", {
                                    "fieldIds": properties,
                                    "rpType": rpType
                                }, function (response) {
                                    var gridArrayData = [];
                                    $.each(response, function (a, b) {
                                        gridArrayData.push(b);
                                    });

                                    //$(".build-step2").hide(300);
                                    $(".build-step3").removeClass("hidden");

                                    $("#dataTable").jqGrid('setGridParam', {data: gridArrayData});
                                    $("#dataTable").trigger('reloadGrid');

                                    if (gridArrayData.length === 0) alert("Данных для отображения не найдено");

                                    $("#export").on("click", function () {
                                        $("#dataTable").jqGrid("exportToExcel", {
                                            includeLabels: true,
                                            includeGroupHeader: true,
                                            includeFooter: false,
                                            fileName: "inventoryData.xlsx"
                                        })
                                    })
                                }).fail(function (e) {
                                    console.log(e);
                                });
                            }
                        })
                    },
                    error: function () {
                        alert("Ajax Error! Свяжитесь с администратором!");
                    }
                });
                break;

            case "rpSvod":
                var gridArrayData = [];
                $(".build-step1").hide(300);
                $(".build-step2").removeClass("hidden");
                $.ajax({
                    url: "/include/ajax/show_report_fields.php",
                    method: "POST",
                    data: {"rpType": rpType},
                    cache: false,
                    async: false,
                    success: function(data) {
                        var result = $.parseJSON(data);
                        html = "";
                        $.each(result, function (id, name) {
                            if (id === "USE_ORDERS") html += "<hr />";
                            html += "<div class='checkbox'>";
                            html += "<label><input type='checkbox' value='" + id + "' name='properties' checked ";
                            html += "headerName = '" + name + "' jqTitle='PROPERTY_" + id + "'>" + name + "</label>";
                            html += "</div>";
                        });

                        $("input[name=selectAll]").prop("value", "Убрать выделение");
                        $("input[name=selectAll]").prop("name", "deselectAll");

                        $(".build-step2 #fieldsList").html(html);

                        $("#selectAllFields").click(function () {
                            if ($(this).attr("name") === "selectAll") {
                                $("input[name=properties]").prop("checked", true);
                                $(this).prop("name", "deselectAll");
                                $(this).prop("value", "Убрать выделение");
                            } else {
                                $("input[name=properties]").prop("checked", false);
                                $(this).prop("name", "selectAll");
                                $(this).prop("value", "Выделить все");
                            }
                        });

                        $("input[name=step3]").click(function () {
                            var properties = [];
                            var headers = [];

                            $(".checkbox input:checked").each(function () {
                                properties.push(this.value);
                                if ($(this).attr("jqTitle") !== "PROPERTY_USE_ORDERS") {
                                    headers.push({
                                        label: $(this).attr("headerName"),
                                        name: $(this).attr("jqTitle")
                                    });
                                }
                            });

                            headers.push({
                                label: "Источник данных",
                                name: "PROPERTY_USE_ORDERS"
                            });

                            if (properties.length === 0)
                                alert("Выберите поля для отображения в отчёте!");
                            else {
                                $.jgrid.gridUnload("#dataTable");

                                $("#dataTable").jqGrid({
                                    colModel: headers,
                                    width: "800",
                                    height: 500,
                                    rowNum: 30,
                                    datatype: "local",
                                    pager: "#jqGridPager",
                                    shrinkToFit : false,
                                    forceFit: true
                                });

                                $.getJSON("/include/ajax/draw_report.php", {
                                    "fieldIds": properties,
                                    "rpType": rpType
                                }, function (response) {
                                    gridArrayData = [];

                                    $.each(response, function (a, b) {
                                        gridArrayData.push(b);
                                    });

                                    //$(".build-step2").hide(300);
                                    $(".build-step3").removeClass("hide");
                                    $("#dataTable").removeClass("hide");
                                    $("#jqGridPager").removeClass("hide");

                                    $("#dataTable").jqGrid('setGridParam', {data: gridArrayData});
                                    $("#dataTable").trigger('reloadGrid');

                                    $("#dataTable").jqGrid('filterToolbar',
                                        {
                                            autoSearch: true,
                                            stringResult: true,
                                            searchOnEnter: true,
                                            ignoreCase: true,
                                            defaultSearch: "cn"
                                        }
                                    );

                                    $("#rpBudgetTitle").html("Отчет по книгообеспеченности");

                                    $("#export").removeClass("hide");

                                    if (gridArrayData.length === 0) alert("Данных для отображения не найдено");
                                }).fail(function (e) {
                                    console.log(e);
                                });
                            }
                        })
                    },
                    error: function () {
                        alert("Ajax Error! Свяжитесь с администратором!");
                    }
                });
                $("#export").on("click", function () {
                    $.ajax({
                        url: "/include/ajax/prepare_excel.php",
                        method: "POST",
                        data: {"data": gridArrayData},
                        cache: false,
                        async: false,
                        success: function (data) {
                            var result = $.parseJSON(data);
                            window.open("/reports/download/?t=x&f=" + result);
                        }
                    });
                })
                break;
            case "rpUMK":
                $("input[name=selectAll]").addClass("hidden");
                $("#typeUMK").removeAttr("hidden");

                $(".build-step1").hide(300);
                $(".build-step2").removeClass("hidden");

                var gridArray = [];

                var isFirstTrigger = true;

                $("input[name=step3]").click(function () {
                    var UMK_IZD = $("#umk_izd :selected").val();
                    var UMK_SUBJ = $("#umk_subj :selected").val();
                    var UMK_CLASS = $("#umk_class").val();

                    gridArray = [];

                    $.ajax({
                        url: "/include/ajax/draw_report_umk.php",
                        method: "POST",
                        data: {"UMK_IZD": UMK_IZD, "UMK_SUBJ" : UMK_SUBJ, "UMK_CLASS" : UMK_CLASS},
                        cache: false,
                        async: false,
                        beforeSend: function () {
                            //$("#empty_list_loading").removeAttr('hidden');
                            //$(".report-control").attr('disabled', 'disabled');
                        },
                        success: function (data) {
                            if (data !== "null") {
                                $("#rpBudgetTitle").html("Отчет по использованию учебника в образовательных организациях");
                                
                                var result = jQuery.parseJSON(data);

                                $("#umk_book_div").removeAttr("hidden");
                                $(".build-step3").removeClass("hide");

                                $(".ui-jqgrid").addClass("hide");

                                $("#export").addClass("hide");

                                gridArray = [];

                                var html = "<option value='-'>-</option>";

                                $.each(result, function (bookId, bookName) {
                                    html += "<option value='" + bookId + "'>" + bookName + "</option>";
                                });

                                $("#umk_book").empty().append(html).change(function () {
                                    var bookId = $(this).val();

                                    $.ajax({
                                        url: "/include/ajax/show_book_use.php",
                                        method: "POST",
                                        data: {"BOOKID": bookId},
                                        cache: false,
                                        async: false,
                                        success: function (data) {
                                            if (data !== "null") {
                                                $(".build-step3").removeClass("hide");

                                                var result = $.parseJSON(data);
                                                var headers = [
                                                    {
                                                        "name": "RAION",
                                                        "label": "Район"
                                                    },
                                                    {
                                                        "name": "FULL_NAME",
                                                        "label": "Полное название"
                                                    },
                                                    {
                                                        "name": "ADDRESS",
                                                        "label": "Почтовый адрес"
                                                    },
                                                    {
                                                        "name": "DIR_FIO",
                                                        "label": "ФИО директора"
                                                    },
                                                    {
                                                        "name": "OTV_FIO",
                                                        "label": "ФИО администратора"
                                                    },
                                                    {
                                                        "name": "PHONE",
                                                        "label": "Телефон"
                                                    },
                                                    {
                                                        "name": "EMAIL",
                                                        "label": "Email"
                                                    },
                                                    {
                                                        "name": "ORDER_COUNT",
                                                        "label": "Заказы (экз.)"
                                                    }
                                                ];

                                                $.jgrid.gridUnload("#dataTable");

                                                $("#dataTable").jqGrid({
                                                    colModel: headers,
                                                    width: "800",
                                                    height: 300,
                                                    rowNum: 30,
                                                    datatype: "local",
                                                    pager: "#jqGridPager",
                                                    shrinkToFit: false,
                                                    forceFit: true
                                                });

                                                gridArray = [];

                                                $.each(result, function (a, b) {
                                                    gridArray.push(b);
                                                });



                                                $("#dataTable").jqGrid('setGridParam', {data: gridArray});
                                                $("#dataTable").trigger('reloadGrid');
                                                $(".ui-jqgrid").removeClass("hide");

                                                $("#dataTable").removeClass("hide");
                                                $("#jqGridPager").removeClass("hide");
                                                $(".ui-jqgrid").removeClass("hide");


                                                $("#export").removeClass("hide");
                                            } else {
                                                $("#errorModal .modal-body").html("Нет информации по данному учебнику или им никто не пользуется!");
                                                $("#errorModal").modal();
                                                $(".ui-jqgrid").addClass("hide");
                                                $("#export").addClass("hide");
                                            }
                                        }
                                    });
                                });
                            } else {
                                $("#errorModal .modal-body").html("В системе не загружены учебники данного издательства!");
                                $(".build-step3").addClass("hide");
                                $("#errorModal").modal();
                            }
                        },
                        error: function () {
                            $("#empty_list_loading").html('<div class="alert alert-danger text-center" role="alert">Ошибка в скрипте AJAX</div>');
                        }
                    });
                });

                $("#export").click(function () {
                    $.ajax({
                        url: "/include/ajax/prepare_excel_umk.php",
                        method: "POST",
                        data: {
                            "data": gridArray,
                            "book": $("#umk_book :selected").text(),
                            "izd": $("#umk_izd :selected").val()
                        },
                        cache: false,
                        async: false,
                        success: function (data) {
                            var result = $.parseJSON(data);
                            window.open("/reports/download/?t=x&f=" + result);
                        }
                    });
                })

                /*$.ajax({
                    url: "/include/ajax/show_report_fields.php",
                    method: "POST",
                    data: {"rpType": rpType},
                    cache: false,
                    async: false,
                    success: function(data) {
                        var result = $.parseJSON(data);
                        html = "";
                        $.each(result, function (id, name) {
                            html += "<div class='checkbox'>";
                            html += "<label><input type='checkbox' value='" + id + "' name='properties' ";
                            html += "headerName = '" + name + "' jqTitle='PROPERTY_" + id + "_VALUE'>" + name + "</label>";
                            html += "</div>";
                        });

                        $(".build-step1").hide(300);
                        $(".build-step2").removeClass("hidden");

                        $(".build-step2 .panel-body").html(html);

                        $("#selectAllFields").click(function () {
                            if ($(this).attr("name") === "selectAll") {
                                $("input[name=properties]").prop("checked", true);
                                $(this).prop("name", "deselectAll");
                                $(this).prop("value", "Убрать выделение");
                            } else {
                                $("input[name=properties]").prop("checked", false);
                                $(this).prop("name", "selectAll");
                                $(this).prop("value", "Выделить все");
                            }
                        });

                        $("input[name=step3]").click(function () {
                            var properties = [];
                            var headers = [];
                            $(".checkbox input:checked").each(function () {
                                properties.push(this.value);
                                headers.push({
                                    label: $(this).attr("headerName"),
                                    name: $(this).attr("jqTitle")
                                });
                            });

                            if (properties.length === 0)
                                alert("Выберите поля для отображения в отчёте!");
                            else {

                                $("#dataTable").jqGrid({
                                    colModel: headers,
                                    width: "800",
                                    height: 500,
                                    rowNum: 30,
                                    datatype: "local",
                                    pager: "#jqGridPager",
                                    shrinkToFit : false,
                                    forceFit: true
                                });

                                $.getJSON("/include/ajax/draw_report.php", {
                                    "fieldIds": properties,
                                    "rpType": rpType
                                }, function (response) {
                                    var gridArrayData = [];
                                    $.each(response, function (a, b) {
                                        gridArrayData.push(b);
                                    });

                                    $(".build-step2").hide(300);
                                    $(".build-step3").removeClass("hidden");

                                    $("#dataTable").jqGrid('setGridParam', {data: gridArrayData});
                                    $("#dataTable").trigger('reloadGrid');

                                    if (gridArrayData.length === 0) alert("Данных для отображения не найдено");

                                    $("#export").on("click", function () {
                                        $("#dataTable").jqGrid("exportToExcel", {
                                            includeLabels: true,
                                            includeGroupHeader: true,
                                            includeFooter: false,
                                            fileName: "inventoryData.xlsx"
                                        })
                                    })
                                }).fail(function (e) {
                                    console.log(e);
                                });
                            }
                        })
                    },
                    error: function () {
                        alert("Ajax Error! Свяжитесь с администратором!");
                    }
                });*/
                break;
            case "rpEmpty":
                $("input[name=selectAll]").addClass("hidden");
                $("#workPeriod").removeAttr("hidden");
                $("#typeEmpty").removeAttr("hidden");

                $(".build-step1").hide(300);
                $(".build-step2").removeClass("hidden");

                $("input[name=step3]").click(function () {
                    var workPeriod = $("#period_empty :selected").val();
                    var typeEmpty = $("#type_empty :selected").val();

                    $.ajax({
                        url: "/include/PHPExcel_ajax/make_empty_list.php",
                        method: "POST",
                        data: {"PERIOD" : workPeriod, "TYPE" : typeEmpty},
                        cache: false,
                        async: false,
                        beforeSend: function(){
                            $("#empty_list_loading").removeAttr('hidden');
                            $(".report-control").attr('disabled','disabled');
                        },
                        success: function(data){
                            var result = jQuery.parseJSON(data);
                            if (!result.error) window.open('/reports/download/?f=' + result.file);
                            $("#empty_list_loading").attr('hidden', 'hidden');
                            $(".report-control").removeAttr('disabled');
                        },
                        error: function(){
                            $("#empty_list_loading").html('<div class="alert alert-danger text-center" role="alert">Ошибка в скрипте AJAX</div>');
                        }
                    });
                });
                break;
            case "rpBudget":
                $(".build-step1").hide(300);
                $(".build-step2").removeClass("hidden");
                $("input[name=selectAll]").addClass("hidden");

                $("#budgetIzd").css("display", "block");

                $("input[name=step3]").click(function () {
                    var BDG_IZD = $("#bdg_izd :selected").val();
                    var BDG_GROUP = $("#bdg_group :selected").val();

                    $.ajax({
                        url: "/include/ajax/budget_info.php",
                        method: "POST",
                        data: {"BDG_IZD" : BDG_IZD, "BDG_GROUP" : BDG_GROUP},
                        cache: false,
                        async: false,
                        beforeSend: function(){
                            //$("#empty_list_loading").removeAttr('hidden');
                            //$(".report-control").attr('disabled','disabled');
                        },
                        success: function(data){
                            var result = $.parseJSON(data);

                            $(".build-step3").removeClass("hide");
                            $("#summTable").removeClass("hide");
                            $("#empty_list_loading").css("display", "none");

                            switch (BDG_GROUP) {
                                case "1":
                                    var html = "";
                                    var total = 0;
                                    html += "<tr>";
                                    html += "<td><b>Муниципалитет</b></td>";
                                    html += "<td><b>Сумма (руб.)</b></td>";
                                    html += "</tr>";
                                    $.each(result, function (name, sum) {
                                        total += sum;
                                        html += "<tr>";
                                        html += "<td>" + name + "</td>";
                                        html += "<td>" + sum + "</td>";
                                        html += "</tr>";
                                    });

                                    html += "<tr>";
                                    html += "<td><b>Итого: </b></td>";
                                    html += "<td><b>" + total + "</b></td>";
                                    html += "</tr>";
                                    $("#summTable").empty().append(html);
                                    $("#rpBudgetTitle").html("Суммарная стоимость заказов по муниципальным образованиям. Издательство: " + $("#bdg_izd :selected").text());
                                    break;
                                case "2":
                                    var html = "";
                                    html += "<tr>";
                                    html += "<td><b>Муниципалитет</b></td>";
                                    html += "<td><b>Образовательная организация</b></td>";
                                    html += "<td><b>Сумма (руб.)</b></td>";
                                    html += "</tr>";
                                    
                                    $.each(result, function (name, schools) {
                                        $.each(schools, function (sname, info) {
                                            html += "<tr>";
                                            html += "<td>" + name + "</td>";
                                            if (sname !== "Нет данных")
                                                html += "<td><a href='school_" + info["SCHOOL_ID"] + "'>" + sname + "</a></td>";
                                            else
                                                html += "<td>" + sname + "</td>";

                                            html += "<td>" + ~~parseFloat(info["COST"]) + "</td>";
                                            html += "</tr>";
                                        })
                                    });

                                    $("#summTable").empty().append(html);
                                    $("#rpBudgetTitle").html("Суммарная стоимость заказов по муниципальным образованиям. Издательство: " + $("#bdg_izd :selected").text());

                                    var bookHtml = "";
                                    bookHtml = "<tr>";
                                    bookHtml += "<td><b>Название ученика</b></td>";
                                    bookHtml += "<td><b>Количество экземпляров</b></td>";
                                    bookHtml += "<td><b>Цена учебника</b></td>";
                                    bookHtml += "<td><b>Общая стоимость</b></td>";
                                    bookHtml += "</tr>";

                                    $.each(result, function (name, schools) {
                                        $.each(schools, function (sname, info) {
                                            if (info["BOOKS"].length > 0)
                                                $.each(info["BOOKS"], function (a, bookInfo) {
                                                    bookHtml += "<tr class='book_" + bookInfo["SCHOOL_ID"] + "'>";
                                                    bookHtml += "<td>" + bookInfo["AUTHOR"] + ", " + bookInfo["TITLE"] + ", " + bookInfo["CLASS"] + "</td>";
                                                    bookHtml += "<td>" + bookInfo["COUNT"] + "</td>";
                                                    bookHtml += "<td>" + bookInfo["PRICE"] + "</td>";
                                                    bookHtml += "<td>" + (bookInfo["PRICE"] * bookInfo["COUNT"]) + "</td>";
                                                    bookHtml += "</tr>";
                                                });
                                        });
                                    });

                                    $("#bookInfoTable").empty().append(bookHtml);

                                    $("#summTable a").click(function (e) {
                                        e.preventDefault();
                                        var clickedSchool = $(this).attr("href").match(/\d+/)[0];

                                        $("#bookInfoTable tbody tr[class]").hide();
                                        $("tr.book_" + clickedSchool).show();

                                        $("#bookInfoModal").modal();
                                    });
                                    break;
                            }
                        },
                        error: function(){
                            $("#empty_list_loading").html('<div class="alert alert-danger text-center" role="alert">Ошибка в скрипте AJAX</div>');
                        }
                    })
                });

                break;
        }
    })
});