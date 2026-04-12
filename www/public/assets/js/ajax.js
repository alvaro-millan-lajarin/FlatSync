$(document).ready(function() {
    $('#login-form').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        let payload = {
            username: $('input[name=username]').val(),
            password: $('input[name=password]').val()
        };

        $.ajax({
            type: 'POST',
            url: '/login',
            contentType: 'application/json;charset=utf-8',
            data: JSON.stringify(payload),
            dataType: 'json'
        })
            .done(function(data) {
                $('#response').html('<p class="success">' + data.responseData + '</p>');
            })
            .fail(function(jqXHR) {
                let errors = jqXHR.responseJSON.errors;
                let errorHtml = '<ul>';
                $.each(errors, function(field, error) {
                    errorHtml += '<li>' + error + '</li>';
                });
                errorHtml += '</ul>';
                $('#response').html('<p class="error">Errors occurred:</p>' + errorHtml);
            });
    });
});