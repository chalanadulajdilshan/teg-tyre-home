$(document).ready(function () {
    // Load complaints on modal open
    $('#mainComplaintModel').on('shown.bs.modal', function () {
        loadComplaints();
    });

    // Search complaints
    $('#searchComplaintBtn').on('click', function () {
        const searchTerm = $('#complaintSearchInput').val();
        loadComplaints(searchTerm);
    });

    // Search on Enter key
    $('#complaintSearchInput').on('keypress', function (e) {
        if (e.which === 13) {
            const searchTerm = $(this).val();
            loadComplaints(searchTerm);
        }
    });

    // Load complaints function
    function loadComplaints(searchTerm = '') {
        $.ajax({
            url: 'ajax/php/customer-complaint.php',
            type: 'POST',
            data: {
                load_complaints: true,
                search: searchTerm
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#mainComplaintTableBody').html(response.html);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading complaints:', error);
            }
        });
    }

    // Select complaint from modal
    $(document).on('click', '.select-complaint', function () {
        const data = $(this).data();

        $('#id').val(data.id);
        $('#complaint_no').val(data.complaint_no);
        $('#uc_number').val(data.uc_number);
        $('#customer_id').val(data.customer_id);
        $('#customer_code').val(data.customer_code);
        $('#customer_name').val(data.customer_name);
        $('#tyre_serial_number').val(data.tyre_serial_number);
        $('#fault_description').val(data.fault_description);
        $('#complaint_category').val(data.complaint_category);
        $('#complaint_date').val(data.complaint_date);

        $('#mainComplaintModel').modal('hide');

        // Show update/delete, hide create
        $('#create').hide();
        $('#update').show();
        $('#print').show();
    });

    // New button - reset form
    $('#new').on('click', function (e) {
        e.preventDefault();
        window.location.reload();
    });

    function resetForm() {
        $('#form-data')[0].reset();
        $('#id').val('0');

        // Generate new complaint number
        $.ajax({
            url: 'ajax/php/customer-complaint.php',
            type: 'POST',
            data: { get_next_id: true },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#complaint_no').val('CC/00/' + response.next_id);
                }
            }
        });

        // Reset customer fields
        $('#customer_id').val('');
        $('#customer_code').val('');
        $('#customer_name').val('');

        // Show create, hide update
        $('#create').show();
        $('#update').hide();
        $('#print').hide();

        // Set default date
        const today = new Date().toISOString().split('T')[0];
        $('#complaint_date').val(today);
    }

    // Create complaint
    $('#create').off('click').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Prevent customer-master.js from handling this

        // Validation removed as per request

        const formData = {
            create: true,
            complaint_no: $('#complaint_no').val(),
            uc_number: $('#uc_number').val(),
            customer_id: $('#customer_id').val(),
            tyre_serial_number: $('#tyre_serial_number').val(),
            fault_description: $('#fault_description').val(),
            complaint_category: $('#complaint_category').val(),
            complaint_date: $('#complaint_date').val()
        };

        $.ajax({
            url: 'ajax/php/customer-complaint.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    swal({
                        title: 'Success!',
                        text: 'Complaint created successfully!',
                        type: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                } else {
                    swal({
                        title: 'Error!',
                        text: response.message || 'Failed to create complaint',
                        type: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                swal({
                    title: 'Error!',
                    text: 'An error occurred while creating the complaint',
                    type: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });

    // Update complaint
    $('#update').off('click').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Prevent customer-master.js from handling this

        // Validation removed as per request

        const formData = {
            update: true,
            complaint_id: $('#id').val(),
            complaint_no: $('#complaint_no').val(),
            uc_number: $('#uc_number').val(),
            customer_id: $('#customer_id').val(),
            tyre_serial_number: $('#tyre_serial_number').val(),
            fault_description: $('#fault_description').val(),
            complaint_category: $('#complaint_category').val(),
            complaint_date: $('#complaint_date').val()
        };

        $.ajax({
            url: 'ajax/php/customer-complaint.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    swal({
                        title: 'Success!',
                        text: 'Complaint updated successfully!',
                        type: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                } else {
                    swal({
                        title: 'Error!',
                        text: response.message || 'Failed to update complaint',
                        type: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                swal({
                    title: 'Error!',
                    text: 'An error occurred while updating the complaint',
                    type: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });

    // Delete complaint
    $('.delete-complaint').on('click', function (e) {
        e.preventDefault();

        const complaintId = $('#id').val();
        if (!complaintId || complaintId === '0') {
            swal({
                title: 'Warning!',
                text: 'Please select a complaint to delete',
                type: 'warning',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        swal({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            closeOnConfirm: false
        }, function (isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: 'ajax/php/customer-complaint.php',
                    type: 'POST',
                    data: {
                        delete: true,
                        complaint_id: complaintId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            swal({
                                title: 'Deleted!',
                                text: 'Complaint has been deleted.',
                                type: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                resetForm();
                            }, 2000);
                        } else {
                            swal({
                                title: 'Error!',
                                text: response.message || 'Failed to delete complaint',
                                type: 'error',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        swal({
                            title: 'Error!',
                            text: 'An error occurred while deleting the complaint',
                            type: 'error',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });
    });



    // Print functionality
    $('#print').on('click', function (e) {
        e.preventDefault();
        const complaintId = $('#id').val();
        if (complaintId && complaintId !== '0') {
            window.open('complaint-print.php?id=' + complaintId, '_blank');
        }
    });
});
