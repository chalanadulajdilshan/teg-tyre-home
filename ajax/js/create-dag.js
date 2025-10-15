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
    $("#vehicleNo, #barcode, #quantity, #serial_num1, #serial_num2, #serial_num3, #serial_num4, #serial_num5, #serial_num6, #serial_num7, #serial_num8").val("");
    $("#beltDesign").val("").trigger("change");
    $("#sizeDesign").val("").trigger("change");
  }

  function resetDagForm() {
    // Reset all form inputs
    $("#form-data")[0].reset();

    // Reset select2 dropdowns
    $("#department_id, #customer_id, #dag_company_id").val("").trigger("change");

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
    const vehicleNo = $("#vehicleNo").val().trim();
    const beltDesignId = $("#beltDesign").val();
    const beltDesignText = $("#beltDesign option:selected").text();
    const sizeDesignId = $("#sizeDesign").val();
    const sizeDesignText = $("#sizeDesign option:selected").text();
    const serialNum1 = $("#serial_num1").val().trim();
    const serialNum2 = $("#serial_num2").val().trim();
    const serialNum3 = $("#serial_num3").val().trim();
    const serialNum4 = $("#serial_num4").val().trim();
    const serialNum5 = $("#serial_num5").val().trim();
    const serialNum6 = $("#serial_num6").val().trim();
    const serialNum7 = $("#serial_num7").val().trim();
    const serialNum8 = $("#serial_num8").val().trim();
    const qty = parseFloat($("#quantity").val()) || 0;
    const price = parseFloat($("#casingCost").val()) || 0;

    if (!vehicleNo || !beltDesignId) {
      swal("Error!", "Please fill all required fields correctly.", "error");
      return;
    }

    let isDuplicate = false;
    $(".dag-item-row").each(function () {
      if ($(this).find(".vehicle_no").val() === vehicleNo) {
        isDuplicate = true;
        return false;
      }
    });

    if (isDuplicate) {
      swal("Duplicate!", "This vehicle number is already added.", "warning");
      return;
    }



    const newRow = $(`
      <tr class="dag-item-row">
        <td>${vehicleNo}<input type="hidden" name="vehicle_no[]" class="vehicle_no" value="${vehicleNo}"></td>
        <td>${beltDesignText}<input type="hidden" name="belt_design_id[]" class="belt_id" value="${beltDesignId}"></td>
        <td>${sizeDesignText}<input type="hidden" name="size_design_id[]" class="size_id" value="${sizeDesignId}"></td>
        <td>${serialNum1}<input type="hidden" name="serial_num1[]" class="serial_num1" value="${serialNum1}"></td>
        <td>${serialNum2}<input type="hidden" name="serial_num2[]" class="serial_num2" value="${serialNum2}"></td>
        <td>${serialNum3}<input type="hidden" name="serial_num3[]" class="serial_num3" value="${serialNum3}"></td>
        <td>${serialNum4}<input type="hidden" name="serial_num4[]" class="serial_num4" value="${serialNum4}"></td>
        <td>${serialNum5}<input type="hidden" name="serial_num5[]" class="serial_num5" value="${serialNum5}"></td>
        <td>${serialNum6}<input type="hidden" name="serial_num6[]" class="serial_num6" value="${serialNum6}"></td>
        <td>${serialNum7}<input type="hidden" name="serial_num7[]" class="serial_num7" value="${serialNum7}"></td>
        <td>${serialNum8}<input type="hidden" name="serial_num8[]" class="serial_num8" value="${serialNum8}"></td>
        <td>1<input type="hidden" name="qty[]" class="qty" value="1"></td>
         
        <td>
          <button type="button" class="btn btn-warning btn-sm edit-item">Edit</button>
          <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
        </td>
      </tr>
    `);

    $("#dagItemsBody").append(newRow);
    resetDagInputs();
    $("#noDagItemRow").hide();

    const dagItems = [];
    $(".dag-item-row").each(function () {
      dagItems.push({
        vehicle_no: $(this).find(".vehicle_no").val(),
        belt_title: $(this).find(".belt_id option:selected").text() || $(this).find(".belt_id").val(), // if text not present
        belt_id: $(this).find(".belt_id").val(),
        barcode: $(this).find(".barcode").val(),
        qty: parseFloat($(this).find(".qty").val()) || 0,
        price: parseFloat($(this).find(".casing_cost").val()) || 0,
      });
    });

    loadDagItemsToTable(dagItems);
    $("#vehicleNo").focus();
  }



  $("#addDagItemBtn").click(function (e) {
    e.preventDefault();
    addDagItem();
  });


  $("#vehicleNo, #beltDesign, #sizeDesign, #casingCost, #barcode, #quantity, #serial_num1, #serial_num2, #serial_num3, #serial_num4, #serial_num5, #serial_num6, #serial_num7, #serial_num8").on("keydown", function (e) {
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
        vehicle_no: $(this).find(".vehicle_no").val(),
        belt_id: $(this).find(".belt_id").val(),
        size_id: $(this).find(".size_id").val(),
        serial_num1: $(this).find(".serial_num1").val(),
        serial_num2: $(this).find(".serial_num2").val(),
        serial_num3: $(this).find(".serial_num3").val(),
        serial_num4: $(this).find(".serial_num4").val(),
        serial_num5: $(this).find(".serial_num5").val(),
        serial_num6: $(this).find(".serial_num6").val(),
        serial_num7: $(this).find(".serial_num7").val(),
        serial_num8: $(this).find(".serial_num8").val(),
        casing_cost: $(this).find(".casing_cost").val(),
        total_amount: $(this).find(".total_amount").val()
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
        vehicle_no: $(this).find(".vehicle_no").val(),
        belt_id: $(this).find(".belt_id").val(),
        size_id: $(this).find(".size_id").val(),
        serial_num1: $(this).find(".serial_num1").val(),
        serial_num2: $(this).find(".serial_num2").val(),
        serial_num3: $(this).find(".serial_num3").val(),
        serial_num4: $(this).find(".serial_num4").val(),
        serial_num5: $(this).find(".serial_num5").val(),
        serial_num6: $(this).find(".serial_num6").val(),
        serial_num7: $(this).find(".serial_num7").val(),
        serial_num8: $(this).find(".serial_num8").val(),
        barcode: $(this).find(".barcode").val()
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

    $("#vehicleNo").val(row.find(".vehicle_no").val());
    $("#beltDesign").val(row.find(".belt_id").val()).trigger("change");
    $("#sizeDesign").val(row.find(".size_id").val()).trigger("change");
    $("#serial_num1").val(row.find(".serial_num1").val());
    $("#serial_num2").val(row.find(".serial_num2").val());
    $("#serial_num3").val(row.find(".serial_num3").val());
    $("#serial_num4").val(row.find(".serial_num4").val());
    $("#serial_num5").val(row.find(".serial_num5").val());
    $("#serial_num6").val(row.find(".serial_num6").val());
    $("#serial_num7").val(row.find(".serial_num7").val());
    $("#serial_num8").val(row.find(".serial_num8").val());
    $("#quantity").val(row.find(".qty").val());

    row.remove();

    $("#vehicleNo").focus();
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

    $("#received_date").val(data.received_date);
    $("#delivery_date").val(data.delivery_date);
    $("#customer_request_date").val(data.customer_request_date);
    $("#dag_company_id").val(data.dag_company_id).trigger("change");
    $("#company_issued_date").val(data.company_issued_date);
    $("#company_delivery_date").val(data.company_delivery_date);
    $("#receipt_no").val(data.receipt_no);
    $("#remark").val(data.remark);
    $("#status").val(data.status);

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
    <td>${item.vehicle_no}<input type="hidden" name="vehicle_no[]" class="vehicle_no" value="${item.vehicle_no}"></td>
    <td>${item.belt_title}<input type="hidden" name="belt_design_id[]" class="belt_id" value="${item.belt_id}"></td>
    <td>${item.size_name || ''}<input type="hidden" name="size_design_id[]" class="size_id" value="${item.size_id}"></td>
    <td>${item.serial_num1 || ''}<input type="hidden" name="serial_num1[]" class="serial_num1" value="${item.serial_num1}"></td>
    <td>${item.serial_num2 || ''}<input type="hidden" name="serial_num2[]" class="serial_num2" value="${item.serial_num2}"></td>
    <td>${item.serial_num3 || ''}<input type="hidden" name="serial_num3[]" class="serial_num3" value="${item.serial_num3}"></td>
    <td>${item.serial_num4 || ''}<input type="hidden" name="serial_num4[]" class="serial_num4" value="${item.serial_num4}"></td>
    <td>${item.serial_num5 || ''}<input type="hidden" name="serial_num5[]" class="serial_num5" value="${item.serial_num5}"></td>
    <td>${item.serial_num6 || ''}<input type="hidden" name="serial_num6[]" class="serial_num6" value="${item.serial_num6}"></td>
    <td>${item.serial_num7 || ''}<input type="hidden" name="serial_num7[]" class="serial_num7" value="${item.serial_num7}"></td>
    <td>${item.serial_num8 || ''}<input type="hidden" name="serial_num8[]" class="serial_num8" value="${item.serial_num8}"></td>
    <td>${item.qty}<input type="hidden" name="qty[]" class="qty" value="${item.qty}"></td>
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
                <td>${item.vehicle_no}</td>
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



});
