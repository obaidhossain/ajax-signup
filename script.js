------ script.js --------
	$(document).on("submit", '#tf_action_signup', function(e) {
        e.preventDefault();
        var form = $(this),
			error,
			username = form.find("#username"),
			email = form.find("#email"),
			password = form.find("#reg_password"),
			password2 = form.find("#reg_password2"),
			tos = form.find("#tos_agree");

        if (tos.is(':checked')) {
            hideerror(tos);
        } else {
            form.find(".reg_msg.fail").text('You must agree to our terms and conditions.').show();
            showerror(tos);
            error = true;
        }
        if (username.val() === '') {
            form.find(".reg_msg.fail").text('Please fill all required fields.').show();
            showerror(username);
            error = true;
        } else {
            hideerror(username);
        }
        if (email.val() == '') {
            form.find(".reg_msg.fail").text('Please fill all required fields.').show();
            showerror(email);
            error = true;
        } else {
            hideerror(email);
        }
        if (password.val() == '') {
            form.find(".reg_msg.fail").text('Please fill all required fields.').show();
            showerror(password);
            error = true;
        } else {
            hideerror(password);
        }
        if (password2.val() == '') {
            form.find(".reg_msg.fail").text('Please fill all required fields.').show();
            showerror(password2);
            error = true;
        } else {
            hideerror(password2);
        }
        if (password.val() != password2.val()) {
            form.find(".reg_msg.fail").text('Your password does not match.').show();
            showerror(password);
            showerror(password2);
            error = true;
        } else {
            hideerror(password);
            hideerror(password2);
        }
        if (error == true) {
            return false;
        }
        form.find(".reg_loader").show();
        form.find(".reg_msg").hide();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_login_var.ajax_url,
            data: form.serialize(),
            success: function(data) {
                form.find(".reg_loader").hide();
                if (data.loggedin == true) {
                    if (data.is_verify == true) {
                        form.find(".reg_msg.success").html(data.message).show();
                        if (data.redirect != false) {
                            window.location = ajax_login_var.signup_redirect;
                        }
                    } else {
                        form.find(".reg_msg.success").html(data.message).show();
                    }
                } else {
                    if (data.invalid_username == true) {
                        showerror(username);
                        username.removeClass('input-success-icon');
                        username.addClass('input-failure-icon');
                    } else {
                        hideerror(username);
                        username.addClass('input-success-icon');
                        username.removeClass('input-failure-icon');
                    }
                    if (data.user_exists == true) {
                        showerror(username);
                        username.removeClass('input-success-icon');
                        username.addClass('input-failure-icon');
                    } else {
                        hideerror(username);
                        username.addClass('input-success-icon');
                        username.removeClass('input-failure-icon');
                    }
                    if (data.email_exists == true) {
                        showerror(email);
                        email.removeClass('input-success-icon');
                        email.addClass('input-failure-icon');
                    } else {
                        hideerror(email);
                        email.addClass('input-success-icon');
                        email.removeClass('input-failure-icon');
                    }
                    form.find(".reg_msg.fail").html(data.message).show();
                }
            },
            error: function(jqXHR, exception, data) {
                var msg = '';
                if (jqXHR.status === 0) {
                    msg = 'Not connect.\n Verify Network.';
                } else if (jqXHR.status == 404) {
                    msg = 'Requested page not found. [404]';
                } else if (jqXHR.status == 500) {
                    msg = 'Internal Server Error [500].';
                } else if (exception === 'parsererror') {
                    msg = 'Requested JSON parse failed.';
                } else if (exception === 'timeout') {
                    msg = 'Time out error.';
                } else if (exception === 'abort') {
                    msg = 'Ajax request aborted.';
                } else if (jqXHR.responseText === '-1') {
                    msg = 'Please refresh page and try again.';
                } else if (!data.loggedin) {
                    msg = data.message;
                } else {
                    msg = 'Uncaught Error.\n' + jqXHR.responseText;
                }
                form.find(".reg_loader").hide();
                form.find(".reg_msg.fail").hide();
                form.find(".reg_msg.fail").html(data.message).show();
            },
        });
    });
