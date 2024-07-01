<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fetch Patient Data</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchPatientData() {
            var phoneNumber = $('#phone_number').val();
            var nicNumber = $('#nic_number').val();
            var uniquePersonId = $('#unique_person_id').val();

            $.ajax({
                url: 'fetch_patient_data.php',
                method: 'POST',
                data: {
                    phone_number: phoneNumber,
                    nic: nicNumber,
                    unique_person_id: uniquePersonId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var data = response.data;
                        // Populate input fields with fetched data
                        $('#customer_name').val(data.customer_name || '');
                        $('#age').val(data.age || '');
                        $('#sex').val(data.sex || '');
                        $('#encrypted_id_number').val(data.encrypted_id_number || '');
                        $('#phone_number_display').val(data.phone_number || '');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Error fetching patient data:', textStatus, errorThrown);
                    alert('Error fetching patient data: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }
    </script>
</head>
<body>
    <h1>Fetch Patient Data</h1>
    <form id="fetchForm" onsubmit="event.preventDefault(); fetchPatientData();">
        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number">
        <br><br>
        <label for="nic_number">NIC Number:</label>
        <input type="text" id="nic_number" name="nic_number">
        <br><br>
        <label for="unique_person_id">Unique Person ID:</label>
        <input type="text" id="unique_person_id" name="unique_person_id">
        <br><br>
        <button type="submit">Fetch Data</button>
    </form>
    <hr>
    <h2>Patient Information</h2>
    <form>
        <label for="customer_name">Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" readonly>
        <br><br>
        <label for="age">Age:</label>
        <input type="text" id="age" name="age" readonly>
        <br><br>
        <label for="sex">Sex:</label>
        <input type="text" id="sex" name="sex" readonly>
        <br><br>
        <label for="encrypted_id_number">Encrypted ID Number:</label>
        <input type="text" id="encrypted_id_number" name="encrypted_id_number" readonly>
        <br><br>
        <label for="phone_number_display">Phone Number:</label>
        <input type="text" id="phone_number_display" name="phone_number_display" readonly>
    </form>
</body>
</html>
