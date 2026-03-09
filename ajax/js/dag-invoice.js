jQuery(document).ready(function () {

    let selectedCustomerId = null;

    // ==========================================
    // CUSTOMER SEARCH MODAL
    // ==========================================
    $("#searchCustomerBtn").click(function () {
        let keyword = $("#customerSearchInput").val();

        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: { search_customer: true, keyword: keyword },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach((customer, index) => {
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${customer.code}</td>
                                <td>${customer.full_name}</td>
                                <td>${customer.dag_count}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info select-customer-btn" 
                                        data-id="${customer.id}" 
                                        data-code="${customer.code}"
                                        data-name="${customer.full_name}">
                                        Select
                                    </button>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="5" class="text-center">No customers found.</td></tr>`;
                    }
                    $("#customerSelectionTableBody").html(html);
                }
            }
        });
    });

    // Auto-search when modal opens
    $('#customerSearchModal').on('show.bs.modal', function () {
        $("#customerSearchInput").val('');
        $("#searchCustomerBtn").trigger('click');
    });

    // Handle customer selection
    $(document).on("click", ".select-customer-btn", function () {
        selectedCustomerId = $(this).data("id");
        let customerCode = $(this).data("code");
        let customerName = $(this).data("name");

        $("#customer_code").val(customerCode);
        $("#customer_name").val(customerName);
        $("#customer_id").val(selectedCustomerId);

        loadCustomerDags(selectedCustomerId, 0);
        $("#customerSearchModal").modal("hide");
    });

    // ==========================================
    // LOAD CUSTOMER DAG ITEMS (with company + cost columns)
    // ==========================================
    function loadCustomerDags(customerId, invoiced) {
        let postData = { get_customer_dags: true, customer_id: customerId };
        if (invoiced !== undefined && invoiced !== null) {
            postData.invoiced = invoiced;
        }
        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: postData,
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    $("#dagInvoiceItemsBody").empty();

                    if (response.data.length > 0) {
                        response.data.forEach((dag, index) => {
                            let dagNumber = dag.dag_number || 'DAG-' + String(dag.id).padStart(5, '0');
                            let cost = parseFloat(dag.cost) || 0;
                            let price = parseFloat(dag.price) || 0;
                            let discount = parseFloat(dag.discount) || 0;
                            let total = parseFloat(dag.total) || 0;
                            let companyName = dag.company_name || '-';

                            let rowHtml = `<tr data-dag-id="${dag.id}">
                                <td>${index + 1}</td>
                                <td>${dagNumber}</td>
                                <td>${dag.my_number}</td>
                                <td>${companyName}</td>
                                <td>${dag.size}</td>
                                <td>${dag.brand}</td>
                                <td>${dag.serial_no}</td>
                                <td><input type="text" class="form-control form-control-sm item-issued-date" value="${dag.issued_date || ''}" placeholder="Select" readonly></td>
                                <td><input type="number" class="form-control form-control-sm item-cost" value="${cost.toFixed(2)}" step="0.01" min="0"></td>
                                <td><input type="number" class="form-control form-control-sm item-price" value="${price.toFixed(2)}" step="0.01" min="0"></td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control form-control-sm item-discount" value="${discount.toFixed(2)}" step="0.01" min="0" max="100">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </td>
                                <td class="item-total fw-bold">${total.toFixed(2)}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-dag-row" title="Remove"><i class="uil uil-times"></i></button></td>
                            </tr>`;

                            $("#dagInvoiceItemsBody").append(rowHtml);
                        });

                        // Initialize datepickers on all issued date inputs
                        $(".item-issued-date").datepicker({
                            dateFormat: 'yy-mm-dd',
                            changeMonth: true,
                            changeYear: true
                        });

                        // Show buttons
                        $("#save").show();
                        $("#update").show();
                        $(".delete-item").show();
                        $("#print").show().attr("href", "print-dag-invoice.php?customer_id=" + customerId);
                        // Show cancel invoice only when editing invoiced items
                        if (invoiced === 1) {
                            $("#cancelInvoice").show();
                        } else {
                            $("#cancelInvoice").hide();
                        }

                        calculateTotals();
                    } else {
                        $("#dagInvoiceItemsBody").html('<tr><td colspan="13" class="text-center">No DAG items found for this customer.</td></tr>');
                    }
                }
            }
        });
    }

    // ==========================================
    // REMOVE DAG ROW
    // ==========================================
    $(document).on("click", ".remove-dag-row", function () {
        $(this).closest("tr").remove();
        // Renumber rows
        $("#dagInvoiceItemsBody tr").each(function (i) {
            $(this).find("td:first").text(i + 1);
        });
        calculateTotals();
    });

    // ==========================================
    // CALCULATE ITEM TOTAL (discount as %)
    // ==========================================
    $(document).on("input", ".item-price, .item-discount", function () {
        let row = $(this).closest("tr");
        let price = parseFloat(row.find(".item-price").val()) || 0;
        let discountPct = parseFloat(row.find(".item-discount").val()) || 0;
        if (discountPct > 100) discountPct = 100;
        if (discountPct < 0) discountPct = 0;

        let discountAmount = price * discountPct / 100;
        let total = price - discountAmount;
        if (total < 0) total = 0;
        row.find(".item-total").text(total.toFixed(2));
        calculateTotals();
    });

    // ==========================================
    // CALCULATE GRAND TOTALS
    // ==========================================
    function calculateTotals() {
        let subTotal = 0;
        let discountTotal = 0;
        let grandTotal = 0;

        $("#dagInvoiceItemsBody tr").each(function () {
            let price = parseFloat($(this).find(".item-price").val()) || 0;
            let discountPct = parseFloat($(this).find(".item-discount").val()) || 0;
            let discountAmount = price * discountPct / 100;
            let total = price - discountAmount;
            if (total < 0) total = 0;

            subTotal += price;
            discountTotal += discountAmount;
            grandTotal += total;
        });

        $("#subTotal").val(subTotal.toFixed(2));
        $("#disTotal").val(discountTotal.toFixed(2));
        $("#grandTotal").val(grandTotal.toFixed(2));
    }

    // ==========================================
    // SAVE / UPDATE INVOICE
    // ==========================================
    function saveInvoice() {
        if (!selectedCustomerId) {
            swal({ title: "Error!", text: "Please select a customer first.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        let items = [];
        let hasItems = false;

        $("#dagInvoiceItemsBody tr").each(function () {
            let dagId = $(this).data("dag-id");
            if (dagId) {
                hasItems = true;
                let price = parseFloat($(this).find(".item-price").val()) || 0;
                let discountPct = parseFloat($(this).find(".item-discount").val()) || 0;
                let discountAmount = price * discountPct / 100;
                let total = price - discountAmount;
                if (total < 0) total = 0;

                items.push({
                    dag_id: dagId,
                    cost: parseFloat($(this).find(".item-cost").val()) || 0,
                    price: price,
                    discount: discountPct,
                    total: total,
                    issued_date: $(this).find(".item-issued-date").val() || ''
                });
            }
        });

        if (!hasItems) {
            swal({ title: "Error!", text: "No DAG items to save.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        $(".someBlock").preloader();

        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: { save_invoice: true, items: JSON.stringify(items) },
            dataType: "json",
            success: function (result) {
                $(".someBlock").preloader("remove");
                if (result.status === 'success') {
                    swal({
                        title: "Success!",
                        text: "DAG Invoice Saved Successfully",
                        type: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }, function () {
                        window.location.reload();
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: result.message || "Error saving data",
                        type: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function () {
                $(".someBlock").preloader("remove");
                swal({ title: "Error!", text: "Server error occurred.", type: "error", timer: 2000, showConfirmButton: false });
            }
        });
    }

    $("#save").click(function (e) { e.preventDefault(); saveInvoice(); });
    $("#update").click(function (e) { e.preventDefault(); saveInvoice(); });

    // ==========================================
    // DELETE INVOICE
    // ==========================================
    $(".delete-item").click(function (e) {
        e.preventDefault();

        if (!selectedCustomerId) {
            swal({ title: "Error!", text: "No invoice selected.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        swal({
            title: "Are you sure?",
            text: "This will clear all invoice data for this customer's DAG items.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }, function (isConfirm) {
            if (isConfirm) {
                $(".someBlock").preloader();

                $.ajax({
                    url: "ajax/php/dag-invoice.php",
                    type: "POST",
                    data: { delete_invoice: true, customer_id: selectedCustomerId },
                    dataType: "json",
                    success: function (result) {
                        $(".someBlock").preloader("remove");
                        if (result.status === 'success') {
                            swal({
                                title: "Deleted!",
                                text: "Invoice deleted successfully.",
                                type: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            // Reload items to show cleared data
                            loadCustomerDags(selectedCustomerId);
                        } else {
                            swal({ title: "Error!", text: result.message || "Failed to delete.", type: "error", timer: 2000, showConfirmButton: false });
                        }
                    },
                    error: function () {
                        $(".someBlock").preloader("remove");
                        swal({ title: "Error!", text: "Server error.", type: "error", timer: 2000, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // ==========================================
    // DAG INVOICE SEARCH MODAL (only invoiced DAGs)
    // ==========================================
    $("#searchDagInvoiceBtn").click(function () {
        let keyword = $("#dagInvoiceSearchInput").val();

        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: { search_dag_invoice: true, keyword: keyword },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    let html = '';
                    if (response.data.length > 0) {
                        // Group by customer AND cancellation status
                        let grouped = {};
                        response.data.forEach(dag => {
                            let isCancelled = parseInt(dag.is_cancelled) === 1;
                            let groupKey = dag.customer_id + '_' + (isCancelled ? 'cancelled' : 'active');

                            if (!grouped[groupKey]) {
                                grouped[groupKey] = {
                                    customer_name: trim_name(dag.customer_name, dag.customer_name_2),
                                    customer_code: dag.customer_code,
                                    customer_id: dag.customer_id,
                                    dags: [],
                                    totalAmount: 0,
                                    dagNumbers: [],
                                    serialNos: [],
                                    is_cancelled: isCancelled
                                };
                            }
                            grouped[groupKey].dags.push(dag);
                            grouped[groupKey].totalAmount += parseFloat(dag.total) || 0;
                            let dagNum = dag.dag_number || 'DAG-' + String(dag.id).padStart(5, '0');
                            grouped[groupKey].dagNumbers.push(dagNum);
                            if (dag.serial_no) {
                                grouped[groupKey].serialNos.push(dag.serial_no);
                            }
                        });

                        let counter = 1;
                        for (let groupKey in grouped) {
                            let group = grouped[groupKey];
                            let cancelledBadge = group.is_cancelled ? ' <span class="badge bg-danger">Cancelled</span>' : '';
                            let actionButtons = '';
                            if (!group.is_cancelled) {
                                actionButtons = `
                                    <button type="button" class="btn btn-sm btn-info load-customer-invoice-btn me-1" 
                                        data-customer-id="${group.customer_id}"
                                        data-customer-code="${group.customer_code}"
                                        data-customer-name="${group.customer_name}"
                                        title="Edit">
                                        <i class="uil uil-edit"></i>
                                    </button>
                                    <a href="print-dag-invoice.php?customer_id=${group.customer_id}" 
                                       target="_blank" class="btn btn-sm btn-secondary me-1" title="Print">
                                        <i class="uil uil-print"></i>
                                    </a>`;
                            }
                            html += `<tr class="${group.is_cancelled ? 'table-secondary' : ''}">
                                <td>${counter}</td>
                                <td>${group.customer_name}${cancelledBadge}</td>
                                <td><small>${group.dagNumbers.join(', ')}</small></td>
                                <td><small>${group.serialNos.join(', ')}</small></td>
                                <td>${group.dags.length}</td>
                                <td class="text-end">${group.totalAmount.toFixed(2)}</td>
                                <td>${actionButtons}</td>
                            </tr>`;
                            counter++;
                        }
                    } else {
                        html = `<tr><td colspan="7" class="text-center">No invoiced records found.</td></tr>`;
                    }
                    $("#dagInvoiceSelectionTableBody").html(html);
                }
            }
        });
    });

    // Auto-search when modal opens
    $('#dagInvoiceSearchModal').on('show.bs.modal', function () {
        $("#dagInvoiceSearchInput").val('');
        $("#searchDagInvoiceBtn").trigger('click');
    });

    // Handle loading a customer's invoice from search (Edit)
    $(document).on("click", ".load-customer-invoice-btn", function () {
        let customerId = $(this).data("customer-id");
        let customerCode = $(this).data("customer-code");
        let customerName = $(this).data("customer-name");

        selectedCustomerId = customerId;
        $("#customer_code").val(customerCode);
        $("#customer_name").val(customerName);
        $("#customer_id").val(customerId);

        loadCustomerDags(customerId, 1);
        $("#dagInvoiceSearchModal").modal("hide");
    });

    // Handle delete from invoice list modal
    $(document).on("click", ".delete-invoice-from-list-btn", function () {
        let customerId = $(this).data("customer-id");
        let customerName = $(this).data("customer-name");

        swal({
            title: "Delete Invoice?",
            text: "Delete invoice for " + customerName + "?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, delete!"
        }, function (isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: "ajax/php/dag-invoice.php",
                    type: "POST",
                    data: { delete_invoice: true, customer_id: customerId },
                    dataType: "json",
                    success: function (result) {
                        if (result.status === 'success') {
                            swal({ title: "Deleted!", text: "Invoice deleted.", type: "success", timer: 1500, showConfirmButton: false });
                            // Refresh the search results
                            $("#searchDagInvoiceBtn").trigger('click');
                            // If this was the currently loaded customer, clear it
                            if (selectedCustomerId == customerId) {
                                $('#new').trigger('click');
                            }
                        } else {
                            swal({ title: "Error!", text: "Failed to delete.", type: "error", timer: 2000, showConfirmButton: false });
                        }
                    }
                });
            }
        });
    });

    // ==========================================
    // NEW BUTTON RESET
    // ==========================================
    $('#new').click(function (e) {
        e.preventDefault();
        selectedCustomerId = null;
        $("#customer_code").val('');
        $("#customer_name").val('');
        $("#customer_id").val('');
        $("#dagInvoiceItemsBody").empty();
        $("#subTotal").val('0.00');
        $("#disTotal").val('0.00');
        $("#grandTotal").val('0.00');
        $("#save").show();
        $("#update").hide();
        $(".delete-item").hide();
        $("#print").hide();
        $("#cancelInvoice").hide();
    });

    // ==========================================
    // CANCEL INVOICE
    // ==========================================
    $("#cancelInvoice").click(function (e) {
        e.preventDefault();

        if (!selectedCustomerId) {
            swal({ title: "Error!", text: "No invoice selected.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        swal({
            title: "Cancel Invoice?",
            text: "This will cancel the invoice and reset all pricing data for this customer's DAG items.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, cancel it!"
        }, function (isConfirm) {
            if (isConfirm) {
                $(".someBlock").preloader();

                $.ajax({
                    url: "ajax/php/dag-invoice.php",
                    type: "POST",
                    data: { cancel_invoice: true, customer_id: selectedCustomerId },
                    dataType: "json",
                    success: function (result) {
                        $(".someBlock").preloader("remove");
                        if (result.status === 'success') {
                            swal({
                                title: "Cancelled!",
                                text: "Invoice has been cancelled successfully.",
                                type: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            // Reset form
                            $('#new').trigger('click');
                        } else {
                            swal({ title: "Error!", text: result.message || "Failed to cancel.", type: "error", timer: 2000, showConfirmButton: false });
                        }
                    },
                    error: function () {
                        $(".someBlock").preloader("remove");
                        swal({ title: "Error!", text: "Server error.", type: "error", timer: 2000, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // Helper
    function trim_name(name, name2) {
        return (name + ' ' + (name2 || '')).trim();
    }

});
