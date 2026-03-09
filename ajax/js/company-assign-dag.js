jQuery(document).ready(function () {
    let selectedDags = []; // To keep track of added items to prevent duplicates

    // Search DAG Modal Logic
    $("#searchDagBtn").click(function () {
        let keyword = $("#dagSearchInput").val();
        let showRejected = $("#showRejectedDagsToggle").is(":checked") ? 1 : 0;

        $.ajax({
            url: "ajax/php/company-assign-dag.php",
            type: "POST",
            data: { search_dag: true, keyword: keyword, show_rejected: showRejected },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach((dag, index) => {
                            let dagNumber = dag.dag_number ? dag.dag_number : 'DAG-' + String(dag.id).padStart(5, '0');
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${dagNumber}</td>
                                <td>${dag.my_number}</td>
                                <td>${dag.customer_full_name}</td>
                                <td>${dag.serial_no}</td>
                                <td>${dag.size}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info select-dag-btn" 
                                        data-id="${dag.id}" 
                                        data-mynumber="${dag.my_number}" 
                                        data-customer="${dag.customer_full_name}">
                                        Select
                                    </button>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="7" class="text-center">No matching records found.</td></tr>`;
                    }
                    $("#dagSelectionTableBody").html(html);
                }
            }
        });
    });

    // Auto-search when toggle changes
    $("#showRejectedDagsToggle").change(function () {
        $("#searchDagBtn").trigger('click');
    });

    // Handle DAG selection
    $(document).on("click", ".select-dag-btn", function () {
        let dagId = $(this).data("id");
        let myNumber = $(this).data("mynumber");
        let customer = $(this).data("customer");

        // Check for duplicates
        if (selectedDags.includes(dagId)) {
            swal({ title: "Warning!", text: "This DAG is already added to the list.", type: "warning", timer: 2000, showConfirmButton: false });
            return;
        }

        selectedDags.push(dagId);

        // Add row to main table
        let rowCount = $("#dagItemsBody tr").length + 1;
        let rowHtml = `<tr data-dag-id="${dagId}">
            <td class="row-number">${rowCount}</td>
            <td><input type="text" class="form-control" value="${myNumber}" readonly></td>
            <td><input type="text" class="form-control" value="${customer}" readonly></td>
            <td><input type="text" class="form-control item-job-number" placeholder="Job No"></td>
            <td><input type="text" class="form-control item-belt-design" placeholder="Belt Design"></td>
            <td>
                <select class="form-select item-company-status">
                    <option value="">Select Status</option>
                    <option value="Processing">Processing</option>
                    <option value="Completed">Completed</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </td>
            <td><input type="text" class="form-control date-picker-date item-company-received-date" placeholder="Select Date"></td>
            <td><input type="text" class="form-control item-uc-number" placeholder="UC Number"></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-dag-btn" data-id="${dagId}">
                    <i class="uil uil-trash"></i>
                </button>
            </td>
        </tr>`;

        $("#dagItemsBody").append(rowHtml);

        // Initialize datepicker on the new row
        $(".date-picker-date").datepicker({
            dateFormat: 'yy-mm-dd'
        });

        $("#dagSearchModal").modal("hide");
        updateToggleState();
    });

    // Remove item
    $(document).on("click", ".remove-dag-btn", function () {
        let dagId = $(this).data("id");
        selectedDags = selectedDags.filter(id => id !== dagId);
        $(this).closest("tr").remove();
        updateRowNumbers();
        updateToggleState();
    });

    function updateRowNumbers() {
        $("#dagItemsBody tr").each(function (index) {
            $(this).find(".row-number").text(index + 1);
        });
    }

    function updateToggleState() {
        if ($("#dagItemsBody tr").length > 0) {
            $("#showRejectedDagsToggle").prop("disabled", true);
        } else {
            $("#showRejectedDagsToggle").prop("disabled", false);
        }
    }

    // Update row color based on status
    $(document).on("change", ".item-company-status", function () {
        if ($(this).val() === 'Rejected') {
            $(this).closest("tr").addClass("table-danger");
        } else {
            $(this).closest("tr").removeClass("table-danger");
        }
    });

    // ==========================================
    // ASSIGNMENT SEARCH & LOAD LOGIC
    // ==========================================
    $("#searchAssignmentBtn").click(function () {
        let keyword = $("#assignmentSearchInput").val();

        $.ajax({
            url: "ajax/php/company-assign-dag.php",
            type: "POST",
            data: { search_assignment: true, keyword: keyword },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach((assignment, index) => {
                            let myNumbersHtml = '';
                            if (assignment.my_numbers) {
                                let nums = assignment.my_numbers.split(', ');
                                nums.forEach(num => {
                                    myNumbersHtml += `<span class="badge bg-soft-primary text-primary me-1 mb-1" style="font-size: 11px;">${num}</span>`;
                                });
                            } else {
                                myNumbersHtml = '<span class="text-muted">-</span>';
                            }
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${assignment.assignment_number}</td>
                                <td>${assignment.company_name}</td>
                                <td>${assignment.company_receipt_number}</td>
                                <td style="white-space: normal; max-width: 250px;">${myNumbersHtml}</td>
                                <td>${assignment.company_issued_date}</td>
                                <td>${assignment.created_at}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info select-assignment-btn" 
                                        data-id="${assignment.id}" 
                                        data-assignment_number="${assignment.assignment_number}" 
                                        data-company_id="${assignment.company_id}"
                                        data-receipt="${assignment.company_receipt_number}"
                                        data-issued="${assignment.company_issued_date}">
                                        Select
                                    </button>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="8" class="text-center">No matching records found.</td></tr>`;
                    }
                    $("#assignmentSelectionTableBody").html(html);
                }
            }
        });
    });

    $(document).on("click", ".select-assignment-btn", function () {
        let assignmentId = $(this).data("id");

        // Populate Header
        $("#id").val(assignmentId);
        $("#assignment_number").val($(this).data("assignment_number"));
        $("#company_id").val($(this).data("company_id"));
        $("#company_receipt_number").val($(this).data("receipt"));
        $("#company_issued_date").val($(this).data("issued"));

        // Toggle Buttons
        $("#create").hide();
        $("#update").show();
        $(".delete-assignment").show();
        $("#print").show().attr("href", "print-assign-dag.php?id=" + assignmentId);

        // Fetch & Populate Items
        $.ajax({
            url: "ajax/php/company-assign-dag.php",
            type: "POST",
            data: { get_assignment_items: true, assignment_id: assignmentId },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    $("#dagItemsBody").empty();
                    selectedDags = [];

                    response.data.forEach((item, index) => {
                        selectedDags.push(item.dag_id);

                        let rowCount = index + 1;
                        let rowClass = item.company_status === 'Rejected' ? 'table-danger' : '';
                        let rowHtml = `<tr data-dag-id="${item.dag_id}" class="${rowClass}">
                            <td class="row-number">${rowCount}</td>
                            <td><input type="text" class="form-control" value="${item.my_number}" readonly></td>
                            <td><input type="text" class="form-control" value="${item.customer_full_name}" readonly></td>
                            <td><input type="text" class="form-control item-job-number" placeholder="Job No" value="${item.job_number}"></td>
                            <td><input type="text" class="form-control item-belt-design" placeholder="Belt Design" value="${item.belt_design}"></td>
                            <td>
                                <select class="form-select item-company-status">
                                    <option value="">Select Status</option>
                                    <option value="Processing" ${item.company_status === 'Processing' ? 'selected' : ''}>Processing</option>
                                    <option value="Completed" ${item.company_status === 'Completed' ? 'selected' : ''}>Completed</option>
                                    <option value="Rejected" ${item.company_status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control date-picker-date item-company-received-date" placeholder="Select Date" value="${item.company_received_date || ''}"></td>
                            <td><input type="text" class="form-control item-uc-number" placeholder="UC Number" value="${item.uc_number}"></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-dag-btn" data-id="${item.dag_id}">
                                    <i class="uil uil-trash"></i>
                                </button>
                            </td>
                        </tr>`;

                        $("#dagItemsBody").append(rowHtml);
                    });

                    // Initialize datepickers
                    $(".date-picker-date").datepicker({
                        dateFormat: 'yy-mm-dd'
                    });

                    updateToggleState();
                }
            }
        });

        $("#assignmentSearchModal").modal("hide");
    });

    // ==========================================
    // SAVE ASSIGNMENT
    // ==========================================
    $("#create").click(function (event) {
        event.preventDefault();
        saveOrUpdateAssignment("create");
    });

    // ==========================================
    // UPDATE ASSIGNMENT
    // ==========================================
    $("#update").click(function (event) {
        event.preventDefault();
        saveOrUpdateAssignment("update");
    });

    function saveOrUpdateAssignment(actionType) {
        // Validation
        if (!$("#company_id").val()) {
            swal({ title: "Error!", text: "Please Select a Company", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }
        if (!$("#company_receipt_number").val()) {
            swal({ title: "Error!", text: "Please enter Company Receipt Number", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }
        if (!$("#company_issued_date").val()) {
            swal({ title: "Error!", text: "Please Select Date", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        if (selectedDags.length === 0) {
            swal({ title: "Error!", text: "Please add at least one DAG item.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        // Collect and Validate items
        let items = [];
        let missingStatus = false;

        $("#dagItemsBody tr").each(function () {
            let status = $(this).find(".item-company-status").val();
            if (!status) {
                missingStatus = true;
            }

            items.push({
                dag_id: $(this).data("dag-id"),
                job_number: $(this).find(".item-job-number").val(),
                belt_design: $(this).find(".item-belt-design").val(),
                company_status: status,
                company_received_date: $(this).find(".item-company-received-date").val(),
                uc_number: $(this).find(".item-uc-number").val()
            });
        });

        if (missingStatus) {
            swal({ title: "Error!", text: "Please Select a Company Status for all items.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        $(".someBlock").preloader();

        let formData = new FormData($("#form-data")[0]);
        formData.append(actionType, true);
        formData.append("items", JSON.stringify(items));
        if (actionType === "update") {
            formData.append("id", $("#id").val());
        }

        $.ajax({
            url: "ajax/php/company-assign-dag.php",
            type: "POST",
            data: formData,
            async: false,
            dataType: 'json',
            success: function (result) {
                $(".someBlock").preloader("remove");
                if (result.status === 'success') {
                    swal({
                        title: "Success!",
                        text: actionType === "create" ? "Assignment Saved Successfully" : "Assignment Updated Successfully",
                        type: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
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
            cache: false,
            contentType: false,
            processData: false
        });
    }

    // ==========================================
    // DELETE ASSIGNMENT
    // ==========================================
    $(".delete-assignment").click(function (e) {
        e.preventDefault();
        let id = $("#id").val();

        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this assignment data!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false
        }, function () {
            $.ajax({
                url: "ajax/php/company-assign-dag.php",
                type: "POST",
                data: { delete: true, id: id },
                dataType: "json",
                success: function (response) {
                    if (response.status === 'success') {
                        swal({ title: "Deleted!", text: "Assignment has been deleted.", type: "success", timer: 2000, showConfirmButton: false });
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        swal("Error!", "Failed to delete logic.", "error");
                    }
                }
            });
        });
    });

    // ==========================================
    // UI RESET BUTTON
    // ==========================================
    $('#new').click(function (e) {
        e.preventDefault();
        $("#form-data")[0].reset();
        $("#id").val("0");
        $("#dagItemsBody").empty();
        selectedDags = [];
        updateToggleState();

        $("#create").show();
        $("#update").hide();
        $(".delete-assignment").hide();
        $("#print").hide();

        // Fetch next ID
        $.ajax({
            url: "ajax/php/company-assign-dag.php",
            type: "POST",
            data: { get_next_id: true },
            dataType: "json",
            success: function (result) {
                if (result.status === 'success') {
                    $("#assignment_number").val(result.next_id);
                }
            }
        });
    });
    // ==========================================
    // AUTO-LOAD MODALS
    // ==========================================
    $('#dagSearchModal').on('show.bs.modal', function () {
        $("#dagSearchInput").val('');
        $("#searchDagBtn").trigger('click');
    });

    $('#assignmentSearchModal').on('show.bs.modal', function () {
        $("#assignmentSearchInput").val('');
        $("#searchAssignmentBtn").trigger('click');
    });

});
