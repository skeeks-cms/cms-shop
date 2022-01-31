(function (sx, $, _) {

    sx.classes.ImportProgressBar = sx.classes.tasks.ProgressBar.extend({

        _init: function()
        {
            var self = this;
            this.applyParentMethod(sx.classes.tasks.ProgressBar, '_init', []);

            this.bind('update', function(e, data)
            {
                $(".sx-executing-ptc", self.getWrapper()).empty().append(self.getExecutedPtc());
            });
        }
    });

    sx.classes.Import = sx.classes.Component.extend({

        _init: function () {
            this.isPaste = false;

            this.TaskManager = new sx.classes.tasks.Manager({
                'tasks' : [],
                'delayQueque' : this.get('delayQueque', 10)
            });

            this.ProgressBar = new sx.classes.ImportProgressBar(this.TaskManager, "#sx-progress-tasks");

            /**
             * Данные задачи
             */
            this.Task = new sx.classes.Entity();
        },

        _onDomReady: function () {

            var self = this;

            $("body").on("click", function () {
                if (self.isPaste === false) {
                    $("#sx-source").focus();
                }
            });

            //Удаление строки в таблице
            $("body").on("click", ".sx-remove-tr-btn", function () {
                $(this).closest("tr").remove();
                return false;
            });

            $("body").on("click", ".sx-value-td", function () {
                $(this).closest("table").find("tr").removeClass("sx-active-tr");
                $(this).closest("tr").addClass("sx-active-tr")
                return false;
            });

            $("body").on("click", ".sx-start-import", function () {
                self.startImport();
                return false;
            });

            $("body").on("dblclick", ".sx-value-td", function () {
                var text = $(this).text();
                var jText = $("<textarea>").append(text);
                $(this).append(jText);
                jText.focus();

                jText.on("focusout", function() {
                    var newText = $(this).val();
                    var jTd = $(this).closest("td");
                    $(this).remove();
                    jTd.empty().append(newText);
                });

                return false;
            });

            $("#sx-source").focus();

            $("#sx-source").on("paste", function () {
                setTimeout(function() {
                    self.generateTable();
                }, 300);
            });
        },

        updateMaxHeight: function() {
            var windowHeight = window.innerHeight;
            var top = $(".sx-max-height-100").offset().top;

            $(".sx-max-height-100").css("max-height", windowHeight - top - 80);

            //$.HSCore.components.HSScrollBar.init($('.sx-max-height-100'));
        },

        /**
         * Начало импорта
         */
        startImport: function() {
            var self = this;
            var matches = [];

            _.each(self.getMatches(), function(value, key) {
                matches.push(value);
            });

            //Проверка на заполнение соответствия
            var hasError = false;
            _.each(self.get("required_matches"), function (required_value) {
                if (matches.indexOf(required_value) == -1) {
                    //ошибка не выбрано все соответствие
                    var reqText = $("#sx-base-matches:eq(0) option[value=" + required_value + "]").text();
                    sx.notify.error('Не проставлено соответствие для: "' + reqText + '"');
                    hasError = true;
                }
            });

            if (hasError) {
                return false;
            }


            var tasks = [];
            var matches = self.getMatches();

            $(".sx-import-table-wrapper tbody tr").each(function() {

                $(this).data("row-index");
                var jTr = $(this);

                var rowData = {};

                $("td", $(this)).each(function() {
                    if ($(this).data("cell-index") >= 1 ) {
                        rowData[$(this).data("cell-index")] = $(this).text();
                    }
                });

                var ajaxQuery = sx.ajax.preparePostQuery(self.get('backend_element'), {
                    'index': $(this).data("row-index"),
                    'row_data': rowData,
                    'matches': matches,
                });

                var AjaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
                    allowResponseSuccessMessage: false,
                    allowResponseErrorMessage: false,
                });

                AjaxHandler.on("success", function(e, data) {
                    jTr.addClass("sx-success-tr");
                    $(".sx-result-td", jTr).empty().append(data.message);
                });
                AjaxHandler.on("error", function(e, data) {
                    jTr.addClass("sx-error-tr");
                    $(".sx-result-td", jTr).empty().append(data.message);

                });

                /*ajaxQuery.onSuccess(function(e, data)
                {
                    console.log(data);
                    jTr.addClass("sx-success-tr");
                    $(".sx-result-td", jTr).empty().append(data.response.message);
                });

                ajaxQuery.onError(function(e, data)
                {
                    console.log(data);
                    jTr.addClass("sx-error-tr");
                    var errorText = '';
                    if (data.response) {
                        errorText = '';
                    } else {
                        errorText = data.textStatus
                    }
                    $(".sx-result-td", jTr).empty().append(errorText);
                });*/

                var Task = new sx.classes.tasks.AjaxTask(ajaxQuery);

                tasks.push(Task);

            });

            this.TaskManager.setTasks(tasks);
            this.TaskManager.start();

            return this;
        },

        getMatches: function() {

            var result = {};

            $(".sx-import-table-wrapper th").each(function() {

                if ($(this).data("cell-index") >= 1 ) {
                    result[$(this).data("cell-index")] = $("select", $(this)).val();
                }

            });

            return result;
        },

        generateTable: function() {
            var self = this;

            var data = $("#sx-source").val();
            var rows = data.split("\n");

            var table = $('<table />');
            var tableBody = $('<tbody />');
            var tableHead = $('<thead />');
            var counter = 0;
            var maxCountCells = 0;

            for (var y in rows) {
                counter = counter + 1;
                var celsCounter = 0;

                var cells = rows[y].split("\t");
                var row = $('<tr />', {
                    'data-row-index' : counter
                });
                row.append('<td class="sx-remove-td"><div class="sx-remove-tr-btn" title="Удалить строку"><i class="hs-icon hs-icon-close"></i></div></td>');
                row.append('<td class="sx-counter-td sx-styled">' + counter + '</td>');
                for (var x in cells) {
                    celsCounter = celsCounter + 1;
                    if (celsCounter > maxCountCells) {
                        maxCountCells = celsCounter;
                    }
                    row.append('<td class="sx-value-td sx-styled" data-cell-index="' + celsCounter + '">' + cells[x].trim() + '</td>');
                }
                row.append('<td class="sx-result-td sx-styled"></td>');
                tableBody.append(row);
            }

            var rowHead = $('<tr />');

            for (var i = 0; i < maxCountCells + 2; i++) {
               // ещё какие-то выражения
                var realI = (i - 1);
                var select = '';
                var classCss = '';
                if (realI > 0) {
                    select = '- выбрать -';
                    select = $("#sx-base-matches").clone();
                    classCss = 'sx-styled';
                }
                var jTh = $("<th>", {
                    'class' : classCss,
                    'data-cell-index' : realI,
                }).append(select);

                rowHead.append(jTh);
            }


            rowHead.append($("<th>", {
                'class' : 'sx-styled sx-result-th',
            }).append("Результат"));

            tableHead.append(rowHead);


            table.append(tableHead).append(tableBody);

            $('.sx-import-table-wrapper').html(table);

            this.isPaste = true;

            $(".sx-info").hide();
            $(".sx-import-values-wrapper").show();

            setTimeout(function() {
                self.updateMaxHeight();
            }, 200);
        }

    });
})(sx, sx.$, sx._);