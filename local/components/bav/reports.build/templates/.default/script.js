/**
 * Created by revil on 07.09.17.
 */
var rpType = "";

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
                                $(".build-step2").hide(300);
                                $(".build-step3").removeClass("hidden");

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
                                    console.log(response);
                                    $.each(response, function (a, b) {
                                        gridArrayData.push(b);
                                    });
                                    //console.log(gridArrayData);
                                    $("#dataTable").jqGrid('setGridParam', {data: gridArrayData});
                                    $("#dataTable").trigger('reloadGrid');

                                    $("#export").on("click", function () {
                                        $("#dataTable").jqGrid("exportToExcel", {
                                            includeLabels: true,
                                            includeGroupHeader: true,
                                            includeFooter: false,
                                            fileName: "orderData.xlsx"
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
                });
                break;

            case "rpSvod":

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

                                    console.log(response);

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
            case "rpUMK":
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
                });
                break;
            case "rpEmpty":
                $("input[name=selectAll]").addClass("hidden");
                $("#workPeriod").removeAttr("hidden");

                $(".build-step1").hide(300);
                $(".build-step2").removeClass("hidden");

                $("input[name=step3]").click(function () {
                    var workPeriod = $("#period_empty :selected").val();

                    $.ajax({
                        url: "/include/PHPExcel_ajax/make_empty_list.php",
                        method: "POST",
                        data: {"PERIOD" : workPeriod},
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
        }
    })
});