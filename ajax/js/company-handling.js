$(document).ready(function () {

    // Load complaints into the modal table
    function loadComplaints() {
        $.ajax({
            url: 'ajax/php/company-handling.php',
            type: 'POST',
            data: { load_complaints: true },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#complaintTableBody').html(response.html);
                    // Re-initialize DataTable if needed, or destroy and re-init
                    if ($.fn.DataTable.isDataTable('#complaintTable')) {
                        $('#complaintTable').DataTable().destroy();
                    }
                    $('#complaintTable').DataTable();
                }
            }
        });
    }

    // Open Complaint Modal
    $('#searchComplaintBtn').click(function () {
        loadComplaints();
        $('#complaintModal').modal('show');
    });

    // Load companies into the company modal table
    function loadCompanies() {
        $.ajax({
            url: 'ajax/php/company-handling.php',
            type: 'POST',
            data: { load_companies: true },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#companyTableBody').html(response.html);
                    if ($.fn.DataTable.isDataTable('#companyTable')) {
                        $('#companyTable').DataTable().destroy();
                    }
                    $('#companyTable').DataTable();
                }
            }
        });
    }

    // Open Company Modal
    $('#searchCompanyBtn').click(function () {
        loadCompanies();
        $('#companyModal').modal('show');
    });

    // Select Company from modal
    $(document).on('click', '.select-company', function () {
        var id = $(this).data('id');
        var code = $(this).data('code');
        var name = $(this).data('name');

        $('#company_id').val(id);
        $('#company_name').val(name);
        $('#company_number').val(code);

        $('#companyModal').modal('hide');
    });

    // Checkbox Logic: Allow multiple selections
    $('.status-checkbox').change(function () {

        // 1. Construct company_status string
        var statuses = [];
        $('.status-checkbox:checked').each(function () {
            statuses.push($(this).val());
        });
        $('#company_status').val(statuses.join(', '));

        // 2. Toggle Visibility
        var id = $(this).attr('id');
        var sectionMap = {
            'status_priced_issued': '#section_priced_issued',
            'status_rejection': '#section_rejection',
            'status_special_request': '#section_special_request'
        };

        if (this.checked) {
            $(sectionMap[id]).slideDown();
        } else {
            $(sectionMap[id]).slideUp();
        }

        // Dependency: Special Request requires Rejection
        if (id === 'status_special_request' && this.checked) {
            if (!$('#status_rejection').is(':checked')) {
                $(this).prop('checked', false).trigger('change'); // Trigger to hide section too
                swal("Warning", "Special Request can only be selected if Rejection is selected.", "warning");
            }
        }

        // Dependency: Unchecking Rejection unchecks Special Request
        if (id === 'status_rejection' && !this.checked) {
            if ($('#status_special_request').is(':checked')) {
                $('#status_special_request').prop('checked', false).trigger('change');
            }
        }
    });

    // Select Complaint
    $(document).on('click', '.select-complaint', function () {
        var id = $(this).data('id');
        var complaint_no = $(this).data('complaint_no');
        var uc_number = $(this).data('uc_number');
        var fault_description = $(this).data('fault_description');

        var handling_id = $(this).data('handling_id');
        var company_number = $(this).data('company_number');
        var company_name = $(this).data('company_name');
        var sent_date = $(this).data('sent_date');
        var company_status = $(this).data('company_status');

        // New Data Attributes
        var price_amount = $(this).data('price_amount');
        var price_issued_date = $(this).data('price_issued_date');
        var issued_invoice_number = $(this).data('issued_invoice_number');
        var rejection_reason = $(this).data('rejection_reason');
        var rejection_date = $(this).data('rejection_date');
        var company_invoice_number = $(this).data('company_invoice_number');
        var received_invoice_number = $(this).data('received_invoice_number');
        var special_remark = $(this).data('special_remark');
        var special_request_date = $(this).data('special_request_date');
        var status_remark = $(this).data('status_remark');
        var general_remark = $(this).data('general_remark');

        // Populate Complaint Info
        $('#complaint_id').val(id);
        $('#complaint_no').val(complaint_no);
        $('#uc_number').val(uc_number);
        $('#fault_description').val(fault_description);

        // Populate Company Handling Info if available
        if (handling_id) {
            $('#id').val(handling_id);
            $('#company_number').val(company_number);
            $('#company_name').val(company_name);
            $('#sent_date').val(sent_date);
            $('#company_status').val(company_status);
            $('#company_id').val($(this).data('company_id'));

            // Populate Specific Fields
            $('#price_amount').val(price_amount);
            $('#price_issued_date').val(price_issued_date);
            $('#issued_invoice_number').val(issued_invoice_number);
            $('#rejection_reason').val(rejection_reason);
            $('#rejection_date').val(rejection_date);
            $('#company_invoice_number').val(company_invoice_number);
            $('#received_invoice_number').val(received_invoice_number);
            $('#special_remark').val(special_remark);
            $('#special_request_date').val(special_request_date);
            $('#status_remark').val(status_remark);
            $('#general_remark').val(general_remark);

            // Trigger Status Checkboxes
            $('.status-checkbox').prop('checked', false); // Reset
            $('.status-section').hide(); // Hide all

            if (company_status) {
                // Check relevant boxes based on string content
                if (company_status.includes('Priced Issued')) {
                    $('#status_priced_issued').prop('checked', true).trigger('change');
                }
                if (company_status.includes('Rejection')) {
                    $('#status_rejection').prop('checked', true).trigger('change');
                }
                if (company_status.includes('Special Request')) {
                    $('#status_special_request').prop('checked', true).trigger('change');
                }
            }

            // Switch to Update mode
            $('#create').hide();
            $('#update').show();

            // Show print button with handling ID
            $('#print').data('handling-id', handling_id).show();
        } else {
            // New entry mode for this complaint
            $('#id').val('0'); // Reset ID or keep 0
            $('#company_number').val('');
            $('#company_name').val('');
            $('#sent_date').val('');
            $('#company_status').val('');
            $('#company_id').val('');

            // Clear new fields
            $('#price_amount').val('');
            $('#price_issued_date').val('');
            $('#issued_invoice_number').val('');
            $('#rejection_reason').val('');
            $('#rejection_date').val('');
            $('#company_invoice_number').val('');
            $('#received_invoice_number').val('');
            $('#special_remark').val('');
            $('#special_request_date').val('');
            $('#status_remark').val('');
            $('#general_remark').val('');

            $('.status-checkbox').prop('checked', false).trigger('change');

            $('#create').show();
            $('#update').hide();
            $('#print').hide();
        }

        $('#complaintModal').modal('hide');
    });

    // Create / Update
    $('#create, #update').click(function (e) {
        e.preventDefault();

        var id = $('#id').val();
        var complaint_id = $('#complaint_id').val();
        var company_number = $('#company_number').val();
        var company_name = $('#company_name').val();
        var sent_date = $('#sent_date').val();
        var company_status = $('#company_status').val() || 'Pending';

        if (!complaint_id) {
            swal("Error", "Please select a complaint.", "error");
            return;
        }
        if (!company_name) {
            swal("Error", "Please enter company name.", "error");
            return;
        }

        var action = $(this).attr('id'); // create or update
        var data = {
            id: id,
            complaint_id: complaint_id,
            company_number: company_number,
            company_name: company_name,
            sent_date: sent_date,
            company_status: company_status,

            // New Fields
            price_amount: $('#price_amount').val(),
            price_issued_date: $('#price_issued_date').val(),
            issued_invoice_number: $('#issued_invoice_number').val(),
            rejection_reason: $('#rejection_reason').val(),
            rejection_date: $('#rejection_date').val(),
            company_invoice_number: $('#company_invoice_number').val(),
            received_invoice_number: $('#received_invoice_number').val(),
            special_remark: $('#special_remark').val(),
            special_request_date: $('#special_request_date').val(),
            status_remark: $('#status_remark').val(),
            general_remark: $('#general_remark').val()
        };

        if (action === 'create') {
            data.create = true;
        } else {
            data.update = true;
        }

        $.ajax({
            url: 'ajax/php/company-handling.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    swal({
                        title: "Success!",
                        text: "Record saved successfully!",
                        type: "success",
                        showConfirmButton: false,
                        timer: 1500
                    }, function () {
                        location.reload();
                    });
                } else {
                    swal("Error", "Something went wrong!", "error");
                }
            }
        });
    });

    // Delete
    $('.delete-company-handling').click(function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this record!",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false
        }, function () {
            $.ajax({
                url: 'ajax/php/company-handling.php',
                type: 'POST',
                data: { delete: true, id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        swal({
                            title: "Deleted!",
                            text: "Record has been deleted.",
                            type: "success",
                            showConfirmButton: false,
                            timer: 1500
                        }, function () {
                            location.reload();
                        });
                    } else {
                        swal("Error", "Failed to delete.", "error");
                    }
                }
            });
        });
    });

    // New Button
    $('#new').click(function () {
        location.reload();
    });

    // Print Button
    $('#print').click(function (e) {
        e.preventDefault();
        var handlingId = $(this).data('handling-id');
        if (!handlingId) {
            swal("Error", "No record selected for printing.", "error");
            return;
        }
        window.open('company-handling-print.php?id=' + handlingId, '_blank');
    });

    // Edit Button (Populate Form)
    $('.edit-company-handling').click(function () {
        var id = $(this).data('id');
        var complaint_id = $(this).data('complaint_id');
        var complaint_no = $(this).data('complaint_no');
        var uc_number = $(this).data('uc_number');
        var fault_description = $(this).data('fault_description');
        var company_number = $(this).data('company_number');
        var company_name = $(this).data('company_name');
        var sent_date = $(this).data('sent_date');
        var company_status = $(this).data('company_status');

        $('#id').val(id);
        $('#complaint_id').val(complaint_id);
        $('#complaint_no').val(complaint_no);
        $('#uc_number').val(uc_number);
        $('#fault_description').val(fault_description);
        $('#company_number').val(company_number);
        $('#company_name').val(company_name);
        $('#sent_date').val(sent_date);
        $('#company_status').val(company_status);

        $('#create').hide();
        $('#update').show();

        $('html, body').animate({
            scrollTop: $("#form-data").offset().top
        }, 500);
    });

});
