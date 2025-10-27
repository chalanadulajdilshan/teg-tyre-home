jQuery(document).ready(function () {

  function loadDagItemsToTable(items) {
    $("#dagItemsBodyInvoice").empty();

    if (!items.length) {
      $("#dagItemsBodyInvoice").append(`
      <tr id="noDagItemRow">
        <td colspan="6" class="text-center text-muted">No items found</td>
      </tr>`);
      return;
    }

    items.forEach((item) => {
      const price = parseFloat(item.price) || 0;
      const qty = parseFloat(item.qty) || 0;
      const total = price * qty;

      const row = $(`
    <tr class="dag-item-row clickable-row">
      <td>
        ${item.vehicle_no}
        <input type="hidden" class="vehicle_no" value="${item.vehicle_no}">
      </td>
      <td>
        ${item.belt_title}
        <input type="hidden" class="belt_id" value="${item.belt_id}">
      </td>
      <td>
        ${item.barcode}
        <input type="hidden" class="barcode" value="${item.barcode}">
      </td>
      <td>
        ${qty}
        <input type="hidden" class="qty" value="${qty}">
      </td>
      <td>
        <input type="number" class="form-control form-control-sm price" value="${price}" readonly>
      </td>
      <td>
        <input type="text" class="form-control form-control-sm total_amount" value="${total.toFixed(2)}" readonly>
      </td>
    </tr>
    `);

      // On row click → populate input fields
      row.on("click", function () {
        $("#vehicleNo").val(item.vehicle_no);
        $("#beltDesign").val(item.belt_id).trigger("change");
        $("#barcode").val(item.barcode);
        $("#quantity").val(qty);
        $("#casingCost").val(price);
        $("#vehicleNo").focus();
      });

      $("#dagItemsBodyInvoice").append(row);
    });
  }


  function resetDagInputs() {
    $("#beltDesign").val("").trigger("change");
    $("#sizeDesign").val("").trigger("change");
    $("#brand_id").val("").trigger("change");
    $("#serial_num1").val("");
  }

  function resetDagForm() {
    // Reset all form inputs
    $("#form-data")[0].reset();

    // Reset select2 dropdowns
    $("#department_id, #customer_id, #dag_company_id, #brand_id").val("").trigger("change");

    // Reset date inputs
    $("#received_date, #delivery_date, #customer_request_date, #company_issued_date, #company_delivery_date").val("");

    // Reset status to default
    $("#status").val("pending");

    // Hide update button, show create button
    $("#update").hide();
    $("#create").show();

    // Hide print button
    $("#print").hide();

    // Reset hidden fields
    $("#id").val("0");
    $("#dag_id").val("");

    // Clear any error messages
    $(".text-danger").remove();
  }


  function addDagItem() {
    try {
      const beltDesignId = $("#beltDesign").val();
      const beltDesignText = $("#beltDesign option:selected").text();
      const sizeDesignId = $("#sizeDesign").val();
      const sizeDesignText = $("#sizeDesign option:selected").text();
      
      // Safe handling of serial number
      const serialNum1Element = $("#serial_num1");
      const serialNum1 = serialNum1Element.length && serialNum1Element.val() ? serialNum1Element.val().trim() : "";

      if (!beltDesignId || !serialNum1) {
        swal("Error!", "Please fill all required fields correctly.", "error");
        return;
      }

      // Get company field values with safe checks
      const companyElement = $("#dag_company_id");
      const companyId = companyElement.length ? (companyElement.val() || "") : "";
      const companyText = companyElement.length ? (companyElement.find("option:selected").text() || "") : "";
      
      const issuedDateElement = $("#company_issued_date");
      const issuedDate = issuedDateElement.length ? (issuedDateElement.val() || "") : "";
      
      const deliveryDateElement = $("#company_delivery_date");
      const deliveryDate = deliveryDateElement.length ? (deliveryDateElement.val() || "") : "";
      
      const receiptNoElement = $("#receipt_no");
      const receiptNo = receiptNoElement.length ? (receiptNoElement.val() || "") : "";
      
      const brandElement = $("#brand_id");
      const brandId = brandElement.length ? (brandElement.val() || "") : "";
      const brandText = brandElement.length ? (brandElement.find("option:selected").text() || "") : "";

      const jobNumberElement = $("#job_number");
      const jobNumber = jobNumberElement.length ? (jobNumberElement.val() || "") : "";
      
      const statusElement = $("#dag_status");
      const statusValue = statusElement.length ? (statusElement.val() || "") : "";
      const statusText = statusElement.length ? (statusElement.find("option:selected").text() || "") : "";

      const newRow = $(`
        <tr class="dag-item-row">
          <td>${beltDesignText}<input type="hidden" name="belt_design_id[]" class="belt_id" value="${beltDesignId}"></td>
          <td>${sizeDesignText}<input type="hidden" name="size_design_id[]" class="size_id" value="${sizeDesignId}"></td>
          <td>${serialNum1}<input type="hidden" name="serial_num1[]" class="serial_num1" value="${serialNum1}"></td>
          <td>${companyText}<input type="hidden" name="dag_company_id[]" value="${companyId}"></td>
          <td>${issuedDate}<input type="hidden" name="company_issued_date[]" value="${issuedDate}"></td>
          <td>${deliveryDate}<input type="hidden" name="company_delivery_date[]" value="${deliveryDate}"></td>
          <td>${receiptNo}<input type="hidden" name="receipt_no[]" value="${receiptNo}"></td>
          <td>${brandText}<input type="hidden" name="brand_id[]" class="brand_id" value="${brandId}"></td>
          <td>${jobNumber}<input type="hidden" name="job_number[]" value="${jobNumber}"></td>
          <td>${statusText}<input type="hidden" name="status[]" value="${statusValue}"></td>
          <td>
            <button type="button" class="btn btn-warning btn-sm edit-item">Edit</button>
            <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
          </td>
        </tr>
      `);

      $("#dagItemsBody").append(newRow);
      resetDagInputs();
      $("#noDagItemRow").hide();

      $("#beltDesign").focus();
    } catch (error) {
      console.error("Error in addDagItem:", error);
      swal("Error!", "An error occurred while adding the item. Please check the console for details.", "error");
    }
  }



  $("#addDagItemBtn").click(function (e) {
    e.preventDefault();
    addDagItem();
  });


  $("#beltDesign, #sizeDesign, #serial_num1").on("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      addDagItem();
    }
  });

  $(document).on("click", ".remove-item", function () {
    $(this).closest("tr").remove();

  });

  $("#create").click(function (event) {
    event.preventDefault();

    if (!$("#ref_no").val().trim()) {
      swal({
        title: "Error!",
        text: "Reference Number is required to proceed.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    if (!$("#received_date").val().trim()) {
      swal({
        title: "Error!",
        text: "Please enter the Received Date to continue.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    if (!$("#customer_request_date").val().trim()) {
      swal({
        title: "Error!",
        text: "Customer Request Date is needed for scheduling.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    let dagItems = [];
    $(".dag-item-row").each(function () {
      dagItems.push({
        belt_id: $(this).find(".belt_id").val(),
        size_id: $(this).find(".size_id").val(),
        serial_num1: $(this).find(".serial_num1").val(),
        dag_company_id: $(this).find(".belt_id").length ? $("#dag_company_id").val() : "",
        company_issued_date: $("#company_issued_date").val(),
        company_delivery_date: $("#company_delivery_date").val(),
        receipt_no: $("#receipt_no").val(),
        brand_id: $(this).find(".brand_id").val() || "",
        job_number: $("#job_number").val(),
        status: $("#dag_status").val()
      });
    });

    if (dagItems.length === 0) {
      swal({
        title: "Error!",
        text: "Please add at least one DAG item before saving.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    $(".someBlock").preloader();
    const formData = new FormData($("#form-data")[0]);
    formData.append("create", true); // Create flag
    formData.append("dag_items", JSON.stringify(dagItems));

    $.ajax({
      url: "ajax/php/create-dag.php",
      type: "POST",
      data: formData,
      async: false,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "JSON",
      success: function (result) {
        $(".someBlock").preloader("remove");
        if (result.status === "success") {
          // Reset the form and clear all inputs
          resetDagForm();

          // Clear DAG items table
          $("#dagItemsBody").empty();
          $("#dagItemsBody").append(`
            <tr id="noDagItemRow">
              <td colspan="13" class="text-center text-muted">No items added</td>
            </tr>
          `);

          // Clear invoice items table
          $("#dagItemsBodyInvoice").empty();
          $("#dagItemsBodyInvoice").append(`
            <tr id="noDagItemRow">
              <td colspan="6" class="text-center text-muted">No items found</td>
            </tr>
          `);

          // Reset totals
          $("#subTotal, #finalTotal").val("0.00");

          // Show success message and refresh page when OK is clicked
          swal({
            title: "Success!",
            text: "DAG created successfully!",
            type: "success",
            confirmButtonText: "OK"
          }, function() {
            location.reload();
          });
        } else {
          swal("Error!", result.message || "Something went wrong while creating.", "error");
        }
      },
    });
  });



  $("#update").click(function (event) {
    event.preventDefault();
    if (!$("#ref_no").val().trim()) {
      swal({
        title: "Error!",
        text: "Reference Number is required to proceed.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    if (!$("#received_date").val().trim()) {
      swal({
        title: "Error!",
        text: "Please enter the Received Date to continue.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }



    if (!$("#customer_request_date").val().trim()) {
      swal({
        title: "Error!",
        text: "Customer Request Date is needed for scheduling.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    if (!$("#remark").val().trim()) {
      swal({
        title: "Error!",
        text: "Dag Remark added.!",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }


    $(".someBlock").preloader();
    const formData = new FormData($("#form-data")[0]);
    formData.append("update", true);
    formData.append("dag_id", $("#id").val());

    let dagItems = [];
    $(".dag-item-row").each(function () {
      dagItems.push({
        belt_id: $(this).find(".belt_id").val(),
        size_id: $(this).find(".size_id").val(),
        serial_num1: $(this).find(".serial_num1").val(),
        barcode: $(this).find(".barcode").val(),
        dag_company_id: $("#dag_company_id").val(),
        company_issued_date: $("#company_issued_date").val(),
        company_delivery_date: $("#company_delivery_date").val(),
        receipt_no: $("#receipt_no").val(),
        brand_id: $(this).find(".brand_id").val() || "",
        job_number: $("#job_number").val(),
        status: $("#dag_status").val()
      });

    });
    formData.append("dag_items", JSON.stringify(dagItems));

    $.ajax({
      url: "ajax/php/create-dag.php",
      type: "POST",
      data: formData,
      async: false,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "JSON",
      success: function (result) {
        $(".someBlock").preloader("remove");
        if (result.status === "success") {
          swal("Success!", "DAG updated successfully!", "success");
          setTimeout(() => location.reload(), 2000);
        } else {
          swal("Error!", "Something went wrong while updating.", "error");
        }
      },
    });
  });


  $(document).on("click", ".edit-item", function () {
    const row = $(this).closest("tr");

    $("#beltDesign").val(row.find(".belt_id").val()).trigger("change");
    $("#sizeDesign").val(row.find(".size_id").val()).trigger("change");
    $("#serial_num1").val(row.find(".serial_num1").val());
    $("#brand_id").val(row.find(".brand_id").val()).trigger("change");

    row.remove();

    $("#beltDesign").focus();
  });


  $(document).on("click", ".select-dag", function () {
    const data = $(this).data();

    $("#id").val(data.id);
    $("#dag_id").val(data.id);
    $("#ref_no").val(data.ref_no);
    $("#job_number").val(data.job_number);
    $("#department_id").val(data.department_id).trigger("change");
    $("#customer_id").val(data.customer_id).trigger("change");


    $("#customer_code").val(data.customer_code);
    $("#customer_name").val(data.customer_name);
    $("#vehicle_no").val(data.vehicle_no);

    $("#received_date").val(data.received_date);
    $("#delivery_date").val(data.delivery_date);
    $("#customer_request_date").val(data.customer_request_date);
    $("#remark").val(data.remark);

    $("#create").hide();
    $("#dagModel").modal("hide");
    $("#mainDagModel").modal("hide");

    $("#noDagItemRow").hide();
    $("#invoiceTable").hide();
    $("#dagTableHide").show();
    $("#addItemTable").hide();
    $("#quotationTableHide").hide();



    $("#dagItemsBody").empty();
    $("#print").data("dag-id", data.id);
    $("#print").show();
    $("#update").show();
    $.ajax({
      url: "ajax/php/create-dag.php",
      type: "POST",
      data: { dag_id: data.id },
      dataType: "json",
      success: function (res) {
        if (res.status === "success") {
          const items = res.data;
          items.forEach((item) => {
              const row = `
  <tr class="dag-item-row">
    <td>${item.belt_title}<input type="hidden" name="belt_design_id[]" class="belt_id" value="${item.belt_id}"></td>
    <td>${item.size_name || ''}<input type="hidden" name="size_design_id[]" class="size_id" value="${item.size_id}"></td>
    <td>${item.serial_num1 || ''}<input type="hidden" name="serial_num1[]" class="serial_num1" value="${item.serial_num1}"></td>
    <td>${item.dag_company_name || ''}<input type="hidden" name="dag_company_id[]" value="${item.dag_company_id}"></td>
    <td>${item.company_issued_date || ''}<input type="hidden" name="company_issued_date[]" value="${item.company_issued_date}"></td>
    <td>${item.company_delivery_date || ''}<input type="hidden" name="company_delivery_date[]" value="${item.company_delivery_date}"></td>
    <td>${item.receipt_no || ''}<input type="hidden" name="receipt_no[]" value="${item.receipt_no}"></td>
    <td>${item.brand_name || ''}<input type="hidden" name="brand_id[]" class="brand_id" value="${item.brand_id}"></td>
    <td>${item.job_number || ''}<input type="hidden" name="job_number[]" value="${item.job_number}"></td>
    <td>${item.status || ''}<input type="hidden" name="status[]" value="${item.status}"></td>
    <td>
      <button type="button" class="btn btn-warning btn-sm edit-item">Edit</button>
      <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
    </td>
  </tr>`;

            $("#dagItemsBody").append(row);

            const price = parseFloat(item.price) || 0;
            const qty = parseFloat(item.qty) || 0;
            const total = price * qty;

            const invoiceRow = `
              <tr class="dag-item-row clickable-row">
                <td>${$("#vehicle_no").val()}</td>
                <td>${item.belt_title}</td>
                <td>${item.barcode}</td>
                <td>${qty}</td>
                <td><input type="number" class="form-control form-control-sm price"   value="${price}"  ></td>
                <td><input type="text" class="form-control form-control-sm totalPrice"  value="${total.toFixed(2)}" readonly>
                <input type="hidden" id="dag_item_id" value="${item.id}" />
                </td>
              </tr>`;
            $("#dagItemsBodyInvoice").append(invoiceRow);
            calculateTotals();

          });

        } else {
          swal("Warning!", "No items returned for this DAG.", "warning");
        }
      },
      error: function () {
        swal("Error!", "Failed to load DAG items.", "error");
      },
    });
  });

  $(document).on("click", "#print", function (e) {
    e.preventDefault();

    const dagId = $(this).data("dag-id");
    if (!dagId) {
      swal("Error!", "No DAG selected to print.", "error");
      return;
    }

    // Redirect to print page
    window.open(`dag-receipt-print.php?id=${dagId}`, "_blank");
  });


  function calculateTotals() {
    let subTotal = 0;

    $("#dagItemsBodyInvoice tr").each(function () {
      const price = parseFloat($(this).find('.price').val()) || 0;
      const qty = parseFloat($(this).find("td:eq(3)").text()) || 0;
      const rowTotal = price * qty;


      // Update totalPrice input (using class, not id)
      $(this).find('input.totalPrice').val(rowTotal.toFixed(2));

      subTotal += rowTotal;
    });

    const discountStr = $("#disTotal").val().replace(/,/g, '').trim();
    const discountPercent = parseFloat(discountStr) || 0;
    const discountAmount = (subTotal * discountPercent) / 100;

    const finalTotal = subTotal - discountAmount;

    $("#subTotal").val(subTotal.toFixed(2));
    $("#finalTotal").val(finalTotal.toFixed(2));

    if (finalTotal < subTotal) {
      $("#finalTotal").css("color", "red");
    } else {
      $("#finalTotal").css("color", "");
    }
  }

  // Handle price input changes dynamically
  $(document).on('input', '.price', function () {
    const row = $(this).closest('tr');
    const price = parseFloat($(this).val()) || 0;
    const qty = parseFloat(row.find("td:eq(3)").text()) || 0;

    const total = price * qty;
    row.find('.totalPrice').val(total.toFixed(2));

    // Enable discount input if needed
    $("#disTotal").prop("disabled", false);

    calculateTotals();
  });

  // Discount input triggers recalculation
  $(document).on("input", "#disTotal", function () {
    setTimeout(() => {
      calculateTotals();
    }, 10);
  });

  // Delete DAG functionality
  $(".delete-dag").click(function (event) {
    event.preventDefault();
    
    const dagId = $("#id").val();
    if (!dagId || dagId === "0") {
      swal("Error!", "Please select a DAG to delete.", "error");
      return;
    }

    // Show confirmation dialog
    swal({
      title: "Are you sure?",
      text: "Once deleted, you will not be able to recover this DAG!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel!",
      closeOnConfirm: false,
      closeOnCancel: false
    }, function(isConfirm) {
      if (isConfirm) {
        // User confirmed, proceed with deletion
        $(".someBlock").preloader();
        
        $.ajax({
          url: "ajax/php/create-dag.php",
          type: "POST",
          data: { delete: true, dag_id: dagId },
          dataType: "JSON",
          success: function (result) {
            $(".someBlock").preloader("remove");
            if (result.status === "success") {
              swal("Deleted!", "The DAG has been deleted.", "success");
              // Reset form and redirect or reload
              resetDagForm();
              setTimeout(() => {
                location.reload();
              }, 2000);
            } else {
              swal("Error!", result.message || "Failed to delete DAG.", "error");
            }
          },
          error: function () {
            $(".someBlock").preloader("remove");
            swal("Error!", "An error occurred while deleting the DAG.", "error");
          }
        });
      } else {
        // User cancelled
        swal("Cancelled", "The DAG is safe :)", "error");
      }
    });
  });



});
