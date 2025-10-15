jQuery(document).ready(function ($) {
  // Configuration constants
  const CONFIG = {
    CHEQUE_NO_REGEX: /^\d{6,12}$/,
    MIN_AMOUNT: 0.01,
    SWAL_TIMEOUT: 3000,
  };

  // Centralized state management
  const state = {
    chequeInfo: [],
    totalUsed: 0,
    totalAvailable: 0,
    cashTotal: 0,
    cashBalance: 0,
    totalOutstanding: 0,
  };

  // Utility functions
  const formatAmount = (amount) =>
    parseFloat(amount || 0).toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

  const parseAmount = (value) => {
    const num = parseFloat(value.toString().replace(/,/g, ""));
    return isNaN(num) ? 0 : num;
  };

  const debounce = (func, wait) => {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  };

  const isValidChequeNo = (chequeNo) => CONFIG.CHEQUE_NO_REGEX.test(chequeNo);

  const isValidDate = (dateStr) => {
    const today = new Date().setHours(0, 0, 0, 0);
    const inputDate = new Date(dateStr);
    return !isNaN(inputDate.getTime()) && inputDate >= today;
  };

  // State and UI updates
  const updateState = ($excludeRow = null) => {
    state.totalUsed = 0;
    state.totalAvailable = 0;
    let totalPaid = 0;
    let totalBalance = 0;
    let totalChequeAmount = 0;

    // Update cheque usage
    state.chequeInfo.forEach((cheque) => {
      const usedAmount = calculateChequeUsedAmount(cheque.id, $excludeRow);
      cheque.usedAmount = usedAmount;
      cheque.remaining = Math.max(0, cheque.amount - usedAmount);
      cheque.used = cheque.remaining <= CONFIG.MIN_AMOUNT;
      state.totalUsed += usedAmount;
      state.totalAvailable += cheque.remaining;
      totalChequeAmount += cheque.amount;
    });

    // Update cash and totals
    state.cashTotal = parseAmount($("#cash_total").val());
    state.cashBalance = state.cashTotal - calculateTotalCashPay($excludeRow);

    // Calculate totals
    state.totalOutstanding = 0;
    $("#invoiceBody tr")
      .not("#noItemRow")
      .each(function () {
        const $row = $(this);
        const overdue = parseAmount($row.find(".invoice-overdue").text());
        const chequePay = parseAmount($row.find(".cheque-pay").val());
        const cashPay = parseAmount($row.find(".cash-pay").val());
        const paidAmount = chequePay + cashPay;
        const balance = overdue - paidAmount;

        state.totalOutstanding += overdue;
        totalPaid += paidAmount;
        totalBalance += balance;
        updateRowBalance($row);
      });

    // Update UI
    $("#total_outstanding, #outstanding").val(formatAmount(state.totalOutstanding));
    $("#paid_amount").val(formatAmount(totalPaid));
    $("#balance_amount").val(formatAmount(totalBalance));
    $("#cheque_balance").val(formatAmount(state.totalAvailable));
    $("#cash_balance").val(formatAmount(state.cashBalance));
    $("#cheque_total").val(formatAmount(totalChequeAmount));

    updateChequeDropdowns();
    updateChequePayDisabledState();
  };

  const calculateChequeUsedAmount = (chequeId, $excludeRow) =>
    $(".cheque-select").get().reduce((sum, select) => {
      const $select = $(select);
      const $row = $select.closest("tr");
      return $select.val() === chequeId && (!$excludeRow || !$row.is($excludeRow))
        ? sum + parseAmount($row.find(".cheque-pay").val())
        : sum;
    }, 0);

  const calculateTotalCashPay = ($excludeRow = null) =>
    $(".cash-pay").get().reduce((sum, input) => {
      const $row = $(input).closest("tr");
      return (!$excludeRow || !$row.is($excludeRow))
        ? sum + parseAmount($(input).val())
        : sum;
    }, 0);

  const updateChequeDropdowns = () => {
    $(".cheque-select").each(function () {
      const $select = $(this);
      const selectedValue = $select.val();
      const $row = $select.closest("tr");

      // Update existing options
      $select.find("option").not(":first").remove();
      
      // Add new options
      state.chequeInfo.forEach((cheque) => {
        if (cheque.remaining > CONFIG.MIN_AMOUNT || cheque.id === selectedValue) {
          const isSelected = cheque.id === selectedValue;
          const displayAmount = isSelected
            ? formatAmount(cheque.amount)
            : `${formatAmount(cheque.remaining)} of ${formatAmount(cheque.amount)}`;
          $select.append(
            $("<option>", {
              value: cheque.id,
              "data-amount": cheque.remaining,
              disabled: cheque.used && !isSelected,
              text: `${cheque.chequeNo} (${displayAmount})`,
            })
          );
        }
      });

      // Validate selected value
      if (!$select.find(`option[value="${selectedValue}"]`).length) {
        $select.val("");
        $row.find(".cheque-pay").val("0.00");
      }
    });
  };

  const updateRowBalance = ($row) => {
    const overdue = parseAmount($row.find(".invoice-overdue").text());
    const chequePay = parseAmount($row.find(".cheque-pay").val());
    const cashPay = parseAmount($row.find(".cash-pay").val());
    const paidAmount = chequePay + cashPay;
    $row.find(".paid-amount").text(formatAmount(paidAmount));
    $row.find(".balance-amount").text(formatAmount(overdue - paidAmount));
  };

  const updateChequePayDisabledState = () => {
    $(".cheque-select").each(function () {
      const $row = $(this).closest("tr");
      $row.find(".cheque-pay").prop("disabled", !$(this).val());
    });
  };

  const validatePayment = ($input, $row, type) => {
    let inputVal = parseAmount($input.val());
    const overdue = parseAmount($row.find(".invoice-overdue").text());
    
    if (type === "cheque") {
      const selectedChequeId = $row.find(".cheque-select").val();
      if (!selectedChequeId) {
        inputVal = 0;
      } else {
        const selectedCheque = state.chequeInfo.find((c) => c.id === selectedChequeId);
        const usedAmount = calculateChequeUsedAmount(selectedChequeId, $row);
        const remainingBalance = selectedCheque.amount - usedAmount;
        inputVal = Math.min(inputVal, overdue, remainingBalance);
      }
    } else {
      const usedCash = calculateTotalCashPay($row);
      const remainingCashBalance = state.cashTotal - usedCash;
      inputVal = Math.min(inputVal, overdue, remainingCashBalance);
    }

    if (inputVal !== parseAmount($input.val())) {
      $input.val(formatAmount(inputVal));
      const limitType = type === "cheque" ? "Cheque" : "Cash";
      const limitValue = type === "cheque" 
        ? calculateChequeUsedAmount($row.find(".cheque-select").val(), $row)
        : calculateTotalCashPay($row);
      swal({
        title: "Invalid Amount!",
        text: `Cannot exceed Overdue (Rs. ${formatAmount(overdue)}) or ${limitType} Balance (Rs. ${formatAmount(limitValue)})`,
        type: "error",
        timer: CONFIG.SWAL_TIMEOUT,
        showConfirmButton: false,
      });
    }
    
    return inputVal;
  };

  const validateOutstandingLimit = () =>
    parseAmount($("#cheque_total").val()) + parseAmount($("#cash_total").val()) <=
    parseAmount($("#total_outstanding").val());

  const toggleCashPay = () => {
    const cashTotal = parseAmount($("#cash_total").val());
    const $cashInputs = $(".cash-pay");
    $cashInputs.prop("disabled", cashTotal <= 0).val(cashTotal <= 0 ? "0.00" : $cashInputs.val());
    if (cashTotal <= 0) {
      $("#invoiceBody tr").not("#noItemRow").each((_, row) => updateRowBalance($(row)));
      updateState();
    }
  };

  // Event handlers
  $(document).on("change", ".cheque-select", function () {
    const $select = $(this);
    const $row = $select.closest("tr");
    const $chequeInput = $row.find(".cheque-pay");
    const selectedChequeId = $select.val();
    
    // Update hidden fields
    const cheque = state.chequeInfo.find((c) => c.id === selectedChequeId);
    $row.find(".cheque-no").val(cheque?.chequeNo || "");
    $row.find(".cheque-date").val(cheque?.chequeDate || "");
    $row.find(".bank-branch").val(cheque?.bankBranchId || "");

    if (selectedChequeId && $select.data("prev-cheque") !== selectedChequeId) {
      const maxAmount = parseAmount($row.find(".invoice-overdue").text());
      const remainingBalance = cheque.amount - calculateChequeUsedAmount(selectedChequeId, $row);
      $chequeInput.val(formatAmount(Math.min(remainingBalance, maxAmount))).focus();
      $select.data("prev-cheque", selectedChequeId);
    } else if (!selectedChequeId) {
      $chequeInput.val("0.00");
    }

    $chequeInput.prop("disabled", !selectedChequeId);
    updateRowBalance($row);
    updateState();
  });

  $(document).on("focus", ".cheque-pay, .cash-pay", function () {
    const $input = $(this);
    const value = $input.val().replace(/[^0-9.]/g, "");
    $input.val(value === "0" || value === "0.00" ? "" : value).prop("selectionStart", 0);
  });

  $(document).on("input", ".cheque-pay, .cash-pay", debounce(function () {
    const $input = $(this);
    const $row = $input.closest("tr");
    const type = $input.hasClass("cheque-pay") ? "cheque" : "cash";
    
    if (!$input.val().replace(/[^0-9.]/g, "")) {
      $input.val("0.00").prop("selectionStart", 0);
    } else {
      validatePayment($input, $row, type);
    }
    
    updateRowBalance($row);
    updateState();
  }, 300));

  $(document).on("blur", ".cheque-pay, .cash-pay", function () {
    const $input = $(this);
    const amount = parseAmount($input.val()) || 0;
    $input.val(formatAmount(amount));
  });

  $("#add_cheque").on("click", function () {
    const chequeData = {
      chequeNo: $("#cheque_no").val().trim(),
      chequeDate: $("#cheque_date").val().trim(),
      bankBranch: $("#bank_branch_name").val().trim(),
      bankBranchId: $("#bank_branch").val().trim(),
      amount: parseAmount($("#amount").val()),
    };

    // Validation
    if (!isValidChequeNo(chequeData.chequeNo)) {
      return swal("Invalid Cheque Number", "Cheque number should be 6–12 digits.", "error");
    }
    if (!isValidDate(chequeData.chequeDate)) {
      return swal("Invalid Cheque Date", "Cheque date must be today or a future date.", "error");
    }
    if (!chequeData.bankBranch || !chequeData.bankBranchId) {
      return swal("Missing Bank", "Please select a valid Bank & Branch.", "error");
    }
    if (chequeData.amount <= 0) {
      return swal("Invalid Amount", "Amount should be a number greater than 0.", "error");
    }
    if (!validateOutstandingLimit()) {
      return swal({
        title: "Exceeded Outstanding Amount!",
        text: `Total amount cannot exceed Outstanding Amount (Rs. ${formatAmount(parseAmount($("#total_outstanding").val()))}).`,
        type: "error",
        timer: CONFIG.SWAL_TIMEOUT,
        showConfirmButton: false,
      });
    }

    // Add cheque
    const chequeId = "cheque_" + Date.now();
    state.chequeInfo.push({
      ...chequeData,
      id: chequeId,
      used: false,
      usedAmount: 0,
      remaining: chequeData.amount,
    });

    $("#noItemRow").remove();
    $("#chequeBody").append(`
      <tr data-cheque-id="${chequeId}">
        <td>${chequeData.chequeNo}<input type="hidden" name="cheque_no[]" value="${chequeData.chequeNo}"></td>
        <td>${chequeData.chequeDate}<input type="hidden" name="cheque_dates[]" value="${chequeData.chequeDate}"></td>
        <td>${chequeData.bankBranch}<input type="hidden" name="bank_branches[]" value="${chequeData.bankBranchId}"></td>
        <td class="cheque-amount" data-amount="${chequeData.amount}">${formatAmount(chequeData.amount)}<input type="hidden" name="cheque_amounts[]" value="${chequeData.amount}"></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">Remove</button></td>
      </tr>
    `);

    $("#cheque_no, #cheque_date, #bank_branch_name, #bank_branch, #amount").val("");
    updateState();
  });

  $("#cheque_no, #cheque_date, #bank_branch_name, #amount").on("keypress", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      $("#add_cheque").click();
    }
  });

  $("#chequeBody").on("click", ".remove-row", function () {
    const $row = $(this).closest("tr");
    const chequeId = $row.data("cheque-id");
    $row.remove();
    state.chequeInfo = state.chequeInfo.filter((cheque) => cheque.id !== chequeId);
    
    if (!$("#chequeBody tr").length) {
      $("#chequeBody").append(
        `<tr id="noItemRow"><td colspan="5" class="text-center text-muted">No items added</td></tr>`
      );
    }
    updateState();
  });

  $(document).on("click", ".select-branch", function () {
    $("#bank_branch").val($(this).data("id"));
    $("#bank_branch_name").val($(this).find("td:eq(2)").text());
    $("#branch_master").modal("hide");
  });

  let customerTableInitialized = false;

  const loadCustomerTable = () => {
    if (!customerTableInitialized) {
      $("#customerTable").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "ajax/php/customer-master.php",
          type: "POST",
          data: { filter: true, category: 1 },
          dataSrc: (json) => json.data,
          error: (xhr) => console.error("Server Error Response:", xhr.responseText),
        },
        columns: [
          { data: "key", title: "#ID" },
          { data: "code", title: "Code" },
          { data: "name", title: "Name" },
          { data: "mobile_number", title: "Mobile Number" },
          { data: "email", title: "Email" },
          { data: "category", title: "Category" },
          { data: "province", title: "Province" },
          { data: "credit_limit", title: "Credit Limit" },
          { data: "outstanding", title: "Outstanding" },
        ],
        order: [[0, "desc"]],
        pageLength: 100,
        createdRow: (row, data) => {
          $(row).addClass('cursor-pointer');
        }
      });

      customerTableInitialized = true;
    } else {
      $("#customerTable").DataTable().ajax.reload();
    }

    $("#customerTable tbody").off("click", "tr").on("click", "tr", function () {
      const data = $("#customerTable").DataTable().row(this).data();
      
      if (data) {
        $("#customer_id").val(data.id);
        $("#customer_code").val(data.code);
        $("#customer_name").val(data.name);
        $("#customer_address").val(data.address);
        $("#outstanding").val(formatAmount(data.outstanding));
        $("#customerModal").modal("hide");
        loadCustomerCreditInvoices(data.id);
      }
    });
  };

  $("#customerModalBtn").on("click", loadCustomerTable);

  const loadCustomerCreditInvoices = (customerId) => {
    if (!customerId) return;
    
    
    $.ajax({
      url: "ajax/php/payment-receipt.php",
      type: "POST",
      data: { action: "get_credit_invoices", customer_id: customerId },
      success: (response) => {
        $("#invoiceBody").empty();
      
        if (response.success && response.data?.length) {
          let totalOutstanding = 0;
          response.data.forEach((invoice) => {
            const invoiceValue = parseFloat(invoice.grand_total || 0);
            const paidAmount = parseFloat(invoice.outstanding_settle_amount || 0);
            const overdue = invoiceValue - paidAmount;
            totalOutstanding += overdue;

            $("#invoiceBody").append(`
              <tr>
                <td>${invoice.invoice_date}</td>
                <td class="hidden"><input type="hidden" name="invoice_id[]" value="${invoice.id}">${invoice.id}</td>
                <td>${invoice.invoice_no}</td>
                <td>${formatAmount(invoiceValue)}</td>
                <td>${formatAmount(paidAmount)}</td>
                <td><span class="text-danger fw-bold invoice-overdue">${formatAmount(overdue)}</span></td>
                <td>
                  <input type="text" name="cheque_pay[]" class="form-control form-control-sm cheque-pay" value="0.00">
                  <select name="cheque_select[]" class="form-select form-select-sm mt-1 cheque-select">
                    <option value="">Select Cheque</option>
                    ${state.chequeInfo.map((cheque) => 
                      `<option value="${cheque.id}" data-amount="${cheque.amount}" ${cheque.used ? "disabled" : ""}>
                        ${cheque.chequeNo} (${formatAmount(cheque.amount)})
                      </option>`).join("")}
                  </select>
                  <input type="hidden" name="cheque_no[]" class="form-control form-control-sm cheque-no" value="">
                  <input type="hidden" name="cheque_date[]" class="form-control form-control-sm cheque-date" value="">
                  <input type="hidden" name="bank_branch[]" class="form-control form-control-sm bank-branch" value="">
                </td>
                <td><input type="text" name="cash_pay[]" disabled class="form-control form-control-sm cash-pay" value="0.00"></td>
                <td class="paid-amount">${formatAmount(0)}</td>
                <td class="balance-amount">${formatAmount(overdue)}</td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="uil uil-trash"></i></button></td>
              </tr>
            `);
          });
          
          $("#total_outstanding, #balance_amount").val(formatAmount(totalOutstanding));
        } else {
          $("#invoiceBody").html(`<tr><td colspan="11" class="text-center text-muted">No items found</td></tr>`);
          $("#total_outstanding, #balance_amount").val("0.00");
          swal("No Data", "No invoices found for this customer.", "info");
        }
        
        updateState();
        toggleCashPay();
        updateChequePayDisabledState();
      },
      error: (xhr) => {
        console.error("Failed to fetch invoices", xhr);
        swal("Error", "Failed to load invoices. Please try again.", "error");
      },
    });
  };

  $("#cash_total").on("input", function () {
    const cashTotal = parseAmount($(this).val());
    const chequeTotal = parseAmount($("#cheque_total").val());
    const outstanding = parseAmount($("#total_outstanding").val());

    if (cashTotal + chequeTotal > outstanding) {
      $(this).val(formatAmount(Math.max(0, outstanding - chequeTotal)));
      swal({
        title: "Exceeded Outstanding Amount!",
        text: `Total amount cannot exceed Outstanding Amount (Rs. ${formatAmount(outstanding)}).`,
        type: "error",
        timer: CONFIG.SWAL_TIMEOUT,
        showConfirmButton: false,
      });
    }

    toggleCashPay();
    updateState();
  });

  $("#new").click((e) => {
    e.preventDefault();
    location.reload();
  });

  $("#create").click((event) => {
    event.preventDefault();

    // Form validation
    const validations = [
      { field: "#code", message: "Please enter receipt number" },
      { field: "#customer_code", message: "Please select a customer" },
      { field: "#entry_date", message: "Please select an entry date" },
      { field: "#paid_amount", message: "Paid amount must be greater than 0", condition: () => parseAmount($("#paid_amount").val()) <= 0 },
    ];

    for (const { field, message, condition } of validations) {
      if (!$(field).val() || (condition && condition())) {
        return swal({ title: "Error!", text: message, type: "error", timer: CONFIG.SWAL_TIMEOUT, showConfirmButton: false });
      }
    }

    if (!validateOutstandingLimit()) {
      return swal({
        title: "Error!",
        text: "Total cheque and cash amount cannot exceed the outstanding amount",
        type: "error",
        timer: CONFIG.SWAL_TIMEOUT,
        showConfirmButton: false,
      });
    }

    $(".someBlock").preloader();
    const formData = new FormData();
    $("#form-data, #form-data-cheque, #form-data-invoice").each(function () {
      new FormData(this).forEach((value, key) => formData.append(key, value));
    });

    formData.append("total_outstanding", parseAmount($("#total_outstanding").val()));
    formData.append("customer_id", $("#customer_id").val());
    formData.append("paid_amount", parseAmount($("#paid_amount").val()));
    formData.append("create", true);
    formData.append("action", "create");

    $.ajax({
      url: "ajax/php/payment-receipt.php",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: ({ status, message }) => {
        $(".someBlock").preloader("remove");
        swal({
          title: status === "success" ? "Success!" : "Error!",
          text: status === "success" ? "Payment receipt created successfully!" : (message || "Something went wrong."),
          type: status,
          timer: CONFIG.SWAL_TIMEOUT,
          showConfirmButton: false,
        });
        if (status === "success") {
          setTimeout(() => window.location.reload(), CONFIG.SWAL_TIMEOUT);
        }
      },
      error: () => {
        $(".someBlock").preloader("remove");
        swal("Error", "Failed to create payment receipt. Please try again.", "error");
      },
    });
  });

  // Initialize
  toggleCashPay();
  updateChequePayDisabledState();
});