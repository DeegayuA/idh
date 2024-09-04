<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Fetch Patient Data by Unique Person ID</title>
</head>
<body>
    <h1>Test Fetch Patient Data</h1>
    <form id="testForm">
        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number">
        <br><br>

        <label for="nic">NIC:</label>
        <input type="text" id="nic" name="nic">
        <br><br>

        <label for="unique_person_id">Unique Person ID:</label>
        <input type="text" id="unique_person_id" name="unique_person_id">
        <br><br>

        <button type="button" onclick="submitForm()">Submit</button>
    </form>

    <h2>Response</h2>
    <pre id="response"></pre>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function submitForm() {
            // Collect form data
            var formData = {
                phone_number: $('#phone_number').val(),
                nic: $('#nic').val(),
                unique_person_id: $('#unique_person_id').val()
            };

            // Make the AJAX request
            $.ajax({
                url: 'fetch_patient_data.php', // Update with your PHP script path
                type: 'POST',
                data: formData,
                success: function(response) {
                    try {
                        // Parse JSON response
                        var parsedResponse = JSON.parse(response);
                        if (parsedResponse.status === 'success') {
                            $('#response').text(JSON.stringify(parsedResponse.data, null, 2));
                        } else if (parsedResponse.status === 'not_found') {
                            $('#response').text('Patient data not found for the provided details.');
                        } else {
                            $('#response').text('Error: ' + parsedResponse.message);
                        }
                    } catch (e) {
                        // Handle JSON parsing error
                        $('#response').text('Error parsing JSON response: ' + e.message);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'Request failed with status: ' + status + ', Error: ' + error;
                    if (xhr.responseText) {
                        errorMessage += '\nDetails: ' + xhr.responseText;
                    }
                    $('#response').text(errorMessage);
                }
            });
        }
    </script>
</body>
</html>
