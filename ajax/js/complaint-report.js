jQuery(document).ready(function ($) {
    // Initialize datepickers with range validation
    $(".date-picker").datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        onSelect: function (selectedDate) {
            var input = $(this);
            if (input.attr('id') === 'from_date') {
                var dateMin = $(this).datepicker('getDate');
                $("#to_date").datepicker("option", "minDate", dateMin);

                var toDate = $("#to_date").datepicker('getDate');
                if (toDate && toDate < dateMin) {
                    $("#to_date").datepicker('setDate', dateMin);
                }
            }
        }
    });

    // Set initial min date for 'to' date picker
    $("#from_date").on('change', function () {
        var fromDate = $(this).datepicker('getDate');
        if (fromDate) {
            $("#to_date").datepicker("option", "minDate", fromDate);
        }
    });

    // Initialize DataTable on existing PHP-rendered data
    var complaintReportTable = $('#complaint-report-table').DataTable({
        responsive: true,
        searching: true,
        ordering: true,
        paging: true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 25,
        language: {
            emptyTable: "No complaint records found for the selected filters",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            lengthMenu: "Show _MENU_ entries",
            search: "Search:",
            zeroRecords: "No matching records found"
        },
        columnDefs: [
            { orderable: false, targets: [0, 9] }  // Disable sorting on # and Actions columns
        ],
        order: [[7, 'desc']] // Default sort by Complaint Date descending
    });

    // Filter button click handler
    $("#btn-filter").click(function (event) {
        event.preventDefault();

        // Show preloader
        $('.someBlock').preloader({
            text: 'Loading...',
            zIndex: '99999'
        });

        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var category = $('#filter_category').val();
        var status = $('#filter_status').val();
        var company = $('#filter_company').val();

        $.ajax({
            url: "ajax/php/customer-complaint.php",
            type: 'POST',
            dataType: 'json',
            data: {
                from_date: fromDate,
                to_date: toDate,
                category: category,
                status: status,
                company: company,
                action: 'filter_report'
            },
            success: function (result) {
                // Hide preloader on success
                $('.someBlock').preloader('remove');

                if (result.status === 'success') {
                    updateComplaintReportTable(result.reports || []);
                } else {
                    swal({
                        title: "Error!",
                        text: result.message || "Something went wrong.",
                        type: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function (xhr, status, error) {
                // Hide preloader on error
                $('.someBlock').preloader('remove');
                console.error("Error:", error);
                swal({
                    title: "Error!",
                    text: "Failed to load data. Please try again.",
                    type: 'error'
                });
            }
        });

        return false;
    });

    // Reset button click handler
    $('#btn-reset-filter').on('click', function (e) {
        e.preventDefault();
        window.location.reload();
    });
});

// Update table with data using DataTables
function updateComplaintReportTable(reports) {
    var complaintReportTable = $('#complaint-report-table').DataTable();

    // Clear existing data
    complaintReportTable.clear().draw();

    if (reports.length > 0) {
        $.each(reports, function (index, report) {
            var status = report.company_status || 'Pending';
            var statusClass = getComplaintStatusClass(status);

            var rowNode = complaintReportTable.row.add([
                index + 1,
                report.complaint_no || '',
                report.uc_number || '',
                report.customer_name || '',
                report.company_name || '<span class="text-muted">Not Assigned</span>',
                report.tyre_serial_number || '',
                report.fault_description || '',
                (report.complaint_date && report.complaint_date !== '0000-00-00') ? formatComplaintDate(report.complaint_date) : '-',
                '<span class="badge ' + statusClass + ' font-size-12">' + capitalizeComplaintFirst(status) + '</span>',
                (report.handling_id && status.toLowerCase() !== 'pending') ? (status.toLowerCase() === 'priced issued' ? '<a href="company-handling-print.php?id=' + report.handling_id + '" target="_blank" class="btn btn-info btn-sm"><i class="mdi mdi-printer"></i></a>' : '<a href="complaint-print.php?id=' + report.id + '" target="_blank" class="btn btn-info btn-sm"><i class="mdi mdi-printer"></i></a>') : ''
            ]).draw(false).node();

            if (status.toLowerCase() === 'rejection') {
                $(rowNode).addClass('table-danger');
            }
        });
    }
}

// Get status badge class
function getComplaintStatusClass(status) {
    var statusLower = status.toLowerCase();
    if (statusLower.includes('priced issued')) {
        return 'bg-success';
    } else if (statusLower.includes('special request')) {
        return 'bg-primary';
    } else if (statusLower.includes('rejection')) {
        return 'bg-danger';
    } else {
        return 'bg-warning';
    }
}

// Format date to dd/mm/yyyy
function formatComplaintDate(dateString) {
    if (!dateString) return '';
    var date = new Date(dateString);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    return day + '/' + month + '/' + year;
}

// Capitalize first letter
function capitalizeComplaintFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Print report function
function printReport() {
    window.print();
}
