jQuery(document).ready(function () {
    // Create
    $("#create").click(function (event) {
        event.preventDefault();

        if (!validateForm()) {
            return false;
        }

        // Preloader start
        $(".someBlock").preloader();

        var formData = new FormData($("#form-data")[0]);
        formData.append("create", true);

        $.ajax({
            url: "ajax/php/dag-customer.php",
            type: "POST",
            data: formData,
            async: false,
            dataType: 'json',
            success: function (result) {
                $(".someBlock").preloader("remove");
                if (result.status === 'success') {
                    swal({
                        title: "Success!",
                        text: "DAG Customer Details Saved Successfully",
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
                        text: "Error saving data",
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
    });

    // Update
    $("#update").click(function (event) {
        event.preventDefault();

        if (!validateForm()) {
            return false;
        }

        // Preloader start
        $(".someBlock").preloader();

        var formData = new FormData($("#form-data")[0]);
        formData.append("update", true);

        $.ajax({
            url: "ajax/php/dag-customer.php",
            type: "POST",
            data: formData,
            async: false,
            dataType: 'json',
            success: function (result) {
                $(".someBlock").preloader("remove");
                if (result.status === 'success') {
                    swal({
                        title: "Success!",
                        text: "DAG Customer Details Updated Successfully",
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
                        text: "Error updating data",
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
    });

    // Delete
    $(document).on("click", ".delete-item", function (e) {
        e.preventDefault();

        var id = $("#id").val();
        var my_number = $("#my_number").val();

        if (!id || id === "0") {
            swal({
                title: "Error!",
                text: "Please select a record first.",
                type: "error",
                timer: 2000,
                showConfirmButton: false,
            });
            return;
        }

        swal(
            {
                title: "Are you sure?",
                text: "Do you want to delete My Number: " + my_number + "?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false,
            },
            function (isConfirm) {
                if (isConfirm) {
                    $(".someBlock").preloader();

                    $.ajax({
                        url: "ajax/php/dag-customer.php",
                        type: "POST",
                        data: {
                            id: id,
                            delete: true,
                        },
                        dataType: "json",
                        success: function (response) {
                            $(".someBlock").preloader("remove");

                            if (response.status === "success") {
                                swal({
                                    title: "Deleted!",
                                    text: "Record has been deleted.",
                                    type: "success",
                                    timer: 2000,
                                    showConfirmButton: false,
                                });

                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Something went wrong.",
                                    type: "error",
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                            }
                        },
                    });
                }
            }
        );
    });

    // Validate
    function validateForm() {
        var isValid = true;

        // Simple manual validation as in company-master.js
        if (!$("#customer_id").val()) {
            swal({ title: "Error!", text: "Please select Customer", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }
        if (!$("#my_number").val()) {
            swal({ title: "Error!", text: "Please enter My Number", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }
        if (!$("#size").val()) {
            swal({ title: "Error!", text: "Please enter Size", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }
        if (!$("#brand").val()) {
            swal({ title: "Error!", text: "Please enter Brand", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }
        if (!$("#serial_no").val()) {
            swal({ title: "Error!", text: "Please enter Serial No", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }
        if (!$("#dag_received_date").val()) {
            swal({ title: "Error!", text: "Please select Date", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }

        return isValid;
    }

    // New button
    $('#new').click(function (e) {
        e.preventDefault();
        $("#form-data")[0].reset();
        $("#id").val("0");
        $("#customer_id").val("");

        // Fetch next DAG number
        $.ajax({
            url: "ajax/php/dag-customer.php",
            type: "POST",
            data: { get_next_id: true },
            dataType: "json",
            success: function (result) {
                if (result.status === 'success') {
                    $("#dag_number").val(result.next_id);
                }
            }
        });

        $("#create").show();
        $("#update").hide();
        $(".delete-item").hide();
        $("#print").hide();
    });

    // Model click append value
    $(document).on("click", ".select-dag-customer", function () {
        var id = $(this).data("id");
        var formattedId = "DAG-" + String(id).padStart(5, '0');

        $("#id").val(id);
        $("#dag_number").val(formattedId);
        $("#customer_id").val($(this).data("customer_id"));
        $("#customer_code").val($(this).data("customer_code"));
        $("#customer_name").val($(this).data("customer_name"));
        $("#my_number").val($(this).data("my_number"));
        $("#size").val($(this).data("size"));
        $("#brand").val($(this).data("brand"));
        $("#serial_no").val($(this).data("serial_no"));
        $("#dag_received_date").val($(this).data("dag_received_date"));
        $("#remark").val($(this).data("remark"));

        $("#create").hide();
        $("#update").show();
        $(".delete-item").show();
        $("#print").show();
        $("#print").attr("href", "print-dag-customer.php?id=" + id);
        $("#dagCustomerModal").modal("hide");
    });
});
