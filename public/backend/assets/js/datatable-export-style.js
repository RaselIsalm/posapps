(function () {
    var THEME = "#308e87";

    function stripCell(data) {
        if (data === null || data === undefined) {
            return "";
        }
        if (typeof data !== "string") {
            return data;
        }
        var tmp = document.createElement("div");
        tmp.innerHTML = data.replace(/<br\s*\/?>/gi, "\n");
        return (tmp.textContent || tmp.innerText || "").replace(/\s+/g, " ").trim();
    }

    function softWrap(text) {
        text = String(text || "");
        return text.replace(/([^\s]{18})/g, "$1\u200b");
    }

    /** pdfMake Roboto lacks some symbols (e.g. ৳) — use default currency code/symbol safely */
    function isPdfSafeSymbol(symbol) {
        return /^[\x20-\x7E\u00A0-\u00FF€£¥¢]*$/.test(String(symbol || ""));
    }

    function pdfCurrencyLabel() {
        var currency = window.AppCurrency || {};
        var symbol = currency.symbol || "";
        if (symbol && isPdfSafeSymbol(symbol)) {
            return symbol;
        }
        return currency.code || "BDT";
    }

    function pdfSafeCurrencyText(text) {
        var currency = window.AppCurrency || {};
        var label = pdfCurrencyLabel();
        var symbol = currency.symbol || "";
        text = String(text || "");

        if (symbol) {
            text = text.split(symbol).join(label);
        }

        return text
            .replace(/\u09F3/g, label)
            .replace(/৳/g, label)
            .replace(/\u20B9/g, label)
            .replace(/₹/g, label);
    }

    function columnMeta(dt, idx) {
        var cols = dt.settings()[0].aoColumns || [];
        return cols[idx] || null;
    }

    function columnName(col) {
        if (!col) {
            return "";
        }
        var name = col.name || col.sName || col.mData || col.data || "";
        if (typeof name === "function") {
            return "";
        }
        return String(name).toLowerCase();
    }

    function columnTitle(col) {
        return stripCell(String((col && col.sTitle) || "")).toLowerCase();
    }

    function isActionOrUiColumn(dt, idx) {
        var col = columnMeta(dt, idx);
        if (!col) {
            return true;
        }
        var name = columnName(col);
        var title = columnTitle(col);
        var th = col.nTh;
        if (name === "action" || name === "checkbox") {
            return true;
        }
        if (th && th.querySelector && th.querySelector('input[type="checkbox"]')) {
            return true;
        }
        if (name === "image" || title === "image") {
            return true;
        }
        return false;
    }

    function exportColumnIndexes(dt) {
        var indexes = [];
        var count = dt.columns().count();
        for (var i = 0; i < count; i += 1) {
            var col = columnMeta(dt, i);
            if (!col || col.bVisible === false) {
                continue;
            }
            if (isActionOrUiColumn(dt, i)) {
                continue;
            }
            indexes.push(i);
        }
        return indexes;
    }

    function mergeExportOptions(config, dt) {
        var existing = config.exportOptions || {};
        var userFormat = (existing.format && existing.format.body) || null;
        return $.extend(true, {}, existing, {
            columns: exportColumnIndexes(dt),
            stripHtml: true,
            format: {
                body: function (data, row, col, node) {
                    var value = userFormat ? userFormat(data, row, col, node) : data;
                    return stripCell(value);
                },
                header: function (data, col, node) {
                    var headerFn = existing.format && existing.format.header;
                    var value = headerFn ? headerFn(data, col, node) : data;
                    return stripCell(value);
                },
                footer: function (data, col, node) {
                    var footerFn = existing.format && existing.format.footer;
                    var value = footerFn ? footerFn(data, col, node) : data;
                    return stripCell(value);
                },
            },
        });
    }

    function stylePrintWindow(win) {
        var doc = win.document;
        var style = doc.createElement("style");
        style.type = "text/css";
        style.appendChild(
            doc.createTextNode(
                "@page{size:A4 landscape;margin:10mm;}" +
                    "html,body{width:100%!important;background:#fff!important;color:#1e293b!important;font-family:Arial,Helvetica,sans-serif;font-size:11px;margin:0!important;}" +
                    "h1{font-size:16px;font-weight:700;text-align:center;margin:0 0 12px;color:#0f172a;}" +
                    "table{width:100%!important;max-width:100%!important;border-collapse:collapse!important;table-layout:fixed!important;background:#fff!important;}" +
                    "thead{display:table-header-group;}" +
                    "tfoot{display:table-footer-group;}" +
                    "thead th{background:" +
                    THEME +
                    "!important;color:#fff!important;font-weight:600;padding:6px 7px;border:1px solid #2a7a74!important;text-align:left;font-size:10px;word-break:break-word;}" +
                    "tbody td{padding:5px 7px;border:1px solid #e2e8f0!important;background:#fff!important;color:#334155!important;vertical-align:top;word-break:break-word;overflow-wrap:anywhere;}" +
                    "tbody tr:nth-child(even) td{background:#f8fafc!important;}" +
                    "tfoot th,tfoot td{font-weight:700;background:#f1f5f9!important;border:1px solid #cbd5e1!important;padding:6px 7px;}" +
                    "img,button,.btn,.dropdown,.dropdown-menu,.form-check-input,.avatar-sm,.copy-inv{display:none!important;}"
            )
        );
        doc.head.appendChild(style);
        var table = doc.querySelector("table");
        if (table) {
            table.removeAttribute("class");
            table.style.width = "100%";
        }
    }

    function cellObject(cell, fontSize) {
        var text = "";
        if (cell && typeof cell === "object") {
            text = cell.text != null ? String(cell.text) : "";
            return $.extend({}, cell, {
                text: softWrap(pdfSafeCurrencyText(text)),
                fontSize: fontSize,
                noWrap: false,
            });
        }
        return {
            text: softWrap(pdfSafeCurrencyText(cell == null ? "" : String(cell))),
            fontSize: fontSize,
            noWrap: false,
        };
    }

    function stylePdfDoc(doc) {
        doc.pageSize = "A4";
        doc.pageOrientation = "landscape";
        doc.pageMargins = [16, 24, 16, 24];

        var tableItem = null;
        (doc.content || []).forEach(function (item) {
            if (item && item.table && item.table.body && item.table.body.length) {
                tableItem = item;
            }
        });
        var cols = tableItem ? tableItem.table.body[0].length : 0;
        var fontSize = cols > 10 ? 7 : cols > 8 ? 8 : 9;

        doc.defaultStyle = $.extend({}, doc.defaultStyle || {}, {
            fontSize: fontSize,
            color: "#1e293b",
        });
        doc.styles = doc.styles || {};
        doc.styles.tableHeader = $.extend({}, doc.styles.tableHeader || {}, {
            fillColor: THEME,
            color: "#ffffff",
            bold: true,
            fontSize: fontSize,
            alignment: "left",
        });
        doc.styles.tableBodyEven = { fontSize: fontSize };
        doc.styles.tableBodyOdd = { fontSize: fontSize };
        doc.styles.tableFooter = $.extend({}, doc.styles.tableFooter || {}, {
            fontSize: fontSize,
            bold: true,
        });
        doc.styles.title = $.extend({}, doc.styles.title || {}, {
            fontSize: 14,
            bold: true,
            color: "#0f172a",
            alignment: "center",
            margin: [0, 0, 0, 10],
        });

        (doc.content || []).forEach(function (item) {
            if (!item.table || !item.table.body || !item.table.body.length) {
                return;
            }
            var colCount = item.table.body[0].length;
            item.table.widths = item.table.body[0].map(function (cell, w) {
                var headerText = "";
                if (cell && typeof cell === "object") {
                    headerText = String(cell.text || "");
                } else {
                    headerText = String(cell == null ? "" : cell);
                }
                headerText = headerText.replace(/\u200b/g, "").trim();
                if (w === 0 && (headerText === "#" || headerText === "SL")) {
                    return 22;
                }
                return "*";
            });
            item.table.headerRows = item.table.headerRows || 1;
            item.width = "100%";
            item.table.body = item.table.body.map(function (row, rowIndex) {
                return row.map(function (cell) {
                    var obj = cellObject(cell, rowIndex === 0 ? fontSize : fontSize);
                    if (rowIndex === 0) {
                        obj.bold = true;
                        obj.color = "#ffffff";
                        obj.fillColor = THEME;
                    }
                    return obj;
                });
            });
            item.layout = {
                hLineWidth: function () {
                    return 0.4;
                },
                vLineWidth: function () {
                    return 0.4;
                },
                hLineColor: function (i) {
                    return i === 0 || i === 1 ? "#2a7a74" : "#e2e8f0";
                },
                vLineColor: function () {
                    return "#e2e8f0";
                },
                fillColor: function (rowIndex) {
                    if (rowIndex === 0) {
                        return THEME;
                    }
                    return rowIndex % 2 === 0 ? "#f8fafc" : "#ffffff";
                },
                paddingLeft: function () {
                    return 4;
                },
                paddingRight: function () {
                    return 4;
                },
                paddingTop: function () {
                    return 4;
                },
                paddingBottom: function () {
                    return 4;
                },
            };
        });
    }

    function wrappedAction(origAction, afterCustomize) {
        return function (e, dt, node, config) {
            config.orientation = "landscape";
            config.pageSize = "A4";
            config.exportOptions = mergeExportOptions(config, dt);
            var userCustomize = config.customize;
            config.customize = function (docOrWin) {
                if (typeof userCustomize === "function") {
                    userCustomize(docOrWin);
                }
                if (typeof afterCustomize === "function") {
                    afterCustomize(docOrWin);
                }
            };
            return origAction.call(this, e, dt, node, config);
        };
    }

    function buttonKind(conf) {
        if (!conf) {
            return "";
        }
        var extend = String(conf.extend || "").toLowerCase();
        var fileExt = String(conf.extension || "").toLowerCase();
        var cls = String(conf.className || "").toLowerCase();
        if (extend.indexOf("pdf") !== -1 || fileExt === ".pdf" || cls.indexOf("buttons-pdf") !== -1) {
            return "pdf";
        }
        if (extend.indexOf("print") !== -1 || cls.indexOf("buttons-print") !== -1 || conf.autoPrint === true) {
            return "print";
        }
        if (extend.indexOf("excel") !== -1 || extend.indexOf("csv") !== -1 || fileExt === ".xlsx" || fileExt === ".csv") {
            return "sheet";
        }
        return "";
    }

    function patchConf(dt, conf) {
        if (!conf || conf._posExportPatched) {
            return;
        }
        var kind = buttonKind(conf);
        if (!kind) {
            return;
        }
        var orig = conf.action;
        if (typeof orig !== "function") {
            return;
        }
        conf.orientation = "landscape";
        conf.pageSize = "A4";
        conf.action = wrappedAction(orig, kind === "pdf" ? stylePdfDoc : kind === "print" ? stylePrintWindow : null);
        conf._posExportPatched = true;
    }

    function walkButtons(list, dt) {
        (list || []).forEach(function (btn) {
            if (!btn) {
                return;
            }
            if (btn.conf) {
                patchConf(dt, btn.conf);
                walkButtons(btn.buttons, dt);
                return;
            }
            patchConf(dt, btn);
        });
    }

    function eachInstanceConf(dt, cb) {
        var settings = dt.settings()[0];
        var groups = settings._buttons || [];
        groups.forEach(function (group) {
            var inst = group.inst || group;
            walkButtons((inst.s && inst.s.buttons) || [], dt);
            if (typeof cb === "function") {
                cb(inst);
            }
        });
    }

    function patchTable(dt) {
        try {
            eachInstanceConf(dt);
        } catch (e) {
            /* ignore version differences */
        }
    }

    function patchExistingTables() {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable || !jQuery.fn.dataTable.tables) {
            return;
        }
        var tables = jQuery.fn.dataTable.tables();
        for (var i = 0; i < tables.length; i += 1) {
            if (!jQuery.fn.DataTable.isDataTable(tables[i])) {
                continue;
            }
            patchTable(jQuery(tables[i]).DataTable());
        }
    }

    function hookPdfMake() {
        if (!window.pdfMake || typeof pdfMake.createPdf !== "function" || pdfMake._posExportHooked) {
            return !!(window.pdfMake && pdfMake._posExportHooked);
        }
        var origCreatePdf = pdfMake.createPdf;
        pdfMake.createPdf = function (docDefinition) {
            try {
                if (docDefinition && docDefinition.content) {
                    stylePdfDoc(docDefinition);
                }
            } catch (err) {
                /* keep original document if styling fails */
            }
            return origCreatePdf.apply(this, arguments);
        };
        pdfMake._posExportHooked = true;
        return true;
    }

    function wrapPrototype(name, afterCustomize) {
        var btn = jQuery.fn.dataTable.ext.buttons[name];
        if (!btn || typeof btn.action !== "function" || btn._posExportStyled) {
            return;
        }
        btn.action = wrappedAction(btn.action, afterCustomize);
        btn.orientation = "landscape";
        btn.pageSize = "A4";
        btn._posExportStyled = true;
    }

    function boot() {
        if (
            !window.jQuery ||
            !jQuery.fn ||
            !jQuery.fn.dataTable ||
            !jQuery.fn.dataTable.ext ||
            !jQuery.fn.dataTable.ext.buttons
        ) {
            return false;
        }
        wrapPrototype("print", stylePrintWindow);
        wrapPrototype("pdfHtml5", stylePdfDoc);
        wrapPrototype("pdf", stylePdfDoc);
        wrapPrototype("excelHtml5", null);
        wrapPrototype("excel", null);
        wrapPrototype("csvHtml5", null);
        wrapPrototype("csv", null);
        hookPdfMake();
        patchExistingTables();
        [200, 800, 2000].forEach(function (ms) {
            setTimeout(function () {
                hookPdfMake();
                patchExistingTables();
            }, ms);
        });
        return true;
    }

    if (window.jQuery) {
        jQuery(document).on("init.dt", function (e, settings) {
            if (e.namespace !== "dt") {
                return;
            }
            patchTable(new jQuery.fn.dataTable.Api(settings));
        });
    }

    if (!boot()) {
        var tries = 0;
        var timer = setInterval(function () {
            tries += 1;
            if (boot() || tries > 80) {
                clearInterval(timer);
            }
        }, 100);
    }
})();
