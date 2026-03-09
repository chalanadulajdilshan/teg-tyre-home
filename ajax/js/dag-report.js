jQuery(document).ready(function ($) {
    // Initialize datepickers
    $(".date-picker").datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    // Initialize DataTable
    var dagReportTable = $('#dag-report-table').DataTable({
        responsive: true,
        searching: true,
        ordering: true,
        paging: true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 25,
        language: {
            emptyTable: "No DAG records found for the selected filters",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            lengthMenu: "Show _MENU_ entries",
            search: "Search:",
            zeroRecords: "No matching records found"
        },
        columnDefs: [
            { orderable: false, targets: [0] }
        ],
        order: [[1, 'desc']]
    });

    // Filter button
    $("#btn-filter").click(function (e) {
        e.preventDefault();
        $('.someBlock').preloader({ text: 'Loading...', zIndex: '99999' });

        $.ajax({
            url: "ajax/php/dag-report.php",
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'filter_report',
                date_from: $('#from_date').val(),
                date_to: $('#to_date').val(),
                customer: $('#filter_customer').val(),
                company: $('#filter_company').val(),
                brand: $('#filter_brand').val(),
                invoice_status: $('#filter_invoice_status').val()
            },
            success: function (result) {
                $('.someBlock').preloader('remove');
                if (result.status === 'success') {
                    updateTable(result.data || []);
                } else {
                    swal({ title: "Error!", text: result.message || "Something went wrong.", type: 'error', timer: 2000, showConfirmButton: false });
                }
            },
            error: function () {
                $('.someBlock').preloader('remove');
                swal({ title: "Error!", text: "Failed to load data.", type: 'error', timer: 2000, showConfirmButton: false });
            }
        });
    });

    // Load all data on page load (clear dates first so no filter is applied)
    $('#from_date').val('');
    $('#to_date').val('');
    $("#btn-filter").trigger('click');

    // Reset button
    $('#btn-reset-filter').on('click', function (e) {
        e.preventDefault();
        window.location.reload();
    });

    function updateTable(data) {
        dagReportTable.clear().draw();

        if (data.length > 0) {
            $.each(data, function (index, r) {
                var dagNumber = r.dag_number || 'DAG-' + String(r.id).padStart(5, '0');
                var customerName = (r.customer_name || '') + (r.customer_name_2 ? ' ' + r.customer_name_2 : '');
                var customerCode = r.customer_code || '';

                // Customer issued
                var issuedHtml = '<span class="text-muted">Not Issued</span>';
                if (r.issued_date) {
                    issuedHtml = '<div class="timeline-info">' +
                        '<span class="label">Date:</span> <span class="value">' + fmtDate(r.issued_date) + '</span><br>' +
                        '<span class="label">To:</span> <span class="value">' + escHtml(customerName) + '</span>' +
                        '</div>';
                }

                // Pricing
                var pricingHtml = '<span class="text-muted">-</span>';
                if (r.is_invoiced == 1 || parseFloat(r.price) > 0) {
                    pricingHtml = '<div class="timeline-info">' +
                        '<span class="label">Cost:</span> ' + parseFloat(r.cost || 0).toFixed(2) + '<br>' +
                        '<span class="label">Price:</span> ' + parseFloat(r.price || 0).toFixed(2) + '<br>' +
                        '<span class="label">Disc:</span> ' + parseFloat(r.discount || 0).toFixed(2) + '%<br>' +
                        '<span class="label">Total:</span> <strong>' + parseFloat(r.total || 0).toFixed(2) + '</strong>' +
                        '</div>';
                }

                // Check for rejection in *any* assignment
                var isRejected = false;
                var hasActiveAssignments = false;
                if (r.company_assignments && r.company_assignments.length > 0) {
                    // It's rejected if the *last* assignment was rejected, or just if any was rejected based on old logic?
                    // Let's assume the overall DAG is rejected if the last assignment in the array is rejected and not invoiced
                    // But to be consistent with previous logic, we check if all assignments are rejected to block invoice.
                    // For UI row color, we color the master row green if it's invoiced. The child rows will hold specific statuses.
                }

                var rowClass = '';
                if (r.is_cancelled == 1) rowClass = 'table-danger';
                else if (r.is_invoiced == 1) rowClass = 'table-success';

                // Invoice status badge for the master row
                var invBadge = '';
                if (r.is_cancelled == 1) {
                    invBadge = '<span class="badge bg-danger font-size-12">Cancelled</span>';
                } else if (r.is_invoiced == 1) {
                    invBadge = '<span class="badge bg-success font-size-12">Invoiced</span>';
                } else {
                    invBadge = '<span class="badge bg-warning font-size-12">Not Invoiced</span>';
                }

                // Add row and attach nested assignments data to the node
                var rowNode = dagReportTable.row.add([
                    '<span class="mdi mdi-plus-circle-outline text-primary" style="font-size:18px; cursor:pointer;" title="View Company Assignments"></span>',
                    '<strong>' + escHtml(dagNumber) + '</strong><br><small class="text-muted">My#: ' + escHtml(r.my_number || '') + '</small>',
                    '<div class="timeline-info"><span class="value">' + escHtml(customerName) + '</span><br><small class="text-muted">' + escHtml(customerCode) + '</small></div>' + issuedHtml,
                    '<div class="timeline-info"><span class="label">Size:</span> <span class="value">' + escHtml(r.size || '') + '</span><br><span class="label">Brand:</span> <span class="value">' + escHtml(r.brand || '') + '</span><br><span class="label">Serial:</span> <span class="value">' + escHtml(r.serial_no || '') + '</span></div>',
                    '<div class="timeline-info"><span class="value">' + fmtDate(r.dag_received_date) + '</span>' + (r.remark ? '<br><small class="text-muted">' + escHtml(r.remark) + '</small>' : '') + '</div>',
                    invBadge,
                    pricingHtml
                ]).draw(false).node();

                // Store assignments data for expansion
                var rowData = dagReportTable.row(rowNode).data();
                rowData._company_assignments = r.company_assignments || [];
                dagReportTable.row(rowNode).data(rowData);

                if (rowClass) $(rowNode).addClass(rowClass);
                $(rowNode).find('td:first').addClass('details-control text-center').css('vertical-align', 'middle');
            });
        }
    }

    // Toggle child rows on click
    $('#dag-report-table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = dagReportTable.row(tr);
        var icon = $(this).find('span.mdi');

        if (row.child.isShown()) {
            // Close row
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('mdi-minus-circle-outline').addClass('mdi-plus-circle-outline');
        } else {
            // Extend data inside row child
            var data = row.data();
            var assignments = data._company_assignments || [];

            var childHtml = '<div class="p-3 bg-light rounded" style="border-left: 3px solid #5b73e8;">';
            childHtml += '<h5 class="font-size-14 mb-3">Company Assignment History</h5>';

            if (assignments.length > 0) {
                childHtml += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0 bg-white">';
                childHtml += '<thead class="table-light"><tr>' +
                    '<th>Company</th>' +
                    '<th>Assign #</th>' +
                    '<th>Company Issued Date</th>' +
                    '<th>Company Receipt #</th>' +
                    '<th>Job #</th>' +
                    '<th>UC #</th>' +
                    '<th>Belt</th>' +
                    '<th>Status</th>' +
                    '<th>Company Received Date</th>' +
                    '</tr></thead><tbody>';

                $.each(assignments, function (i, a) {
                    var compBadge = '<span class="text-muted">-</span>';
                    var compStatus = a.company_status || '';
                    if (compStatus) {
                        var csLower = compStatus.toLowerCase();
                        if (csLower.indexOf('reject') !== -1) {
                            compBadge = '<span class="badge bg-danger font-size-12">' + escHtml(compStatus) + '</span>';
                        } else if (csLower.indexOf('received') !== -1 || csLower.indexOf('complete') !== -1) {
                            compBadge = '<span class="badge bg-success font-size-12">' + escHtml(compStatus) + '</span>';
                        } else if (csLower.indexOf('pending') !== -1) {
                            compBadge = '<span class="badge bg-warning font-size-12">' + escHtml(compStatus) + '</span>';
                        } else {
                            compBadge = '<span class="badge bg-info font-size-12">' + escHtml(compStatus) + '</span>';
                        }
                    }

                    childHtml += '<tr>' +
                        '<td><strong>' + escHtml(a.company_name || '-') + '</strong></td>' +
                        '<td>' + escHtml(a.assignment_number || '-') + '</td>' +
                        '<td>' + fmtDate(a.company_issued_date) + '</td>' +
                        '<td>' + escHtml(a.company_receipt_number || '-') + '</td>' +
                        '<td>' + escHtml(a.job_number || '-') + '</td>' +
                        '<td>' + escHtml(a.uc_number || '-') + '</td>' +
                        '<td>' + escHtml(a.belt_design || '-') + '</td>' +
                        '<td>' + compBadge + '</td>' +
                        '<td>' + fmtDate(a.company_received_date) + '</td>' +
                        '</tr>';
                });

                childHtml += '</tbody></table></div>';
            } else {
                childHtml += '<p class="text-muted mb-0">No company assignments found for this DAG.</p>';
            }
            childHtml += '</div>';

            // Open row
            row.child(childHtml).show();
            tr.addClass('shown');
            icon.removeClass('mdi-plus-circle-outline').addClass('mdi-minus-circle-outline');
        }
    });

    function fmtDate(d) {
        if (!d || d === '0000-00-00') return '-';
        var parts = d.split('-');
        if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
        return d;
    }

    function escHtml(str) {
        if (!str) return '';
        return $('<div>').text(str).html();
    }
});
