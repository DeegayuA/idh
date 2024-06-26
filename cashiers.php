<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Doctors List</h3>
        <div class="card-tools align-middle">
            <!-- Logout All Doctors Button -->
            <button id="logoutAllCashiers" class="btn btn-flat btn-danger rounded-0 btn-sm py-1"><i class="fa fa-sign-out"></i> Logout All Doctors</button>
            <!-- Add New Doctor Button -->
            <button class="btn btn-dark btn-sm py-1 rounded-0" type="button" id="create_new">Add New</button>
        </div>
    </div>
    <div class="card-body">
        <!-- Table to Display Doctor List -->
        <table class="table table-hover table-striped table-bordered">
            <colgroup>
                <col width="5%">
                <col width="30%">
                <col width="25%">
                <col width="25%">
                <col width="15%">
            </colgroup>
            <thead>
                <tr>
                    <th class="text-center p-0">#</th>
                    <th class="text-center p-0">Name</th>
                    <th class="text-center p-0">Log Status</th>
                    <th class="text-center p-0">Status</th>
                    <th class="text-center p-0">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // PHP code to fetch and display doctor data from the database
                $sql = "SELECT * FROM `cashier_list`  order by `name` asc";
                $qry = $conn->query($sql);
                $i = 1;
                while ($row = $qry->fetchArray()) :
                ?>
                    <tr>
                        <td class="text-center p-0"><?php echo $i++; ?></td>
                        <td class="py-0 px-1"><?php echo $row['name'] ?></td>
                        <td class="py-0 px-1 text-center">
                            <?php
                            // Displaying doctor's log status
                            if ($row['log_status'] == 1) {
                                echo  '<span class="py-1 px-3 badge rounded-pill bg-success"><small>In-Use</small></span>';
                            } else {
                                echo  '<span class="py-1 px-3 badge rounded-pill bg-danger"><small>Not In-Use</small></span>';
                            }
                            ?>
                        </td>
                        <td class="py-0 px-1 text-center">
                            <?php
                            // Displaying doctor's active status
                            if ($row['status'] == 1) {
                                echo  '<span class="py-1 px-3 badge rounded-pill bg-success"><small>Active</small></span>';
                            } else {
                                echo  '<span class="py-1 px-3 badge rounded-pill bg-danger"><small>In-Active</small></span>';
                            }
                            ?>
                        </td>
                        <th class="text-center py-0 px-1">
                            <!-- Action Dropdown for each doctor -->
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle btn-sm rounded-0 py-0" data-bs-toggle="dropdown" aria-expanded="false">
                                    Action
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    <!-- Edit and Delete options in dropdown -->
                                    <li><a class="dropdown-item edit_data" data-id='<?php echo $row['cashier_id'] ?>' href="javascript:void(0)">Edit</a></li>
                                    <li><a class="dropdown-item delete_data" data-id='<?php echo $row['cashier_id'] ?>' data-name='<?php echo $row['name'] ?>' href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </th>
                    </tr>
                <?php endwhile; ?>
                <?php if (!$qry->fetchArray()) : ?>
                    <!-- Display message if no data is available -->
                    <tr>
                        <th class="text-center p-0" colspan="5">No data display.</th>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript Section -->
<script>
    // jQuery document ready function
    $(function() {
        // Click event for Add New Doctor button
        $('#create_new').click(function() {
            uni_modal('Add New Doctor', "manage_cashier.php")
        })

        // Click event for Edit buttons in the dropdown
        $('.edit_data').click(function() {
            uni_modal('Edit Cashier Details', "manage_cashier.php?id=" + $(this).attr('data-id'))
        })

        // Click event for Delete buttons in the dropdown
        $('.delete_data').click(function() {
            _conf("Are you sure to delete <b>" + $(this).attr('data-name') + "</b> from list?", 'delete_data', [$(this).attr('data-id')])
        })
    })

    // Click event for Logout All Doctors button
    $('#logoutAllCashiers').click(function() {
        $.ajax({
            url: 'logout_all_cashiers.php',
            dataType: 'json',
            error: function(err) {
                console.log(err);
            },
            success: function(resp) {
                // Alert user based on logout status
                if (resp.status === 'success') {
                    alert('All Doctors logged out temporarily.');
                    location.reload(); // Reload the page after successful logout
                } else {
                    alert('Failed to logout all Doctors.');
                }
            }
        });
    });

    // Function to handle deleting doctor data
    function delete_data($id) {
        $('#confirm_modal button').attr('disabled', true)
        $.ajax({
            url: './Actions.php?a=delete_cashier',
            method: 'POST',
            data: {
                id: $id
            },
            dataType: 'JSON',
            error: err => {
                console.log(err)
                alert("An error occurred.")
                $('#confirm_modal button').attr('disabled', false)
            },
            success: function(resp) {
                if (resp.status == 'success') {
                    location.reload()
                } else if (resp.status == 'failed' && !!resp.msg) {
                    // Display error message if deletion fails
                    var el = $('<div>')
                    el.addClass('alert alert-danger pop-msg')
                    el.text(resp.msg)
                    el.hide()
                    $('#confirm_modal .modal-body').prepend(el)
                    el.show('slow')
                } else {
                    alert("An error occurred.")
                }
                $('#confirm_modal button').attr('disabled', false)
            }
        })
    }
</script>
