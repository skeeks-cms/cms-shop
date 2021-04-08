(function (sx, $, _) {
    sx.classes.Import = sx.classes.Component.extend({

        _init: function () {
            this.isPaste = false;
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
                    row.append('<td class="sx-value-td sx-styled" data-cell-index="' + celsCounter + '">' + cells[x] + '</td>');
                }
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

                rowHead.append(jTh)
            }
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