document.addEventListener('DOMContentLoaded', function () {
    let sendEmailsTimeout;

    // Handle file upload form submission
    document.getElementById('uploadForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('../../backend/upload.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                displayMessage(data.message || data.error || 'Unknown error occurred');
            })
            .catch(error => {
                console.error('Error:', error);
                displayMessage('An error occurred while uploading the file.');
            });
    });

    // Handle send emails form submission
    document.getElementById('sendEmailsForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        sendEmails(formData);
    });

    /**
     * Sends emails using the provided form data.
     *
     * @param {FormData} formData The form data to be sent.
     */
    function sendEmails(formData) {
        fetch('../../backend/send_emails.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                handleSendEmailsResponse(data);
            })
            .catch(error => {
                console.error('Error:', error);
                displayMessage('An error occurred while sending emails.');
            });
    }

    /**
     * Handles the response from send emails request.
     *
     * @param {Object} data The response data from the server.
     */
    function handleSendEmailsResponse(data) {
        const messageDiv = document.getElementById('message');
        if (data.message) {
            displayMessage(data.message);
        }
        if (data.emails && data.emails.length > 0) {
            const emailList = document.createElement('ul');
            data.emails.forEach(function (email) {
                const listItem = document.createElement('li');
                listItem.textContent = email.message;
                if (email.status === 'error') {
                    listItem.classList.add('error');
                }
                emailList.appendChild(listItem);
            });
            messageDiv.appendChild(emailList);
        }
        if (data.interval) {
            sendEmailsTimeout = setTimeout(function () {
                sendEmails(new FormData(document.getElementById('sendEmailsForm')));
            }, data.interval * 1000);
        }
    }

    // Handle stop button click event
    document.getElementById('stopButton').addEventListener('click', function () {
        clearTimeout(sendEmailsTimeout);
        displayMessage('Sending emails stopped.');
    });

    /**
     * Displays a message in the message div.
     *
     * @param {string} message The message to be displayed.
     */
    function displayMessage(message) {
        const messageDiv = document.getElementById('message');
        messageDiv.innerHTML = message;
    }
});
